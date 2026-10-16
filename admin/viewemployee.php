<?php
session_start();
include_once '../db/conn.php';
// include_once '../includes/headeradmin.php'; // Not needed, we build header here

// --- 1. Get the EmployeeID from the URL ---
$EmployeeID = $_GET['id'] ?? 0;

if ($EmployeeID == 0) {
    die("Error: No employee ID specified.");
}

// --- 2. Query 1: Get Details from 'admin' and 'employee_details' ---
$sql_details = "SELECT * FROM
                    admin AS a
                INNER JOIN
                    employee_details AS d ON a.EmployeeID = d.EmployeeID
                WHERE
                    a.EmployeeID = ?";

$stmt_details = $conn->prepare($sql_details);
$stmt_details->bind_param("i", $EmployeeID);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
$details = $result_details->fetch_assoc();
$stmt_details->close();

if (!$details) {
    echo '<script>
    alert("Employee profile has not been filled out yet.");
    window.location.href = "index.php";
    </script>';
    exit;
}

// --- 3. Query 2: Get Document Status from legacy 'employee_documents' ---
$sql_docs = "SELECT
                LENGTH(prc_file) as prc_file,
                LENGTH(file_leave) as file_leave,
                LENGTH(dll_file) as dll_file,
                LENGTH(saln_file) as saln_file,
                LENGTH(ipcr_file) as ipcr_file,
                LENGTH(diploma_file) as diploma_file,
                LENGTH(tor_file) as tor_file,
                LENGTH(itr_file) as itr_file,
                LENGTH(itr_file_2) as itr_file_2,
                LENGTH(service_record_file) as service_record_file,
                LENGTH(appointment_file) as appointment_file,
                LENGTH(trainings_awards_file) as trainings_awards_file,
                LENGTH(pds_file) as pds_file,
                LENGTH(ICS) as ICS
             FROM employee_documents WHERE EmployeeID = ?";

$stmt_docs = $conn->prepare($sql_docs);
$stmt_docs->bind_param("i", $EmployeeID);
$stmt_docs->execute();
$result_docs = $stmt_docs->get_result();
$documents = $result_docs->fetch_assoc();
$stmt_docs->close();

// --- 3.5. Query 3: Fetch multi-file records from new 'employee_document_files' table ---
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

// Helper array to build the document table
$doc_map = [
    'prc_file' => 'PRC',
    /* 'file_leave' => 'FILE LEAVE', */
    'dll_file' => 'DLL',
    'saln_file' => 'SALN',
    'ipcr_file' => 'IPCR',
    'diploma_file' => 'Diploma',
    'tor_file' => 'Transcript of Records (TOR)',
    'itr_file' => 'ITR',
    'itr_file_2' => 'ICS/RIS',
    'service_record_file' => 'Service Record',
    'appointment_file' => 'Appointment',
    'trainings_awards_file' => 'Trainings and Awards',
    'pds_file' => 'PDS',
    'ICS' => 'ICS(ICS)'
];

// --- 4. Create the Avatar Source ---
$avatar_src = "../src/images/avatar.png"; // A default placeholder
if (!empty($details['avatar_image']) && !empty($details['avatar_mime_type'])) {
    $avatar_src = "data:" . $details['avatar_mime_type'] . ";base64," . base64_encode($details['avatar_image']);
}

// --- 5. Get Admin's display name for the header ---
$admin_display_name = $_SESSION['username'] ?? 'Admin';
$AdminEmployeeID = (int) ($_SESSION['EmployeeID'] ?? 0);
$isSuperadmin = ($_SESSION['role'] ?? '') === 'superadmin';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$credentialResetStatus = $_GET['status'] ?? '';
$credentialResetMessage = $_GET['message'] ?? '';
$sql_admin_name = "SELECT name FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_admin_name = $conn->prepare($sql_admin_name);
if ($stmt_admin_name) {
    $stmt_admin_name->bind_param("i", $AdminEmployeeID);
    $stmt_admin_name->execute();
    $result_admin_name = $stmt_admin_name->get_result();
    if ($row_admin_name = $result_admin_name->fetch_assoc()) {
        $admin_display_name = $row_admin_name['name'];
    }
    $stmt_admin_name->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>View Employee: <?php echo htmlspecialchars($details['name']); ?></title>
    <style>
        :root {
            --primary: #1e293b;
            --primary-light: #334155;
            --accent-blue: #2563eb;
            --accent-blue-hover: #1d4ed8;
            --success: #10b981;
            --success-hover: #059669;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --info: #0ea5e9;
            --info-hover: #0284c7;
            --secondary: #64748b;
            --secondary-hover: #475569;

            --bg-main: #f1f5f9;
            --bg-card: #ffffff;
            --bg-input: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;

            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.02);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
        }

        /* --- Base Button Styles --- */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #fff;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-view {
            background-color: var(--accent-blue);
        }

        .btn-view:hover {
            background-color: var(--accent-blue-hover);
        }

        .btn-success {
            background-color: var(--success);
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--bg-main);
            color: var(--primary-light);
        }

        .btn-reset {
            background-color: #b45309;
        }

        .btn-reset:hover {
            background-color: #92400e;
        }

        .reset-alert {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid;
            font-size: .9rem;
            font-weight: 600;
        }

        .reset-alert-success {
            color: #166534;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .reset-alert-error {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .reset-modal-content form {
            display: grid;
            gap: .55rem;
        }

        .reset-modal-content label {
            color: var(--text-muted);
            font-size: .78rem;
            font-weight: 700;
            margin-top: .35rem;
        }

        .reset-modal-content input {
            width: 100%;
            padding: .7rem .8rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-input);
            color: var(--text-main);
            font: inherit;
        }

        .reset-password-field {
            position: relative;
        }

        .reset-password-field input {
            padding-right: 2.75rem;
        }

        .reset-password-toggle {
            position: absolute;
            top: 50%;
            right: .65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
        }

        .reset-password-toggle:hover,
        .reset-password-toggle:focus-visible {
            color: var(--accent-blue);
        }

        .reset-password-toggle:focus-visible {
            outline: 2px solid rgba(37, 99, 235, .35);
            outline-offset: 2px;
            border-radius: 50%;
        }

        .reset-modal-content input:focus {
            outline: 2px solid rgba(37, 99, 235, .2);
            border-color: var(--accent-blue);
        }

        .reset-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .65rem;
            margin-top: 1rem;
        }

        /* --- Main Layout --- */
        .admin-content {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 2rem;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-card);
            padding: 1.5rem 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .content-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary);
        }

        .content-header-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* --- Profile Structural Grid --- */
        .profile-view-container {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 2rem;
            align-items: flex-start;
        }

        .profile-view-sidebar {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 2.5rem 1.75rem;
            text-align: center;
            border: 1px solid var(--border-color);
            position: sticky;
            top: 2rem;
        }

        .avatar-preview {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: var(--shadow-lg);
            margin-bottom: 1.25rem;
            transition: var(--transition);
        }

        .profile-view-sidebar:hover .avatar-preview {
            transform: scale(1.03);
        }

        .profile-name-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .profile-position {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--accent-blue);
            font-weight: 700;
            background-color: #eff6ff;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .profile-key-info {
            text-align: left;
            font-size: 0.9rem;
            margin-top: 1.5rem;
        }

        .profile-key-info div {
            margin-bottom: 1rem;
            background: var(--bg-input);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .profile-key-info label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .profile-key-info span {
            color: var(--text-main);
            font-weight: 600;
            word-break: break-all;
        }

        .profile-view-details {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .detail-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .detail-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .detail-card h2 {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1.25rem 1.75rem;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .detail-card h2 i {
            color: var(--accent-blue);
        }

        /* --- Read-only Data Grid --- */
        .detail-grid {
            padding: 1.75rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        .detail-item {
            background: var(--bg-input);
            padding: 0.85rem 1.1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            text-transform: uppercase;
        }

        .detail-item:hover {
            background: #ffffff;
            border-color: var(--accent-blue);
        }

        .detail-item label {
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: block;
        }

        .detail-item span {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        /* --- Document Management Table --- */
        .table-responsive {
            overflow-x: auto;
            padding: 0.5rem;
        }

        .employee-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .employee-table th {
            background: var(--bg-input);
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .employee-table td {
            padding: 1rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .employee-table tbody tr:last-child td {
            border-bottom: none;
        }

        .employee-table tbody tr:hover td {
            background: var(--bg-input);
        }

        /* --- Status Badges --- */
        .status-badge {
            padding: 0.3rem 0.75rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-uploaded {
            background: #d1fae5;
            color: #065f46;
        }

        .status-missing {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            padding: 0.4rem 0.85rem;
            font-size: 0.8rem;
            border-radius: var(--radius-sm);
        }

        /* --- Print Architecture --- */
        .print-header {
            display: none;
        }

        /* --- Modal Styling for Multi-File Archive --- */
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
        }

        .custom-modal-content {
            background-color: var(--bg-card);
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .custom-modal-close {
            position: absolute;
            top: 1.25rem;
            right: 1.5rem;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
            background: none;
            border: none;
        }

        .custom-modal-close:hover {
            color: var(--danger);
        }

        .archive-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.25rem;
            max-height: 350px;
            overflow-y: auto;
        }

        .archive-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-input);
            padding: 0.85rem 1.1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        /* --- Responsive Viewports --- */
        @media (max-width: 1024px) {
            .profile-view-container {
                grid-template-columns: 1fr;
            }

            .profile-view-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .content-header-actions {
                width: 100%;
                flex-direction: column;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .admin-content {
                padding: 1rem;
            }
        }

        /* --- Print Enhancements --- */
        @media print {

            .main-header,
            .admin-sidebar,
            .content-header,
            .profile-view-sidebar,
            .action-buttons,
            .custom-modal,
            th:last-child,
            td:last-child {
                display: none !important;
            }

            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 2rem;
                border-bottom: 2px solid #000;
                padding-bottom: 1rem;
            }

            body {
                background-color: #fff !important;
                font-family: 'Times New Roman', serif;
            }

            .profile-view-container {
                display: block !important;
            }

            .detail-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
                padding: 1rem !important;
            }

            .detail-card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                page-break-inside: avoid;
                margin-bottom: 1.5rem;
            }

            .detail-card h2 {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeradmin.php'; ?>

    <div class="admin-container">
        <main class="admin-content">

            <div class="content-header">
                <h1 style="text-transform: uppercase;">
                    <?php echo htmlspecialchars($details['name']); ?>
                </h1>
                <div class="content-header-actions">
                    <a href="admin_download_pdf.php?id=<?php echo urlencode($EmployeeID); ?>" class="btn btn-success">
                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                    </a>

                    <?php if ($isSuperadmin && $details['role'] === 'staff'): ?>
                        <button type="button" class="btn btn-reset" onclick="openResetModal()">
                            <i class="fa-solid fa-key"></i> Reset Credentials
                        </button>
                    <?php endif; ?>

                    <a href="index.php" class="btn btn-secondary">&laquo; Back to Dashboard</a>
                </div>
            </div>

            <div class="profile-view-container">

                <aside class="profile-view-sidebar">
                    <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview">
                    <div class="profile-name-title"><!-- <?php echo htmlspecialchars($details['name']); ?> --></div>

                    <p class="profile-position">
                        <?php echo htmlspecialchars($details['position']); ?>
                    </p>

                    <hr style="border:0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

                    <div class="profile-key-info">
                        <div>
                            <label>Employee Number</label>
                            <span><?php echo htmlspecialchars($details['Employeenumber']); ?></span>
                        </div>
                        <div>
                            <label>Username</label>
                            <span><?php echo htmlspecialchars($details['username']); ?></span>
                        </div>
                        <div>
                            <label>Email Address</label>
                            <span><?php echo htmlspecialchars($details['email_address']); ?></span>
                        </div>
                        <div>
                            <label>Contact Number</label>
                            <span><?php echo htmlspecialchars($details['contact_number']); ?></span>
                        </div>
                    </div>
                </aside>

                <?php if ($credentialResetMessage !== ''): ?>
                    <div
                        class="reset-alert <?php echo $credentialResetStatus === 'success' ? 'reset-alert-success' : 'reset-alert-error'; ?>">
                        <i
                            class="fa-solid <?php echo $credentialResetStatus === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                        <span><?php echo htmlspecialchars($credentialResetMessage); ?></span>
                    </div>
                <?php endif; ?>

                <section class="profile-view-details">

                    <div class="detail-card">
                        <h2><i class="fa-solid fa-user"></i> Personal Information</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Full Name</label>
                                <span><?php echo htmlspecialchars($details['name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Birthdate</label>
                                <span><?php echo htmlspecialchars($details['birthdate']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Gender</label>
                                <span><?php echo htmlspecialchars($details['gender']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Marital Status</label>
                                <span><?php echo htmlspecialchars($details['marital_status']); ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Residential Address</label>
                                <span><?php echo htmlspecialchars($details['residential_address']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h2><i class="fa-solid fa-briefcase"></i> Employment Details</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Position</label>
                                <span><?php echo htmlspecialchars($details['position']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Subject Handled</label>
                                <span><?php echo htmlspecialchars($details['subject_handled']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Educational Attainment</label>
                                <span><?php echo htmlspecialchars($details['educational_attainment']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Eligibility</label>
                                <span><?php echo htmlspecialchars($details['eligibility']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Basic Salary</label>
                                <span>
                                    <?php
                                    echo !empty($details['basic_salary'])
                                        ? '₱' . number_format((float) $details['basic_salary'], 2)
                                        : '';
                                    ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Salary Grade</label>
                                <span><?php echo htmlspecialchars($details['salary_grade']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Step</label>
                                <span><?php echo htmlspecialchars($details['step']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Plantilla Number</label>
                                <span><?php echo htmlspecialchars($details['plantilla_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Original Appointment Date</label>
                                <span><?php echo htmlspecialchars($details['original_appointment_date']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Latest Appointment Date</label>
                                <span><?php echo htmlspecialchars($details['latest_appointment_date']); ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Previous Employer</label>
                                <span><?php echo htmlspecialchars($details['previous_employer']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h2><i class="fa-solid fa-id-card"></i> IDs & Licenses</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>GSIS Number</label>
                                <span><?php echo htmlspecialchars($details['gsis_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>PhilHealth Number</label>
                                <span><?php echo htmlspecialchars($details['philhealth_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Pag-IBIG Number</label>
                                <span><?php echo htmlspecialchars($details['pagibig_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>TIN Number</label>
                                <span><?php echo htmlspecialchars($details['tin_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>PRC License</label>
                                <span><?php echo htmlspecialchars($details['prc_license']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>PRC Expiration Date</label>
                                <span><?php echo htmlspecialchars($details['expiration_date']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h2><i class="fa-solid fa-folder-open"></i> Uploaded Documents</h2>
                        <div class="table-responsive">
                            <table class="employee-table">
                                <thead>
                                    <tr>
                                        <th>Document Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($documents || !empty($multiFiles)) {
                                        foreach ($doc_map as $col_name => $display_name) {
                                            $files = $multiFiles[$col_name] ?? [];
                                            $hasLegacy = isset($documents[$col_name]) && $documents[$col_name] > 0;
                                            $totalCount = count($files) + ($hasLegacy && count($files) === 0 ? 1 : 0);

                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($display_name) . "</td>";

                                            if ($totalCount > 0) {
                                                $statusText = $totalCount === 1 ? "Uploaded" : "Uploaded (" . $totalCount . " Files)";
                                                echo '<td><span class="status-badge status-uploaded"><i class="fa-solid fa-circle-check"></i> ' . $statusText . '</span></td>';
                                                echo '<td><div class="action-buttons">';

                                                // Case 1: Only legacy file exists
                                                if ($hasLegacy && count($files) === 0) {
                                                    $view_link = "viewemployeedocument.php?id=" . $EmployeeID . "&doc=" . $col_name;
                                                    $download_link = "downloademployeedocument.php?id=" . $EmployeeID . "&doc=" . $col_name;
                                                    echo '<a href="' . $view_link . '" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>';
                                                    echo '<a href="' . $download_link . '" class="btn btn-success"><i class="fa-solid fa-download"></i> Download</a>';
                                                }
                                                // Case 2: Exactly 1 new file exists
                                                else if (count($files) === 1 && !$hasLegacy) {
                                                    $f = $files[0];
                                                    echo '<a href="downloademployeedocument.php?file_id=' . $f['file_id'] . '&action=view" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>';
                                                    echo '<a href="downloademployeedocument.php?file_id=' . $f['file_id'] . '&action=download" class="btn btn-success"><i class="fa-solid fa-download"></i> Download</a>';
                                                }
                                                // Case 3: Multiple files exist (opens clean modal)
                                                else {
                                                    echo '<button type="button" class="btn btn-view btn-view-archive" data-col="' . htmlspecialchars($col_name, ENT_QUOTES) . '" data-label="' . htmlspecialchars($display_name, ENT_QUOTES) . '"><i class="fa-solid fa-folder-open"></i> View Files (' . $totalCount . ')</button>';
                                                }
                                                echo '</div></td>';
                                            } else {
                                                echo '<td><span class="status-badge status-missing"><i class="fa-solid fa-circle-xmark"></i> Missing</span></td>';
                                                echo '<td><span style="color: var(--text-muted); font-size: 0.8rem;">N/A</span></td>';
                                            }
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo '<tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No document record found for this employee.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section>

            </div>
        </main>
    </div>

    <!-- Multi-File Archive Modal -->
    <div id="archiveModal" class="custom-modal">
        <div class="custom-modal-content">
            <button type="button" class="custom-modal-close" onclick="closeArchiveModal()">&times;</button>
            <h2 id="modalTitle" style="color: var(--primary); font-size: 1.2rem; margin-bottom: 0.25rem;">Uploaded Files
                Archive</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">View or download files submitted under this
                requirement category.</p>
            <div id="archiveListContainer" class="archive-list"></div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="button" class="btn btn-secondary" onclick="closeArchiveModal()">Close</button>
            </div>
        </div>
    </div>

    <?php if ($isSuperadmin && $details['role'] === 'staff'): ?>
        <div id="resetCredentialsModal" class="custom-modal" role="dialog" aria-modal="true"
            aria-labelledby="resetCredentialsTitle">
            <div class="custom-modal-content reset-modal-content">
                <button type="button" class="custom-modal-close" onclick="closeResetModal()"
                    aria-label="Close reset credentials dialog">&times;</button>
                <h2 id="resetCredentialsTitle" style="color: var(--primary); font-size: 1.2rem; margin-bottom: 0.25rem;">
                    <i class="fa-solid fa-key" style="color: var(--accent-blue);"></i> Reset Employee Credentials
                </h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">
                    Set a fresh username and password for this employee account.
                </p>
                <form action="../controllers/reset_employee_credentials.php" method="POST">
                    <input type="hidden" name="EmployeeID" value="<?php echo (int) $EmployeeID; ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="reset_username">New Username</label>
                    <input id="reset_username" name="username" type="text" required minlength="3" maxlength="50"
                        pattern="[A-Za-z0-9._-]{3,50}"
                        value="<?php echo htmlspecialchars($details['username'], ENT_QUOTES, 'UTF-8'); ?>"
                        autocomplete="off">
                    <label for="reset_password">New Password</label>
                    <div class="reset-password-field">
                        <input id="reset_password" name="new_password" type="password" required minlength="6"
                            autocomplete="new-password">
                        <button type="button" class="reset-password-toggle" data-password-target="reset_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                    <label for="reset_confirm_password">Confirm New Password</label>
                    <div class="reset-password-field">
                        <input id="reset_confirm_password" name="confirm_password" type="password" required minlength="6"
                            autocomplete="new-password">
                        <button type="button" class="reset-password-toggle" data-password-target="reset_confirm_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                    <div class="reset-form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Cancel</button>
                        <button type="submit" class="btn btn-reset"><i class="fa-solid fa-key"></i> Save
                            Credentials</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="print-header">
        <h1>Employee Information Sheet</h1>
        <p><?php echo htmlspecialchars($details['name']); ?></p>
        <span><?php echo htmlspecialchars($details['position']); ?></span>
    </div>

    <script>
        const uploadedFiles = <?php echo json_encode(empty($multiFiles) ? new stdClass() : $multiFiles); ?>;
        const legacyFiles = <?php echo json_encode(empty($documents) ? new stdClass() : $documents); ?>;
        const employeeId = <?php echo (int) $EmployeeID; ?>;
        const modal = document.getElementById('archiveModal');

        function openArchiveModal(column, label) {
            modal.style.display = 'block';
            document.getElementById('modalTitle').textContent = label + ' - Uploaded Files';
            const container = document.getElementById('archiveListContainer');
            container.innerHTML = '';

            const files = uploadedFiles[column] || [];
            const hasLegacy = legacyFiles[column] && legacyFiles[column] > 0;

            if (files.length === 0 && !hasLegacy) {
                container.innerHTML = '<p style="text-align:center; color:var(--text-muted); padding:1rem;">No files found.</p>';
                return;
            }

            files.forEach(f => {
                const dateStr = f.uploaded_at ? new Date(f.uploaded_at).toLocaleDateString() : 'Recent upload';
                const div = document.createElement('div');
                div.className = 'archive-item';
                div.innerHTML = `
                    <div style="min-width:0; margin-right: 1rem;">
                        <div style="font-weight:600; font-size:0.9rem; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${f.file_name}"><i class="fa-solid fa-file-lines" style="color:var(--accent-blue); margin-right:6px;"></i> ${f.file_name}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Uploaded on ${dateStr}</div>
                    </div>
                    <div class="action-buttons" style="flex-shrink:0;">
                        <a href="downloademployeedocument.php?file_id=${f.file_id}&action=view" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>
                        <a href="downloademployeedocument.php?file_id=${f.file_id}&action=download" class="btn btn-success"><i class="fa-solid fa-download"></i> Download</a>
                    </div>
                `;
                container.appendChild(div);
            });

            if (hasLegacy && files.length === 0) {
                const div = document.createElement('div');
                div.className = 'archive-item';
                div.innerHTML = `
                    <div style="min-width:0; margin-right: 1rem;">
                        <div style="font-weight:600; font-size:0.9rem; color:var(--text-main);"><i class="fa-solid fa-file-circle-check" style="color:var(--success); margin-right:6px;"></i> Archived Document</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Legacy system upload</div>
                    </div>
                    <div class="action-buttons" style="flex-shrink:0;">
                        <a href="viewemployeedocument.php?id=${employeeId}&doc=${column}" target="_blank" class="btn btn-view"><i class="fa-solid fa-eye"></i> View</a>
                        <a href="downloademployeedocument.php.php?id=${employeeId}&doc=${column}" class="btn btn-success"><i class="fa-solid fa-download"></i> Download</a>
                    </div>
                `;
                container.appendChild(div);
            }
        }

        function closeArchiveModal() {
            modal.style.display = 'none';
        }

        const resetCredentialsModal = document.getElementById('resetCredentialsModal');

        function openResetModal() {
            if (!resetCredentialsModal) return;
            resetCredentialsModal.style.display = 'block';
        }

        function closeResetModal() {
            if (resetCredentialsModal) resetCredentialsModal.style.display = 'none';
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-view-archive');
            if (btn) {
                openArchiveModal(btn.getAttribute('data-col'), btn.getAttribute('data-label'));
                return;
            }
            if (e.target === modal) closeArchiveModal();
            if (e.target === resetCredentialsModal) closeResetModal();
        });

        document.querySelectorAll('.reset-password-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.passwordTarget);
                if (!input) return;

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                this.querySelector('i').className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                this.setAttribute('aria-pressed', String(isHidden));
                this.title = isHidden ? 'Hide password' : 'Show password';
            });
        });
    </script>
</body>

</html>