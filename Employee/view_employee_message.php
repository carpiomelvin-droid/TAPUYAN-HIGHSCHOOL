<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];
$message_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$view_type = isset($_GET['view_type']) ? trim($_GET['view_type']) : 'inbox';

if ($message_id <= 0) {
    die("Invalid message ID.");
}

// 🟢 DYNAMIC PERMISSION CHECK: Check Inbox vs Sent items query parameters
if ($view_type === 'sent') {
    // Viewer is the Sender
    $sql = "SELECT
                m.message_id, m.sender_id, m.sender_role, m.receiver_id, m.receiver_role,
                m.subject, m.message_text, m.sent_at, m.is_read,
                a.username AS party_username,
                ed.name AS party_name,
                ed.employee_number AS party_emp_num
            FROM messages m
            LEFT JOIN admin a ON m.receiver_id = a.EmployeeID
            LEFT JOIN employee_details ed ON a.EmployeeID = ed.EmployeeID
            WHERE m.message_id = ?
              AND m.sender_id = ?
              AND m.sender_role = 'employee'
              AND m.sender_deleted = 0
            LIMIT 1";
} else {
    // Viewer is the Receiver (Inbox Default)
    $sql = "SELECT
                m.message_id, m.sender_id, m.sender_role, m.receiver_id, m.receiver_role,
                m.subject, m.message_text, m.sent_at, m.is_read,
                a.username AS party_username,
                ed.name AS party_name,
                ed.employee_number AS party_emp_num
            FROM messages m
            LEFT JOIN admin a ON m.sender_id = a.EmployeeID
            LEFT JOIN employee_details ed ON a.EmployeeID = ed.EmployeeID
            WHERE m.message_id = ?
              AND m.receiver_id = ?
              AND m.receiver_role = 'employee'
              AND m.receiver_deleted = 0
            LIMIT 1";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("ii", $message_id, $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message) {
    die("Message not found or access denied.");
}

// 🟢 STEP 2: Mark message as read ONLY if viewing an unread inbox item
if ($view_type !== 'sent' && (int) $message['is_read'] === 0) {
    $sqlUpdate = "UPDATE messages SET is_read = 1, read_at = NOW() WHERE message_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if ($stmtUpdate) {
        $stmtUpdate->bind_param("i", $message_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
}

// 🟢 STEP 3: Fetch linked attachments if they exist
$attachments = [];
$sqlFiles = "SELECT file_id, file_name, mime_type FROM message_files WHERE message_id = ?";
$stmtFiles = $conn->prepare($sqlFiles);
if ($stmtFiles) {
    $stmtFiles->bind_param("i", $message_id);
    $stmtFiles->execute();
    $resFiles = $stmtFiles->get_result();
    while ($fRow = $resFiles->fetch_assoc()) {
        $attachments[] = $fRow;
    }
    $stmtFiles->close();
}

// Format party names
$partyDisplayName = !empty($message['party_name']) ? htmlspecialchars($message['party_name']) : htmlspecialchars($message['party_username'] ?? 'System/Admin');
$roleLabel = ($view_type === 'sent')
    ? (($message['receiver_role'] === 'admin') ? 'Super Admin' : 'Staff')
    : (($message['sender_role'] === 'admin') ? 'Super Admin' : 'Staff');

$backUrl = ($view_type === 'sent') ? 'employee_sent_messages.php' : 'employee_inbox.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message | Employee Portal</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg-page: #f8fafc;
            --surface: #ffffff;
            --surface-hover: #f1f5f9;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;

            --primary: #0f172a;
            --primary-hover: #1e293b;
            --accent: #2563eb;
            --accent-soft: #eff6ff;

            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .content {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 24px 40px;
        }

        /* --- Header & Navigation --- */
        .topbar {
            margin-bottom: 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.15s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* --- Main Layout --- */
        .page-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .page-header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            padding: 32px;
            background: var(--bg-page);
        }

        /* --- Panels --- */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fdfdfd;
        }

        .panel-header i {
            color: var(--text-muted);
        }

        .panel-body {
            padding: 20px;
        }

        /* --- Message Content --- */
        .message-box {
            background: var(--surface-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 20px;
            min-height: 200px;
            white-space: pre-wrap;
            text-align: left;
            line-height: 1.6;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        /* --- Metadata List --- */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
        }

        /* --- Attachments --- */
        .attachment-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .attachment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            background: var(--surface);
            transition: border-color 0.15s ease;
        }

        .attachment-item:hover {
            border-color: var(--border-strong);
        }

        .attachment-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .file-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--accent-soft);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        .attachment-name {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.9rem;
            margin-bottom: 2px;
            word-break: break-word;
        }

        .attachment-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .empty-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-style: italic;
        }

        /* --- Buttons --- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-download {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border);
        }

        .btn-download:hover {
            background: var(--surface-hover);
            border-color: var(--border-strong);
        }

        .btn-back-action {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border-strong);
        }

        .btn-back-action:hover {
            background: var(--surface-hover);
        }

        .btn-reply {
            background: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn-reply:hover {
            background: var(--primary-hover);
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .actions .btn {
            flex: 1;
        }

        @media (max-width: 900px) {
            .main-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }

        @media (max-width: 640px) {
            .attachment-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-download {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <?php include_once '../includes/headeremployee.php'; ?>

    <main class="content">
        <div class="topbar">
            <a href="<?php echo $backUrl; ?>" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to
                <?php echo ($view_type === 'sent') ? 'Sent Items' : 'Inbox'; ?>
            </a>
        </div>

        <div class="page-card">
            <div class="page-header">
                <h1 class="page-title"><?php echo htmlspecialchars($message['subject']); ?></h1>
                <p class="page-subtitle">Message communication details</p>
            </div>

            <div class="main-grid">
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div class="panel">
                        <div class="panel-header">
                            <i class="fa-solid fa-envelope-open-text"></i>
                            Message Content
                        </div>
                        <div class="panel-body">
                            <div class="message-box"><?php echo htmlspecialchars($message['message_text']); ?></div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <i class="fa-solid fa-paperclip"></i>
                            Attachments
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($attachments)): ?>
                                <div class="attachment-list">
                                    <?php foreach ($attachments as $file): ?>
                                        <div class="attachment-item">
                                            <div class="attachment-left">
                                                <div class="file-icon">
                                                    <i class="fa-solid fa-file-lines"></i>
                                                </div>
                                                <div>
                                                    <div class="attachment-name">
                                                        <?php echo htmlspecialchars($file['file_name']); ?>
                                                    </div>
                                                    <div class="attachment-meta">
                                                        <?php echo htmlspecialchars($file['mime_type'] ?: 'Unknown type'); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <a class="btn btn-download"
                                                href="download_employee_message_file.php?id=<?php echo $file['file_id']; ?>"
                                                target="_blank">
                                                <i class="fa-solid fa-download"></i> Download
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-text">No files were attached to this message.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="panel">
                        <div class="panel-header">
                            <i class="fa-regular fa-id-badge"></i>
                            Metadata Details
                        </div>
                        <div class="panel-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-label">
                                        <?php echo ($view_type === 'sent') ? 'Recipient Name' : 'Sender Name'; ?>
                                    </div>
                                    <div class="info-value">
                                        <?php echo $partyDisplayName; ?>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">Account Role</div>
                                    <div class="info-value">
                                        <?php echo $roleLabel; ?>
                                    </div>
                                </div>

                                <?php if ($view_type !== 'sent' && !empty($message['party_emp_num'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">ID Number</div>
                                        <div class="info-value">
                                            <?php echo htmlspecialchars($message['party_emp_num']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="info-item">
                                    <div class="info-label">Date Sent</div>
                                    <div class="info-value">
                                        <?php echo date("F d, Y", strtotime($message['sent_at'])); ?>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-label">Time Sent</div>
                                    <div class="info-value">
                                        <?php echo date("h:i A", strtotime($message['sent_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="actions">
                        <a href="<?php echo $backUrl; ?>" class="btn btn-back-action">
                            <i class="fa-solid fa-arrow-left"></i> Go Back
                        </a>

                        <?php if ($view_type !== 'sent'): ?>
                            <a href="employee_reply_message.php?id=<?php echo (int) $message['message_id']; ?>"
                                class="btn btn-reply">
                                <i class="fa-solid fa-reply"></i> Reply
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>