<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];

$allowedColumns = [
    'dll_file' => 'DLL (Daily Lesson Log)',
    'prc_file' => 'PRC License',
    'saln_file' => 'SALN',
    'ipcr_file' => 'IPCR',
    'diploma_file' => 'DIPLOMA',
    'tor_file' => 'TOR (Transcript of Records)',
    'itr_file' => 'ITR',
    'itr_file_2' => 'ICS / RIS',
    'service_record_file' => 'SERVICE RECORD',
    'appointment_file' => 'APPOINTMENT',
    'trainings_awards_file' => 'TRAININGS & AWARDS',
    'pds_file' => 'PDS (Personal Data Sheet)',
    'ICS' => 'ICS'
];

// 1. Fetch multi-file records from our new table with safe UTF-8 encoding
$multiFiles = [];
$stmtMulti = $conn->prepare("SELECT file_id, doc_column, file_name, uploaded_at FROM employee_document_files WHERE EmployeeID = ? ORDER BY uploaded_at DESC");
if ($stmtMulti) {
    $stmtMulti->bind_param("i", $EmployeeID);
    $stmtMulti->execute();
    $resMulti = $stmtMulti->get_result();
    while ($f = $resMulti->fetch_assoc()) {
        $multiFiles[$f['doc_column']][] = [
            'file_id' => (int) $f['file_id'],
            'file_name' => mb_convert_encoding($f['file_name'], 'UTF-8', 'UTF-8'),
            'uploaded_at' => $f['uploaded_at']
        ];
    }
    $stmtMulti->close();
}

// 2. Fetch legacy single-column files (for backward compatibility if not yet migrated)
$stmtLegacy = $conn->prepare("
    SELECT prc_file, file_leave, dll_file, saln_file, ipcr_file, diploma_file, tor_file, itr_file,
           itr_file_2, service_record_file, appointment_file, trainings_awards_file, pds_file, ICS
    FROM employee_documents WHERE EmployeeID = ? LIMIT 1
");
$stmtLegacy->bind_param("i", $EmployeeID);
$stmtLegacy->execute();
$legacyRow = $stmtLegacy->get_result()->fetch_assoc() ?: [];
$stmtLegacy->close();

// 🟢 Convert legacy binary data into boolean flags to prevent JSON crashes & browser lags
$legacyFlags = [];
foreach ($legacyRow as $col => $val) {
    $legacyFlags[$col] = !empty($val);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents | Tapuyan National High School</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #e4f40dff;
            --primary-hover: #cfd91b;
            --success-color: #198754;
            --success-bg: #d1e7dd;
            --danger-color: #dc3545;
            --danger-bg: #f8d7da;
            --info-color: #0d6efd;
            --info-bg: #e7f1ff;
            --warning-color: #856404;
            --warning-bg: #fff3cd;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            --border-radius: 12px;
            --header-height: 60px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            overflow: hidden;
            height: 100vh;
        }

        .content-area {
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.4rem 1.8rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(180deg, #ffffff, #fafbfd);
        }

        .page-title h2 {
            font-size: 1.6rem;
            margin-bottom: 0.2rem;
        }

        .page-title p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .summary-chip {
            background: #f1f3f5;
            border: 1px solid var(--border-color);
            padding: 0.65rem 0.9rem;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .documents-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .documents-table thead th {
            text-align: left;
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            color: var(--text-muted);
            background: #f8f9fb;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
        }

        .documents-table tbody td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #edf0f2;
            vertical-align: middle;
        }

        .documents-table tbody tr:hover {
            background: #fcfdff;
        }

        .doc-main {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .doc-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f5;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .doc-name {
            font-weight: 700;
            font-size: 0.96rem;
        }

        .doc-sub {
            color: var(--text-muted);
            font-size: 0.82rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .status-uploaded {
            color: var(--success-color);
            background: var(--success-bg);
        }

        .status-missing {
            color: var(--danger-color);
            background: var(--danger-bg);
        }

        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: none;
            border-radius: 8px;
            padding: 0.55rem 0.85rem;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: 0.18s ease;
            white-space: nowrap;
        }

        .btn-view {
            background: var(--info-bg);
            color: var(--info-color);
        }

        .btn-view:hover {
            background: #d7e8ff;
        }

        .btn-upload {
            background: #eef7cf;
            color: #4e6500;
        }

        .btn-upload:hover {
            background: #e4f0b4;
        }

        .btn-delete {
            background: #fdebec;
            color: #9f1239;
        }

        .btn-delete:hover {
            background: #f9d7db;
        }

        .footer-note {
            padding: 1rem 1.4rem;
            font-size: 0.88rem;
            color: var(--text-muted);
            background: #fafbfc;
            border-top: 1px solid var(--border-color);
        }

        .toast {
            position: fixed;
            right: 20px;
            bottom: 20px;
            min-width: 260px;
            max-width: 360px;
            background: #333;
            color: white;
            padding: 14px 16px;
            border-radius: 10px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
            opacity: 0;
            pointer-events: none;
            transform: translateY(15px);
            transition: all 0.25s ease;
            z-index: 10000;
            font-size: 0.92rem;
        }

        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .toast.success {
            background: #198754;
        }

        .toast.error {
            background: #dc3545;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            padding: 20px;
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 24px;
            border-radius: 14px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            position: relative;
            animation: modalFade 0.2s ease;
            max-height: 85vh;
            overflow-y: auto;
        }

        @keyframes modalFade {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close-btn {
            position: absolute;
            top: 14px;
            right: 16px;
            border: none;
            background: transparent;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .modal-subtext {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .drop-zone {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 28px 18px;
            text-align: center;
            cursor: pointer;
            background: #fcfcfc;
            transition: all 0.2s ease;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: var(--primary-color);
            background: #f8faff;
        }

        .drop-zone-content {
            pointer-events: none;
        }

        .drop-icon {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .drop-title {
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .drop-subtitle {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        .file-preview-list {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 180px;
            overflow-y: auto;
        }

        .file-preview-item {
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
        }

        .upload-spinner {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 3px solid #ddd;
            border-top: 3px solid var(--info-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .modal-actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* History modal list */
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 12px;
        }

        .history-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 10px;
        }

        .history-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            margin-right: 12px;
        }

        .history-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-meta {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            body {
                overflow: auto;
                height: auto;
            }

            .content-area {
                height: auto;
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .documents-table {
                min-width: 760px;
            }
        }
    </style>
</head>

<body>
    <div id="toast" class="toast"></div>
    <main class="content-area">
        <div class="page-card">
            <div class="page-header">
                <div class="page-title">
                    <h2>My Documents</h2>
                    <p>Manage your uploaded requirements and records. You can upload multiple files per requirement.</p>
                </div>
                <div class="summary-chip">
                    <i class="fa-solid fa-folder-open"></i>
                    <?php
                    $activeCategories = 0;
                    foreach ($allowedColumns as $col => $lbl) {
                        if (!empty($multiFiles[$col]) || !empty($legacyFlags[$col])) {
                            $activeCategories++;
                        }
                    }
                    echo $activeCategories . ' of ' . count($allowedColumns) . ' categories active';
                    ?>
                </div>
            </div>

            <div class="table-wrap">
                <table class="documents-table">
                    <thead>
                        <tr>
                            <th>Document Requirement</th>
                            <th>Status & File Count</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allowedColumns as $column => $label): ?>
                            <?php
                            // Combine new table files and legacy files
                            $files = $multiFiles[$column] ?? [];
                            $hasLegacy = !empty($legacyFlags[$column]);
                            $fileCount = count($files) + ($hasLegacy && count($files) === 0 ? 1 : 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="doc-main">
                                        <div class="doc-icon"
                                            style="<?php echo $fileCount > 0 ? 'background:#e6f8f0; color:#0f5132;' : ''; ?>">
                                            <?php if ($fileCount > 0): ?>
                                                <i class="fa-solid fa-file-circle-check"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-file-arrow-up"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="doc-name"><?php echo htmlspecialchars($label); ?></div>
                                            <div class="doc-sub">
                                                <?php echo $column === 'dll_file' ? 'Weekly lesson logs (multiple uploads allowed)' : 'Employee document record'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($fileCount > 0): ?>
                                        <span class="status-badge status-uploaded">
                                            <i class="fa-solid fa-check-double"></i>
                                            <?php echo $fileCount . ($fileCount === 1 ? ' File Uploaded' : ' Files Uploaded'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-missing">
                                            <i class="fa-solid fa-xmark"></i> Not Uploaded
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo $fileCount > 0 ? 'Ready / Available' : 'Waiting for upload'; ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <?php if ($fileCount > 0): ?>
                                            <button type="button" class="btn btn-view btn-view-history"
                                                data-col="<?php echo htmlspecialchars($column, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-lbl="<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa-solid fa-folder-open"></i> View Files (<?php echo $fileCount; ?>)
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-upload btn-add-files"
                                            data-col="<?php echo htmlspecialchars($column, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-lbl="<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-plus"></i> Add Files
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="footer-note">
                <i class="fa-solid fa-circle-info"></i> Note: Uploading new files will <strong>not</strong> delete or
                replace your previously uploaded DLLs or records. Old files remain safely archived.
            </div>
        </div>
    </main>

    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <button type="button" class="close-btn" onclick="closeUploadModal()">&times;</button>

            <h3 id="uploadModalTitle">Upload Documents</h3>
            <p class="modal-subtext">You can select and upload <strong>multiple files</strong> at once. Max 5MB per
                file.</p>

            <form id="uploadForm" enctype="multipart/form-data">
                <input type="hidden" name="file_column" id="uploadFileColumn">

                <div id="dropZone" class="drop-zone">
                    <input type="file" name="new_files[]" id="newFileInput" multiple
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*" hidden>

                    <div class="drop-zone-content">
                        <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up" style="color:#0d6efd;"></i></div>
                        <div class="drop-title">Drag & drop files here</div>
                        <div class="drop-subtitle">or click to browse your computer</div>
                    </div>
                </div>

                <div id="filePreviewList" class="file-preview-list" style="display:none;"></div>

                <div id="uploadSpinner" class="upload-spinner" style="display:none;">
                    <div class="spinner"></div>
                    <span>Uploading files to secure archive...</span>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-delete" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="btn btn-upload" id="saveBtn" style="background:#0d6efd; color:#fff;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="historyModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <button type="button" class="close-btn" onclick="closeHistoryModal()">&times;</button>

            <h3 id="historyModalTitle">Uploaded Files</h3>
            <p class="modal-subtext">View, download, or remove specific files from your records.</p>

            <div id="historyListContainer" class="history-list">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-delete" onclick="closeHistoryModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Safely encode JSON with empty fallback objects to prevent syntax errors
        const allUploadedFiles = <?php echo json_encode(empty($multiFiles) ? new stdClass() : $multiFiles) ?: '{}'; ?>;
        const legacyFiles = <?php echo json_encode(empty($legacyFlags) ? new stdClass() : $legacyFlags) ?: '{}'; ?>;

        const uploadModal = document.getElementById('uploadModal');
        const historyModal = document.getElementById('historyModal');
        const uploadFileColumn = document.getElementById('uploadFileColumn');
        const uploadModalTitle = document.getElementById('uploadModalTitle');
        const dropZone = document.getElementById('dropZone');
        const newFileInput = document.getElementById('newFileInput');
        const filePreviewList = document.getElementById('filePreviewList');
        const uploadSpinner = document.getElementById('uploadSpinner');
        const uploadForm = document.getElementById('uploadForm');
        const saveBtn = document.getElementById('saveBtn');
        const toast = document.getElementById('toast');

        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            setTimeout(() => { toast.className = 'toast'; }, 3500);
        }

        // --- UPLOAD MODAL LOGIC ---
        function openUploadModal(column, label) {
            uploadModal.style.display = 'block';
            uploadFileColumn.value = column;
            uploadModalTitle.textContent = 'Add Files to: ' + label;
            clearSelectedFiles();
        }

        function closeUploadModal() {
            uploadModal.style.display = 'none';
            uploadForm.reset();
            filePreviewList.style.display = 'none';
            uploadSpinner.style.display = 'none';
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Now';
        }

        function updateFilePreviewList(files) {
            filePreviewList.innerHTML = '';
            if (!files || files.length === 0) {
                filePreviewList.style.display = 'none';
                return;
            }

            filePreviewList.style.display = 'flex';
            Array.from(files).forEach(file => {
                const item = document.createElement('div');
                item.className = 'file-preview-item';
                item.innerHTML = `
                    <span><i class="fa-solid fa-file-lines" style="color:#0d6efd; margin-right:6px;"></i> ${file.name}</span>
                    <span style="color:#6c757d; font-size:0.8rem;">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                `;
                filePreviewList.appendChild(item);
            });
        }

        function clearSelectedFiles() {
            newFileInput.value = '';
            updateFilePreviewList(null);
        }

        dropZone.addEventListener('click', () => newFileInput.click());
        newFileInput.addEventListener('change', function () { updateFilePreviewList(this.files); });

        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                newFileInput.files = e.dataTransfer.files;
                updateFilePreviewList(e.dataTransfer.files);
            }
        });

        uploadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!newFileInput.files.length) {
                showToast('Please select at least one file to upload.', 'error');
                return;
            }

            const formData = new FormData(uploadForm);
            uploadSpinner.style.display = 'flex';
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';

            try {
                const response = await fetch('process_update_document.php', { method: 'POST', body: formData });
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (err) {
                    console.error('Invalid JSON:', text);
                    showToast('Server error during upload processing.', 'error');
                    uploadSpinner.style.display = 'none';
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Now';
                    return;
                }

                uploadSpinner.style.display = 'none';
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Now';

                if (result.success) {
                    showToast(result.message || 'Files uploaded successfully!', 'success');
                    closeUploadModal();
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    showToast(result.message || 'Upload failed.', 'error');
                }
            } catch (error) {
                console.error(error);
                uploadSpinner.style.display = 'none';
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Now';
                showToast('Something went wrong during upload.', 'error');
            }
        });

        // --- VIEW HISTORY MODAL LOGIC ---
        function openHistoryModal(column, label) {
            historyModal.style.display = 'block';
            document.getElementById('historyModalTitle').textContent = label + ' - File Archive';
            const container = document.getElementById('historyListContainer');
            container.innerHTML = '';

            const files = allUploadedFiles[column] || [];
            const legacyVal = legacyFiles[column];

            if (files.length === 0 && !legacyVal) {
                container.innerHTML = '<p style="color:#6c757d; text-align:center; padding:20px;">No files found.</p>';
                return;
            }

            // Render multi-file rows
            files.forEach(f => {
                const dateStr = f.uploaded_at ? new Date(f.uploaded_at).toLocaleString() : 'Recent upload';
                const div = document.createElement('div');
                div.className = 'history-item';
                div.innerHTML = `
                    <div class="history-info">
                        <span class="history-name" title="${f.file_name}"><i class="fa-solid fa-file-lines" style="color:#0d6efd; margin-right:6px;"></i> ${f.file_name}</span>
                        <span class="history-meta">Uploaded on ${dateStr}</span>
                    </div>
                    <div style="display:flex; gap:6px; flex-shrink:0;">
                        <a href="view_document.php?file_id=${f.file_id}" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>
                        <button type="button" class="btn btn-delete btn-del-single" data-id="${f.file_id}" data-col="${column}"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `;
                container.appendChild(div);
            });

            // Render legacy single-column file if it exists and no new table entries exist
            if (legacyVal && files.length === 0) {
                const div = document.createElement('div');
                div.className = 'history-item';
                div.innerHTML = `
                    <div class="history-info">
                        <span class="history-name"><i class="fa-solid fa-file-circle-check" style="color:#198754; margin-right:6px;"></i> Archived Requirement File</span>
                        <span class="history-meta">Legacy system upload</span>
                    </div>
                    <div style="display:flex; gap:6px; flex-shrink:0;">
                        <a href="view_document.php?file=${column}" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>
                        <form action="delete_document.php" method="POST" onsubmit="return confirm('Remove this file?');" style="margin:0;">
                            <input type="hidden" name="file" value="${column}">
                            <button type="submit" class="btn btn-delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                `;
                container.appendChild(div);
            }
        }

        function closeHistoryModal() {
            historyModal.style.display = 'none';
        }

        async function deleteSingleFile(fileId, column) {
            if (!confirm("Are you sure you want to remove this specific file? Old files will remain safe.")) return;

            try {
                const formData = new FormData();
                formData.append('file_id', fileId);

                const response = await fetch('delete_document.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast('File deleted successfully.', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(result.message || 'Failed to delete file.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error removing file.', 'error');
            }
        }

        // Attach event listeners safely without breaking string quotes
        document.addEventListener('click', function (e) {
            // Check if Add Files button was clicked
            const addBtn = e.target.closest('.btn-add-files');
            if (addBtn) {
                openUploadModal(addBtn.getAttribute('data-col'), addBtn.getAttribute('data-lbl'));
                return;
            }

            // Check if View History button was clicked
            const viewBtn = e.target.closest('.btn-view-history');
            if (viewBtn) {
                openHistoryModal(viewBtn.getAttribute('data-col'), viewBtn.getAttribute('data-lbl'));
                return;
            }

            // Check if Delete single file button inside modal was clicked
            const delBtn = e.target.closest('.btn-del-single');
            if (delBtn) {
                deleteSingleFile(delBtn.getAttribute('data-id'), delBtn.getAttribute('data-col'));
                return;
            }

            // Close modals when clicking background overlay
            if (e.target === uploadModal) closeUploadModal();
            if (e.target === historyModal) closeHistoryModal();
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