<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$adminDisplayName = $_SESSION['username'] ?? 'Administrator';
$adminEmployeeId = (int) ($_SESSION['EmployeeID'] ?? 0);
if ($adminEmployeeId > 0) {
    $adminNameStmt = $conn->prepare("SELECT COALESCE(NULLIF(d.name, ''), a.username) AS display_name FROM admin a LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID WHERE a.EmployeeID = ? LIMIT 1");
    if ($adminNameStmt) {
        $adminNameStmt->bind_param('i', $adminEmployeeId);
        $adminNameStmt->execute();
        $adminNameRow = $adminNameStmt->get_result()->fetch_assoc();
        $adminDisplayName = $adminNameRow['display_name'] ?? $adminDisplayName;
        $adminNameStmt->close();
    }
}

$employees = [];
$stmt = $conn->prepare("SELECT a.EmployeeID, a.Employeenumber, COALESCE(NULLIF(d.name, ''), a.username) AS display_name FROM admin a LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID WHERE a.role = 'staff' ORDER BY display_name ASC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Administration | Tapuyan National High School</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --header-height: 70px;
            --sidebar-width: 270px;
            --primary: #0f172a;
            --accent: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --background: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #334155;
            --muted: #64748b;
            --radius: 8px;
            --shadow: 0 4px 12px rgba(15, 23, 42, .06);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            overflow: hidden;
            color: var(--text);
            background: var(--background);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .leave-main {
            height: calc(100vh - var(--header-height));
            margin-left: var(--sidebar-width);
            padding: 2rem;
            overflow-y: auto;
        }

        .admin-welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
            padding: 1.25rem 1.4rem;
            color: #fff;
            background: #172554;
            border-left: 5px solid #facc15;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .admin-welcome-copy {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .admin-welcome-avatar {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            color: #172554;
            background: #facc15;
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .admin-welcome h1 {
            margin: 0 0 .2rem;
            color: #fff;
            font-size: 1.25rem;
        }

        .admin-welcome p {
            color: #cbd5e1;
            font-size: .82rem;
        }

        .admin-welcome-date {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .65rem;
            color: #e2e8f0;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 6px;
            font-size: .75rem;
            white-space: nowrap;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .eyebrow {
            display: block;
            margin-bottom: .3rem;
            color: var(--accent);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .page-header h1 {
            color: var(--primary);
            font-size: 1.65rem;
        }

        .page-header p {
            margin-top: .35rem;
            color: var(--muted);
            font-size: .9rem;
        }

        .example-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .7rem;
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .workspace-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(280px, .7fr);
            gap: 1.25rem;
            align-items: start;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .panel-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            color: var(--accent);
            background: #eff6ff;
            border-radius: 7px;
        }

        .panel-header h2 {
            color: var(--primary);
            font-size: 1rem;
        }

        .panel-header p {
            margin-top: .15rem;
            color: var(--muted);
            font-size: .78rem;
        }

        .panel-body {
            padding: 1.25rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            color: #475569;
            font-size: .76rem;
            font-weight: 750;
            text-transform: uppercase;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            min-height: 42px;
            padding: .65rem .75rem;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            outline: none;
            font: inherit;
            font-size: .88rem;
        }

        .field textarea {
            min-height: 76px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .drop-zone {
            display: grid;
            place-items: center;
            min-height: 180px;
            padding: 1.5rem;
            text-align: center;
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius);
            background: #f8fafc;
            cursor: pointer;
            transition: .2s ease;
        }

        .drop-zone:hover,
        .drop-zone.dragging {
            border-color: var(--accent);
            background: #eff6ff;
        }

        .drop-zone.has-file {
            border-style: solid;
            border-color: #86efac;
            background: #f0fdf4;
        }

        .drop-zone input {
            display: none;
        }

        .drop-zone i {
            margin-bottom: .75rem;
            color: var(--accent);
            font-size: 2rem;
        }

        .drop-zone strong {
            display: block;
            color: var(--primary);
            font-size: .9rem;
        }

        .drop-zone span {
            display: block;
            margin-top: .3rem;
            color: var(--muted);
            font-size: .76rem;
        }

        .file-preview {
            display: none;
            align-items: center;
            gap: .7rem;
            margin-top: .8rem;
            padding: .75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .file-preview.show {
            display: flex;
        }

        .file-preview i {
            color: var(--danger);
            font-size: 1.3rem;
        }

        .file-meta {
            flex: 1;
            min-width: 0;
        }

        .file-meta strong {
            display: block;
            overflow: hidden;
            color: var(--primary);
            font-size: .82rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-meta span {
            color: var(--muted);
            font-size: .72rem;
        }

        .icon-button {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            color: var(--danger);
            background: #fef2f2;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .65rem;
            margin-top: 1.15rem;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 40px;
            padding: .6rem 1rem;
            border: 0;
            border-radius: 6px;
            font-size: .84rem;
            font-weight: 700;
            cursor: pointer;
        }

        .button-secondary {
            color: #475569;
            background: #f1f5f9;
            border: 1px solid var(--border);
        }

        .button-primary {
            color: #fff;
            background: var(--accent);
        }

        .button-primary:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .progress-wrap {
            display: none;
            margin-top: 1rem;
        }

        .progress-wrap.show {
            display: block;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: .35rem;
            color: var(--muted);
            font-size: .75rem;
        }

        .progress-track {
            height: 7px;
            overflow: hidden;
            background: #e2e8f0;
            border-radius: 5px;
        }

        .progress-bar {
            width: 0;
            height: 100%;
            background: var(--success);
            transition: width .25s ease;
        }

        .summary-list {
            display: grid;
            gap: .9rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .summary-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #475569;
            background: #f1f5f9;
            border-radius: 6px;
        }

        .summary-item strong {
            display: block;
            color: var(--primary);
            font-size: .82rem;
        }

        .summary-item span {
            color: var(--muted);
            font-size: .73rem;
        }

        .recent-panel {
            margin-top: 1.25rem;
            overflow: hidden;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .credit-toolbar,
        .credit-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 0 1.25rem 1rem;
        }

        .credit-toolbar {
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .credit-search {
            display: flex;
            align-items: center;
            gap: .5rem;
            width: min(360px, 100%);
            padding: .55rem .7rem;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .credit-search input {
            width: 100%;
            border: 0;
            outline: 0;
            font: inherit;
            color: var(--text);
        }

        .credit-page-info {
            color: var(--muted);
            font-size: .78rem;
        }

        .credit-pagination .button {
            min-height: 34px;
            padding: .45rem .7rem;
            font-size: .76rem;
        }

        .credit-pagination .button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: .8rem 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: .8rem;
        }

        th {
            color: var(--muted);
            background: #f8fafc;
            font-size: .7rem;
            text-transform: uppercase;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .status.available {
            color: #15803d;
            background: #f0fdf4;
        }

        .status.review {
            color: #a16207;
            background: #fefce8;
        }

        .toast {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            z-index: 2000;
            max-width: 340px;
            padding: .85rem 1rem;
            color: #fff;
            background: var(--primary);
            border-radius: 7px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .2);
            opacity: 0;
            transform: translateY(12px);
            pointer-events: none;
            transition: .2s ease;
            font-size: .82rem;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 1000px) {
            .workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .leave-main {
                height: auto;
                min-height: calc(100vh - var(--header-height));
                margin-left: 0;
                padding: 1.25rem;
            }
        }

        @media (max-width: 640px) {
            .admin-welcome {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-welcome-copy {
                align-items: flex-start;
            }

            .page-header {
                flex-direction: column;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeradmin.php'; ?>
    <?php $activeAdminView = 'leave'; ?>
    <?php include_once '../includes/sidenav.php'; ?>

    <main class="leave-main">

        </section>

        <header class="page-header">
            <div>
                <span class="eyebrow">Staff & HR Management</span>
                <h1>Leave Administration</h1>
                <p>Upload leave records and make current credit balances available to employees.</p>
            </div>
            <span class="example-badge"><i class="fa-solid fa-database"></i> Live records</span>
        </header>

        <div class="workspace-grid">
            <section class="panel">
                <div class="panel-header">
                    <div class="panel-icon"><i class="fa-solid fa-file-arrow-up"></i></div>
                    <div>
                        <h2>Upload Leave Document</h2>
                        <p>Complete the record details before selecting a file.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <form id="leaveUploadForm" action="../controllers/admin_leave_upload.php" method="post"
                        enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="field full">
                                <label for="employee">Employee</label>
                                <select id="employee" name="employee_id" required>
                                    <option value="">Select an employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo (int) $employee['EmployeeID']; ?>">
                                            <?php echo htmlspecialchars($employee['display_name'] . ' - ' . $employee['Employeenumber']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="leaveType">Leave Category</label>
                                <select id="leaveType" name="leave_type" required>
                                    <option value="vacation">Vacation Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="other">Other Leave</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="documentType">Document Type</label>
                                <select id="documentType" name="document_type" required>
                                    <option value="">Select document type</option>
                                    <option>Application for Leave</option>
                                    <option>Leave Credit Summary</option>
                                    <option>Medical Certificate</option>
                                    <option>Supporting Document</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="status">Record Status</label>
                                <select id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="referenceNumber">Reference Number</label>
                                <input id="referenceNumber" name="reference_number" type="text"
                                    placeholder="Example: LV-2026-001">
                            </div>
                            <div class="field">
                                <label for="periodStart">Period Start</label>
                                <input id="periodStart" name="period_start" type="date" required>
                            </div>
                            <div class="field">
                                <label for="periodEnd">Period End (15-day default)</label>
                                <input id="periodEnd" name="period_end" type="date" required>
                            </div>
                            <div class="field full">
                                <label for="remarks">Remarks</label>
                                <textarea id="remarks" name="remarks"
                                    placeholder="Add an optional administrative note"></textarea>
                            </div>
                            <div class="field full">
                                <label>Leave Document</label>
                                <label class="drop-zone" id="dropZone" for="leaveFile">
                                    <input id="leaveFile" name="attachment" type="file"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                    <span id="dropContent">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <strong>Choose a file or drag it here</strong>
                                        <span>PDF, DOC, DOCX, JPG, or PNG up to 10 MB</span>
                                    </span>
                                </label>
                                <div class="file-preview" id="filePreview">
                                    <i class="fa-solid fa-file-pdf" id="fileIcon"></i>
                                    <div class="file-meta">
                                        <strong id="fileName"></strong>
                                        <span id="fileSize"></span>
                                    </div>
                                    <button class="icon-button" id="removeFile" type="button"
                                        title="Remove selected file">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="progress-wrap" id="progressWrap">
                            <div class="progress-label"><span id="progressStatus">Preparing upload</span><strong
                                    id="progressValue">0%</strong></div>
                            <div class="progress-track">
                                <div class="progress-bar" id="progressBar"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="button button-secondary" id="resetButton" type="reset"><i
                                    class="fa-solid fa-rotate-left"></i> Reset</button>
                            <button class="button button-primary" id="uploadButton" type="submit"><i
                                    class="fa-solid fa-arrow-right"></i> Proceed & Create Leave</button>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="panel">
                <div class="panel-header">
                    <div class="panel-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div>
                        <h2>Upload Checklist</h2>
                        <p>Requirements for a complete record.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="summary-list">
                        <div class="summary-item">
                            <div class="summary-icon"><i class="fa-solid fa-user"></i></div>
                            <div><strong>Employee selected</strong><span>Assign the document to the correct
                                    employee.</span></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon"><i class="fa-solid fa-calendar-days"></i></div>
                            <div><strong>Leave period recorded</strong><span>End date cannot be earlier than the start
                                    date.</span></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon"><i class="fa-solid fa-file-shield"></i></div>
                            <div><strong>Supported file selected</strong><span>Maximum accepted file size is 10
                                    MB.</span></div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

    </main>

    <div class="toast" id="toast"></div>

    <script>
        const form = document.getElementById('leaveUploadForm');
        const fileInput = document.getElementById('leaveFile');
        const dropZone = document.getElementById('dropZone');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');
        const removeFile = document.getElementById('removeFile');
        const progressWrap = document.getElementById('progressWrap');
        const progressBar = document.getElementById('progressBar');
        const progressValue = document.getElementById('progressValue');
        const progressStatus = document.getElementById('progressStatus');
        const uploadButton = document.getElementById('uploadButton');
        const toast = document.getElementById('toast');
        const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        const maxSize = 10 * 1024 * 1024;

        function showToast(message) {
            toast.textContent = message;
            toast.classList.add('show');
            window.setTimeout(() => toast.classList.remove('show'), 3200);
        }

        const query = new URLSearchParams(window.location.search);
        const message = query.get('message');
        if (message) {
            showToast(message);
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function clearFile() {
            fileInput.value = '';
            filePreview.classList.remove('show');
            dropZone.classList.remove('has-file');
        }

        function setFile(file) {
            if (!file) return;
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(extension)) {
                clearFile();
                showToast('Select a PDF, DOC, DOCX, JPG, or PNG file.');
                return;
            }
            if (file.size > maxSize) {
                clearFile();
                showToast('The selected file exceeds the 10 MB limit.');
                return;
            }
            fileName.textContent = file.name;
            fileSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
            fileIcon.className = ['jpg', 'jpeg', 'png'].includes(extension) ? 'fa-solid fa-file-image' : 'fa-solid fa-file-pdf';
            filePreview.classList.add('show');
            dropZone.classList.add('has-file');
        }

        fileInput.addEventListener('change', () => setFile(fileInput.files[0]));
        removeFile.addEventListener('click', clearFile);

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, event => {
                event.preventDefault();
                dropZone.classList.add('dragging');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, event => {
                event.preventDefault();
                dropZone.classList.remove('dragging');
            });
        });

        dropZone.addEventListener('drop', event => {
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const transfer = new DataTransfer();
            transfer.items.add(file);
            fileInput.files = transfer.files;
            setFile(file);
        });

        form.addEventListener('reset', () => {
            window.setTimeout(() => {
                clearFile();
                progressWrap.classList.remove('show');
                progressBar.style.width = '0';
            });
        });

        const periodStart = document.getElementById('periodStart');
        const periodEnd = document.getElementById('periodEnd');
        function setDefaultPeriodEnd() {
            if (!periodStart.value || periodEnd.dataset.manual === 'true') return;
            const date = new Date(`${periodStart.value}T00:00:00`);
            date.setDate(date.getDate() + 14);
            periodEnd.value = date.toISOString().slice(0, 10);
        }
        periodStart.addEventListener('change', () => {
            periodEnd.dataset.manual = 'false';
            setDefaultPeriodEnd();
        });
        periodEnd.addEventListener('input', () => {
            periodEnd.dataset.manual = 'true';
        });
        setDefaultPeriodEnd();

        form.addEventListener('submit', event => {
            const start = periodStart.value;
            const end = periodEnd.value;
            uploadButton.disabled = true;
            progressWrap.classList.add('show');
            progressStatus.textContent = 'Uploading document';
            progressBar.style.width = '100%';
            progressValue.textContent = '100%';
        });

    </script>
</body>

</html>