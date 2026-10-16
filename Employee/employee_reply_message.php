<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];
$message_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($message_id <= 0) {
    die("Invalid message ID.");
}

// Fetch original message to reply to (Ensure the logged-in employee was actually the receiver)
$sql = "SELECT
            m.message_id,
            m.sender_id AS sender_id,
            m.sender_role,
            m.subject,
            m.message_text,
            m.sent_at,
            a.username AS admin_username,
            ed.name AS sender_name
        FROM messages m
        LEFT JOIN admin a ON m.sender_id = a.EmployeeID AND m.sender_role = 'admin'
        LEFT JOIN employee_details ed ON m.sender_id = ed.EmployeeID AND m.sender_role = 'employee'
        WHERE m.message_id = ?
          AND m.receiver_id = ?
          AND m.receiver_role = 'employee'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $message_id, $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
$original = $result->fetch_assoc();
$stmt->close();

if (!$original) {
    die("Original message not found or access denied.");
}

// Format the display name of who you are replying to
$recipientName = !empty($original['sender_name'])
    ? $original['sender_name']
    : (!empty($original['admin_username']) ? $original['admin_username'] : 'HR Admin');

// Prefix subject with RE: if not already present
$replySubject = (stripos($original['subject'], 'RE:') === 0)
    ? $original['subject']
    : 'RE: ' . $original['subject'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message | Employee Portal</title>
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
            max-width: 900px;
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

        .page-subtitle strong {
            color: var(--text-main);
            font-weight: 600;
        }

        /* --- Form Structure --- */
        .form-body {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .field input[type="text"],
        .field textarea {
            width: 100%;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text-main);
            background: var(--surface);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .field input[type="text"]:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .field textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.6;
        }

        /* --- Original Message Box --- */
        .original-box {
            background: var(--surface-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--text-muted);
            max-height: 250px;
            overflow-y: auto;
        }

        .original-box strong {
            color: var(--text-main);
            font-size: 0.95rem;
            display: block;
            margin-bottom: 8px;
        }

        .original-date {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 8px;
            display: block;
        }

        /* --- File Input --- */
        .file-upload-wrapper {
            border: 1px dashed var(--border-strong);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
            background: #fdfdfd;
            transition: background 0.15s ease;
        }

        .file-upload-wrapper:hover {
            background: var(--surface-hover);
        }

        .field input[type="file"] {
            font-size: 0.9rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .field input[type="file"]::file-selector-button {
            background: var(--surface);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm);
            padding: 6px 12px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            margin-right: 12px;
            transition: all 0.15s ease;
        }

        .field input[type="file"]::file-selector-button:hover {
            background: var(--surface-hover);
        }

        /* --- Buttons --- */
        .actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin-top: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-cancel {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border-strong);
        }

        .btn-cancel:hover {
            background: red;
            color: white;
        }

        .btn-send {
            background: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn-send:hover {
            background: var(--primary-hover);
        }

        @media (max-width: 640px) {
            .actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeremployee.php'; ?>

    <main class="content">
        <div class="topbar">
            <a href="view_employee_message.php?id=<?php echo (int) $original['message_id']; ?>" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Message
            </a>
        </div>

        <div class="page-card">
            <div class="page-header">
                <h1 class="page-title">Reply to Message</h1>
                <p class="page-subtitle">
                    Replying to:
                    <strong>
                        <?php echo htmlspecialchars($recipientName); ?>
                    </strong>
                    (
                    <?php echo htmlspecialchars(ucfirst($original['sender_role'])); ?>)
                </p>
            </div>

            <form class="form-body" action="../controllers/employee_reply_message_controller.php" method="POST"
                enctype="multipart/form-data">

                <input type="hidden" name="receiver_id" value="<?php echo (int) $original['sender_id']; ?>">
                <input type="hidden" name="receiver_role"
                    value="<?php echo htmlspecialchars($original['sender_role']); ?>">
                <input type="hidden" name="original_message_id" value="<?php echo (int) $original['message_id']; ?>">

                <div class="field">
                    <label>Original Message Reference</label>
                    <div class="original-box">

                        <strong>
                            <?php echo htmlspecialchars($original['subject']); ?>
                        </strong>
                        <?php echo nl2br(htmlspecialchars($original['message_text'])); ?>
                    </div>
                </div>

                <div class="field">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject"
                        value="<?php echo htmlspecialchars($replySubject); ?>" maxlength="255" required
                        autocomplete="off">
                </div>

                <div class="field">
                    <label for="message_text">Reply Message</label>
                    <textarea id="message_text" name="message_text" placeholder="Type your reply here..."
                        required></textarea>
                </div>

                <div class="field">
                    <label for="attachment">Attachment (Optional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="attachment" name="attachment"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*">
                    </div>
                </div>

                <div class="actions">
                    <a href="view_employee_message.php?id=<?php echo (int) $original['message_id']; ?>"
                        class="btn btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-send">
                        <i class="fa-regular fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>