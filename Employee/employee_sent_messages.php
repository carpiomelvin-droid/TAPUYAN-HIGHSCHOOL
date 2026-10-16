<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];

// Fetch messages sent by this employee
$sql = "SELECT
            m.message_id,
            m.receiver_id,
            m.receiver_role,
            m.subject,
            m.message_text,
            m.is_read,
            m.sent_at,
            m.read_at,
            a.username,
            a.role,
            ed.name AS receiver_name,
            (
                SELECT COUNT(*)
                FROM message_files mf
                WHERE mf.message_id = m.message_id
            ) AS attachment_count
        FROM messages m
        LEFT JOIN admin a ON m.receiver_id = a.EmployeeID
        LEFT JOIN employee_details ed ON a.EmployeeID = ed.EmployeeID
        WHERE m.sender_id = ?
          AND m.sender_role = 'employee'
          AND m.sender_deleted = 0
        ORDER BY m.sent_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sent Messages | HR Correspondence</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg-page: #f8fafc;
            --surface: #ffffff;
            --surface-hover: #f8fafc;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;

            --primary: #0f172a;
            --primary-hover: #1e293b;
            --accent: #2563eb;
            --accent-soft: #eff6ff;

            --danger: #ef4444;
            --danger-soft: #fef2f2;
            --success: #10b981;
            --success-soft: #ecfdf5;
            --warning: #f59e0b;
            --warning-soft: #fffbeb;

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
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px 40px;
        }

        /* --- Workspace Header --- */
        .workspace-header {
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 16px;
        }

        .workspace-title h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .workspace-title p {
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        /* --- Main Card Container --- */
        .page-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        /* --- Toolbar & Navigation --- */
        .message-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            background-color: #ffffff;
            flex-wrap: wrap;
        }

        .message-tabs {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: var(--radius-md);
        }

        .tab-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.15s ease;
        }

        .tab-link:hover {
            color: var(--text-main);
        }

        .tab-link.active {
            background: #ffffff;
            color: var(--text-main);
            box-shadow: var(--shadow-sm);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Minimalist Buttons --- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            height: 38px;
        }

        .btn-new {
            background: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn-new:hover {
            background: var(--primary-hover);
        }

        .btn-delete-selected {
            background: var(--surface);
            color: var(--danger);
            border-color: var(--border);
        }

        .btn-delete-selected:hover {
            background: var(--danger-soft);
            border-color: #fecaca;
        }

        .btn-open {
            background: var(--surface);
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 6px 12px;
            font-size: 0.8rem;
            height: 32px;
        }

        .btn-open:hover {
            background: var(--surface-hover);
            border-color: var(--border-strong);
        }

        /* --- Inbox Table Layout --- */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead th {
            padding: 12px 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            user-select: none;
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background-color 0.15s ease;
            background-color: var(--surface);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: var(--surface-hover);
        }

        tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        /* --- Table Cell Content --- */
        .receiver-name {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .receiver-meta {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .subject {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .preview {
            color: var(--text-muted);
            font-size: 0.85rem;
            max-width: 440px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Status Badges --- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-pending {
            background: var(--surface-hover);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .badge-read {
            background: var(--success-soft);
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .badge-file {
            background: var(--surface);
            color: var(--accent);
            border: 1px solid var(--border);
            font-weight: 500;
        }

        .date-main {
            font-size: 0.85rem;
            color: var(--text-main);
            font-weight: 500;
            margin-bottom: 2px;
        }

        .date-sub {
            color: var(--text-light);
            font-size: 0.78rem;
        }

        /* --- Empty State --- */
        .empty-state {
            text-align: center;
            padding: 64px 20px !important;
        }

        .empty-state .icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            background: #f1f5f9;
            color: var(--text-muted);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .empty-state .title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .empty-state .desc {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* --- Checkbox Styling --- */
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary);
            border-radius: 4px;
            border: 1px solid var(--border-strong);
        }

        /* --- Toast Notification --- */
        .toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            min-width: 280px;
            max-width: 380px;
            background: #1e293b;
            color: #ffffff;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 10000;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--danger);
        }

        @media (max-width: 768px) {
            .content {
                margin: 20px auto;
                padding: 0 16px 24px;
            }

            .workspace-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .message-topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .topbar-actions {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .topbar-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>

<body>
    <div id="toast" class="toast"></div>

    <?php include_once '../includes/headeremployee.php'; ?>

    <main class="content">
        <div class="workspace-header">
            <div class="workspace-title">
                <h1>Sent Messages</h1>
                <p>Track correspondence and documents you've submitted.</p>
            </div>
        </div>

        <div class="page-card">
            <form id="deleteForm" action="../controllers/delete_messages_controller.php" method="POST"
                onsubmit="return confirmDeleteSelected();">
                <input type="hidden" name="box_type" value="employee_sent">

                <div class="message-topbar">
                    <div class="message-tabs">
                        <a href="employee_inbox.php" class="tab-link">
                            <i class="fa-solid fa-inbox"></i>
                            Inbox
                        </a>

                        <a href="employee_sent_messages.php" class="tab-link active">
                            <i class="fa-regular fa-paper-plane"></i>
                            Sent
                        </a>
                    </div>

                    <div class="topbar-actions">
                        <button type="submit" class="btn btn-delete-selected" title="Delete selected messages">
                            <i class="fa-regular fa-trash-can"></i>
                            Delete
                        </button>

                        <a href="employee_send_message.php" class="btn btn-new">
                            <i class="fa-solid fa-pen-to-square"></i>
                            New Message
                        </a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 48px; text-align: center;">
                                    <input type="checkbox" id="selectAll" title="Select all">
                                </th>
                                <th style="width: 220px;">To</th>
                                <th>Subject & Message</th>
                                <th style="width: 130px;">Attachment</th>
                                <th style="width: 130px;">Status</th>
                                <th style="width: 140px;">Date Sent</th>
                                <th style="width: 90px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    // Fallback logic for receiver name[cite: 10]
                                    $toName = !empty($row['receiver_name']) ? $row['receiver_name'] : (!empty($row['username']) ? $row['username'] : 'HR Admin');
                                    $toRole = $row['role'] ?: 'HR Admin';
                                    $isRead = ((int) $row['is_read'] === 1);
                                    ?>

                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox" name="message_ids[]"
                                                value="<?php echo (int) $row['message_id']; ?>" class="message-checkbox">
                                        </td>

                                        <td>
                                            <div class="receiver-name">
                                                <?php echo htmlspecialchars($toName); ?>
                                            </div>
                                            <div class="receiver-meta">
                                                <?php echo htmlspecialchars(ucfirst($toRole)); ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="subject">
                                                <?php echo htmlspecialchars($row['subject']); ?>
                                            </div>
                                            <div class="preview">
                                                <?php echo htmlspecialchars(mb_strimwidth($row['message_text'], 0, 85, '...')); ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if ((int) $row['attachment_count'] > 0): ?>
                                                <span class="badge badge-file">
                                                    <i class="fa-solid fa-paperclip"></i>
                                                    File Attached
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-light); font-size: 0.8rem;">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($isRead): ?>
                                                <span class="badge badge-read">
                                                    <i class="fa-solid fa-check-double"></i> Seen
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">
                                                    <i class="fa-solid fa-check"></i> Delivered
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="date-main">
                                                <?php echo date('M d, Y', strtotime($row['sent_at'])); ?>
                                            </div>
                                            <div class="date-sub">
                                                <?php echo date('h:i A', strtotime($row['sent_at'])); ?>
                                            </div>
                                        </td>

                                        <td style="text-align: right;">
                                            <a class="btn btn-open"
                                                href="view_employee_message.php?id=<?php echo (int) $row['message_id']; ?>">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <div class="icon">
                                            <i class="fa-regular fa-paper-plane"></i>
                                        </div>
                                        <div class="title">No sent messages</div>
                                        <div class="desc">Messages you send to HR will appear here.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </main>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;

            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.message-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        function confirmDeleteSelected() {
            const checked = document.querySelectorAll('.message-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one item to delete.');
                return false;
            }
            return confirm('Are you sure you want to permanently delete the selected correspondence?');
        }

        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const message = params.get('message');

            if (status && message) {
                showToast(message, status);

                const url = new URL(window.location);
                url.searchParams.delete('status');
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.pathname);
            }
        })();
    </script>
</body>

</html>