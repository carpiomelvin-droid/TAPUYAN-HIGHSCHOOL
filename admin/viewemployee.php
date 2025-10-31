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

// --- 3. Query 2: Get Document Status from 'employee_documents' ---
$sql_docs = "SELECT 
                LENGTH(prc_file) as prc_file,
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
                LENGTH(pds_file) as pds_file
             FROM employee_documents WHERE EmployeeID = ?";

$stmt_docs = $conn->prepare($sql_docs);
$stmt_docs->bind_param("i", $EmployeeID);
$stmt_docs->execute();
$result_docs = $stmt_docs->get_result();
$documents = $result_docs->fetch_assoc();
$stmt_docs->close();

// Helper array to build the document table
$doc_map = [
    'prc_file' => 'PRC',
    'dll_file' => 'DLL (Daily Lesson Log)',
    'saln_file' => 'SALN',
    'ipcr_file' => 'IPCR',
    'diploma_file' => 'Diploma',
    'tor_file' => 'Transcript of Records (TOR)',
    'itr_file' => 'ITR (1st Copy)',
    'itr_file_2' => 'ITR (2nd Copy)',
    'service_record_file' => 'Service Record',
    'appointment_file' => 'Appointment',
    'trainings_awards_file' => 'Trainings and Awards',
    'pds_file' => 'PDS (Personal Data Sheet)'
];

// --- 4. Create the Avatar Source ---
$avatar_src = "../path/to/default/avatar.png"; // A default placeholder
if (!empty($details['avatar_image']) && !empty($details['avatar_mime_type'])) {
    $avatar_src = "data:" . $details['avatar_mime_type'] . ";base64," . base64_encode($details['avatar_image']);
}

// --- 5. Get Admin's display name for the header ---
$admin_display_name = $_SESSION['username'] ?? 'Admin';
$AdminEmployeeID = $_SESSION['EmployeeID'] ?? 0;
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
    <title>View Employee: <?php echo htmlspecialchars($details['name']); ?></title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --danger-color: #dc3545;
            --danger-hover: #c82333;
            --success-color: #28a745;
            --success-hover: #218838;
            --secondary-color: #6c757d;
            --secondary-hover: #5a6268;
            --info-color: #17a2b8;
            --info-hover: #138496;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --header-height: 60px;
            --sidebar-bg: #2c3e50;
            --sidebar-text: #ecf0f1;
            --sidebar-hover: #34495e;
            --sidebar-active: #007bff;
            --sidebar-width: 240px;
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
            height: 100vh;
            overflow: hidden;
        }

        /* --- Main Header --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            height: var(--header-height);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .header-logo strong {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .header-user span {
            font-weight: 500;
        }

        /* --- Base Button Styles --- */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #fff;
        }

        .btn-view {
            background-color: var(--primary-color);
        }

        .btn-view:hover {
            background-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-hover);
        }

        .btn-info {
            background-color: var(--info-color);
        }

        .btn-info:hover {
            background-color: var(--info-hover);
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-logout:hover {
            background: var(--danger-color);
            color: #fff;
        }

        /* --- Admin Dashboard Layout (Grid) --- */
        .admin-container {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            height: calc(100vh - var(--header-height));
        }

        /* --- Sidebar --- */
        .admin-sidebar {
            background-color: var(--sidebar-bg);
            padding: 1.5rem 1rem;
            height: 100%;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a,
        .sidebar-nav button {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--sidebar-text);
            border-radius: 6px;
            transition: background-color 0.2s;
            background: none;
            border: none;
            text-align: left;
            font-size: 1rem;
            font-family: inherit;
            cursor: pointer;
        }

        .sidebar-nav a:hover,
        .sidebar-nav button:hover {
            background-color: var(--sidebar-hover);
        }

        .sidebar-nav a.active {
            background-color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar-nav form .btn-logout {
            width: 100%;
            margin-top: 1rem;
            text-align: left;
        }

        /* --- Main Content Area --- */
        .admin-content {
            height: 100%;
            overflow-y: auto;
            padding: 2.5rem;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .content-header-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* --- Profile View --- */
        .profile-view-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
            align-items: flex-start;
            text-transform: uppercase;
        }

        .profile-view-sidebar {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            text-align: center;
            position: sticky;
            top: 0;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border: 4px solid var(--border-color);
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            word-wrap: break-word;
        }

        .profile-position {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .profile-key-info {
            text-align: left;
            font-size: 0.95rem;
        }

        .profile-key-info div {
            margin-bottom: 0.75rem;
        }

        .profile-key-info label {
            display: block;
            font-weight: 600;
            color: var(--text-color);
        }

        .profile-key-info span {
            color: var(--text-muted);
        }

        .profile-view-details {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .detail-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .detail-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        /* --- Read-only Data Grid --- */
        .detail-grid {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-item label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .detail-item span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-color);
            word-wrap: break-word;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        /* --- Table Styles --- */
        .employee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-table th,
        .employee-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .employee-table th {
            background-color: var(--light-bg);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .employee-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* --- Status Badges --- */
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-uploaded {
            color: var(--success-color);
            background-color: #e9f5ec;
        }

        .status-missing {
            color: var(--danger-color);
            background-color: #fbebee;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* --- NEW: Print-Only Header (Hidden on screen) --- */
        .print-header {
            display: none;
        }

        /* --- Responsive --- */
        @media (max-width: 1100px) {
            .profile-view-container {
                grid-template-columns: 1fr;
            }

            .profile-view-sidebar {
                position: static;
            }
        }

        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .admin-container {
                grid-template-columns: 1fr;
                height: auto;
            }

            .admin-sidebar {
                height: auto;
                width: 100%;
                display: flex;
                overflow-x: auto;
            }

            .sidebar-nav ul {
                display: flex;
                gap: 0.5rem;
            }

            .admin-content {
                height: auto;
                padding: 1.5rem;
            }
        }

        @media (max-width: 600px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* --- @page rule to control print margins --- */
        @page {
            margin: 20mm;
            /* Sets a 2cm margin */
        }


        /* --- UPDATED PRINT STYLES --- */
        @media print {

            /* Hide all UI elements */
            .main-header,
            .admin-sidebar,
            .content-header,
            .profile-view-sidebar {
                display: none !important;
            }

            /* Hide the "Action" column AND the entire documents card */
            .employee-table th:last-child,
            .employee-table td:last-child,
            .profile-view-details .detail-card:last-of-type {
                display: none !important;
            }

            /* --- NEW: Show and style the print header --- */
            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 2rem;
                border-bottom: 2px solid #ccc;
                padding-bottom: 1rem;
            }

            .print-header h1 {
                font-size: 20pt;
                font-weight: 600;
                color: #000;
            }

            .print-header p {
                font-size: 14pt;
                font-weight: 500;
                margin: 0;
            }

            .print-header span {
                font-size: 12pt;
                color: #555;
            }

            /* Reset body for printing */
            body {
                overflow: visible !important;
                height: auto !important;
                background-color: #fff !important;
                font-family: 'Times New Roman', Times, serif;
                /* Use a serif font for print */
            }

            /* Reset main containers */
            .admin-container {
                display: block !important;
            }

            .admin-content {
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
            }

            /* Make the details section full width */
            .profile-view-container {
                display: block !important;
            }

            .profile-view-details {
                grid-column: 1 / -1;
            }

            /* --- NEW: Force 1-column layout for details --- */
            .detail-grid {
                grid-template-columns: 1fr !important;
                /* Stack all items */
                gap: 1.25rem !important;
                padding: 1.25rem !important;
            }

            .detail-item label {
                font-size: 10pt;
                /* Smaller label */
                color: #333;
                margin-bottom: 2px;
                text-transform: uppercase;
            }

            .detail-item span {
                font-size: 12pt;
                /* Larger, readable value */
                font-weight: 600;
                color: #000;
            }

            /* Style cards for printing */
            .detail-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                page-break-inside: avoid;
                margin-bottom: 1.5rem;
            }

            .detail-card h2 {
                font-size: 14pt;
                font-weight: 700;
                background-color: #eee !important;
                /* Light gray for section breaks */
                color: #000;
                padding: 0.5rem 1rem;
            }

            /* Ensure links are not blue */
            a {
                color: #000 !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="header-logo">
            <strong>Tapuyan NHS</strong> Admin Portal
        </div>
        <div class="header-user">
            <span>Welcome, <?php echo htmlspecialchars($admin_display_name); ?>!</span>
        </div>
    </header>

    <div class="admin-container">

        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li id="create-account"><button type="button" onclick="alert('Please go to the main dashboard to create an account.')">Create Employee</button></li>
                    <li><a href="editadminaccount.php">Edit My Account</a></li>
                    <li>
                        <form action="../controllers/logout.php" method="post">
                            <button type="submit" class="btn btn-logout">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">

            <div class="content-header">
                <h1><?php echo htmlspecialchars($details['name']); ?></h1>

                <div class="content-header-actions">
                    <button type="button" class="btn btn-info" onclick="window.print()">Print Details</button>
                    <a href="index.php" class="btn btn-secondary">&laquo; Back to Dashboard</a>
                </div>
            </div>

            <div class="profile-view-container">

                <aside class="profile-view-sidebar">
                    <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview">
                    <h2 class="profile-name"><?php echo htmlspecialchars($details['name']); ?></h2>
                    <p class="profile-position"><?php echo htmlspecialchars($details['position']); ?></p>

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

                <section class="profile-view-details">

                    <div class="detail-card">
                        <h2>Personal Information</h2>
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
                        <h2>Employment Details</h2>
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
                                <label>Basic Salary</label>
                                <span><?php echo htmlspecialchars($details['basic_salary']); ?></span>
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
                        <h2>IDs & Licenses</h2>
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
                        <h2>Uploaded Documents</h2>
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
                                if ($documents) {
                                    foreach ($doc_map as $col_name => $display_name) {
                                        echo "<tr>";
                                        echo "<td>" . $display_name . "</td>";

                                        if (isset($documents[$col_name]) && $documents[$col_name] > 0) {
                                            $view_link = "viewemployeedocument.php?id=" . $EmployeeID . "&doc=" . $col_name;
                                            $download_link = "downloademployeedocument.php?id=" . $EmployeeID . "&doc=" . $col_name;

                                            echo '<td><span class="status-badge status-uploaded">Uploaded</span></td>';

                                            echo '<td><div class="action-buttons">';
                                            echo '<a href="' . $view_link . '" target="_blank" class="btn btn-view">View</a>';
                                            echo '<a href="' . $download_link . '" class="btn btn-success">Download</a>';
                                            echo '</div></td>';
                                        } else {
                                            echo '<td><span class="status-badge status-missing">Not Uploaded</span></td>';
                                            echo '<td>N/A</td>';
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

                </section>

            </div>
        </main>
    </div>
    <div class="print-header">
        <h1>Employee Information Sheet</h1>
        <p><?php echo htmlspecialchars($details['name']); ?></p>
        <span><?php echo htmlspecialchars($details['position']); ?></span>
    </div>

</body>

</html>