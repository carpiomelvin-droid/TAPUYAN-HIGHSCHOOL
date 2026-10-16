<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];

// 🟢 Fetch all recipients, sorted purely alphabetically A-Z by name
$sqlRecipients = "SELECT a.EmployeeID, a.role, a.username, d.name
                  FROM admin a
                  LEFT JOIN employee_details d ON a.EmployeeID = d.EmployeeID
                  WHERE a.EmployeeID != ?
                  ORDER BY d.name ASC, a.username ASC";

$stmtRecipients = $conn->prepare($sqlRecipients);
if (!$stmtRecipients) {
    die("Query preparation failed: " . $conn->error);
}
$stmtRecipients->bind_param("i", $EmployeeID);
$stmtRecipients->execute();
$resultRecipients = $stmtRecipients->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message | HR Correspondence</title>
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

            --danger: #ef4444;
            --danger-soft: #fef2f2;
            --success: #10b981;

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

        /* --- Workspace Header --- */
        .workspace-header {
            margin-bottom: 24px;
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

        .form-body {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* --- Form Fields --- */
        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .field label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .selection-badge {
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .field input[type="text"],
        .field textarea {
            width: 100%;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text-main);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .field input[type="text"]:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: var(--text-light);
        }

        .field textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.6;
        }

        /* --- Checkbox Recipient Box --- */
        .recipients-box {
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            overflow: hidden;
            background: #ffffff;
        }

        .recipients-toolbar {
            padding: 10px 14px;
            background: var(--bg-page);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .select-all-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            cursor: pointer;
            user-select: none;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
            max-width: 260px;
        }

        .search-input-wrap i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .recipient-search {
            width: 100%;
            padding: 6px 10px 6px 28px !important;
            font-size: 0.82rem !important;
            border-radius: 999px !important;
            border: 1px solid var(--border) !important;
            background: #ffffff !important;
        }

        .recipients-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 6px 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }

        .recipient-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            cursor: pointer;
            transition: background 0.15s ease;
            user-select: none;
        }

        .recipient-item:hover {
            background: var(--surface-hover);
        }

        .recipient-item span {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
            border-radius: 4px;
        }

        /* --- Drag & Drop Zone --- */
        .drop-zone {
            border: 2px dashed var(--border-strong);
            border-radius: var(--radius-md);
            padding: 32px 20px;
            text-align: center;
            background: var(--surface-hover);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: var(--accent);
            background: var(--accent-soft);
        }

        .drop-zone-content {
            pointer-events: none;
        }

        .drop-icon {
            font-size: 2rem;
            color: var(--text-light);
            margin-bottom: 12px;
        }

        .drop-zone:hover .drop-icon,
        .drop-zone.dragover .drop-icon {
            color: var(--accent);
        }

        .drop-title {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .drop-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* --- File Preview --- */
        .file-preview {
            display: none;
            margin-top: 12px;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            align-items: center;
            gap: 12px;
        }

        .file-preview i {
            color: var(--accent);
            font-size: 1.2rem;
        }

        .file-name-text {
            flex: 1;
            word-break: break-word;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .clear-file-btn {
            border: none;
            background: var(--surface-hover);
            color: var(--text-muted);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .clear-file-btn:hover {
            background: var(--danger-soft);
            color: var(--danger);
        }

        /* --- Action Buttons --- */
        .action-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 16px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-back {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border-strong);
        }

        .btn-back:hover {
            background: var(--surface-hover);
        }

        .btn-send {
            background: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn-send:hover {
            background: var(--primary-hover);
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

            .form-body {
                padding: 20px;
            }

            .recipients-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-wrap {
                max-width: 100%;
            }

            .recipients-list {
                grid-template-columns: 1fr;
            }

            .action-row {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
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
                <h1>Draft Message</h1>
                <p>Send correspondence or document submissions to HR or colleagues.</p>
            </div>
        </div>

        <div class="page-card">
            <form class="form-body" action="../controllers/employee_send_message_controller.php" method="POST"
                enctype="multipart/form-data" onsubmit="return validateForm();">

                <div class="field">
                    <div class="field-header">
                        <label>Select Recipients</label>
                        <span id="selectionBadge" class="selection-badge">0 selected</span>
                    </div>

                    <div class="recipients-box">
                        <div class="recipients-toolbar">
                            <label class="select-all-label">
                                <input type="checkbox" id="selectAll">
                                Select All Recipients
                            </label>

                            <div class="search-input-wrap">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="recipientSearch" class="recipient-search"
                                    placeholder="Filter by name..." autocomplete="off">
                            </div>
                        </div>

                        <div class="recipients-list" id="recipientsList">
                            <?php
                            while ($row = $resultRecipients->fetch_assoc()) {
                                $rawName = !empty($row['name']) ? $row['name'] : $row['username'];
                                $cleanName = strtoupper(htmlspecialchars(trim($rawName)));
                                $empId = (int) $row['EmployeeID'];

                                // 🟢 Uses receiver_ids[] array format
                                echo '
                                <label class="recipient-item">
                                    <input type="checkbox" name="receiver_ids[]" value="' . $empId . '" class="recipient-checkbox">
                                    <span>' . $cleanName . '</span>
                                </label>';
                            }
                            $stmtRecipients->close();
                            ?>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" maxlength="255" placeholder="Enter message subject"
                        required>
                </div>

                <div class="field">
                    <label for="message_text">Message</label>
                    <textarea name="message_text" id="message_text" placeholder="Type your message here..."
                        required></textarea>
                </div>

                <div class="field">
                    <label>Attachment (Optional)</label>

                    <div id="dropZone" class="drop-zone">
                        <input type="file" name="attachment" id="attachment"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*" hidden>
                        <div class="drop-zone-content">
                            <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="drop-title">Drag & drop a file here</div>
                            <div class="drop-subtitle">or click to browse your computer (Max 5MB)</div>
                        </div>
                    </div>

                    <div id="filePreview" class="file-preview">
                        <i class="fa-solid fa-file-lines"></i>
                        <span id="fileNameText" class="file-name-text"></span>
                        <button type="button" class="clear-file-btn" onclick="clearSelectedFile()" title="Remove file">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="action-row">
                    <a href="employee_inbox.php" class="btn btn-back">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-send">
                        <i class="fa-regular fa-paper-plane"></i>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // --- Recipients Checkbox Logic ---
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.recipient-checkbox');
        const selectionBadge = document.getElementById('selectionBadge');
        const recipientSearch = document.getElementById('recipientSearch');
        const recipientItems = document.querySelectorAll('.recipient-item');

        function updateCounter() {
            const checkedCount = document.querySelectorAll('.recipient-checkbox:checked').length;
            selectionBadge.textContent = checkedCount + (checkedCount === 1 ? ' selected' : ' selected');
            selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        }

        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                // Only toggle checkboxes that are currently visible (not hidden by search filter)
                if (cb.closest('.recipient-item').style.display !== 'none') {
                    cb.checked = this.checked;
                }
            });
            updateCounter();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCounter);
        });

        // Live Search Filter
        recipientSearch.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            recipientItems.forEach(item => {
                const name = item.querySelector('span').textContent.toLowerCase();
                item.style.display = name.includes(term) ? 'flex' : 'none';
            });
        });

        // Ensure at least one recipient is checked before submitting
        function validateForm() {
            const checked = document.querySelectorAll('.recipient-checkbox:checked');
            if (checked.length === 0) {
                showToast('Please select at least one recipient.', 'error');
                return false;
            }
            return true;
        }

        // --- Attachment & Toast Logic ---
        const dropZone = document.getElementById('dropZone');
        const attachment = document.getElementById('attachment');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileNameText');

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;

            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        function updateFilePreview(file) {
            if (!file) {
                filePreview.style.display = 'none';
                fileNameText.textContent = '';
                return;
            }

            filePreview.style.display = 'flex';
            fileNameText.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        }

        function clearSelectedFile() {
            attachment.value = '';
            updateFilePreview(null);
        }

        dropZone.addEventListener('click', () => attachment.click());

        attachment.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    showToast('File is too large. Maximum is 5MB.', 'error');
                    clearSelectedFile();
                    return;
                }
                updateFilePreview(file);
            }
        });

        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', function () {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.size > 5 * 1024 * 1024) {
                    showToast('File is too large. Maximum is 5MB.', 'error');
                    return;
                }
                attachment.files = files;
                updateFilePreview(file);
            }
        });

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