<?php
session_start();
include_once '../db/conn.php';
// include_once '../includes/headeradmin.php'; // Your admin header

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
}

// --- 3. Query 2: Get Document Status from 'employee_documents' ---
// We check the LENGTH of the file. If it's > 0, the file exists.
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
$documents = $result_docs->fetch_assoc(); // This will be one row of file sizes
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee: <?php echo htmlspecialchars($details['name']); ?></title>
    <style>
        /* (You can copy the styles from home.php here) */
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .form-container {
            max-width: 900px;
            margin: auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .field {
            flex: 1 1 300px;
            margin-bottom: 15px;
        }

        .field label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .field input,
        .field textarea,
        .field select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            background-color: #f4f4f4;
            color: #333;
            border: 1px solid #ccc;
            cursor: not-allowed;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border: 3px solid #ddd;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px auto;
            display: block;
        }

        /* Table styles for documents */
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .document-table th,
        .document-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }

        .document-table th {
            background-color: #f4f4f4;
        }

        .status-uploaded {
            color: green;
            font-weight: bold;
        }

        .status-missing {
            color: red;
        }

        .btn-view {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="form-container">

        <a href="index.php">&laquo; Back to Employee List</a>

        <h2>Employee Profile: <?php echo htmlspecialchars($details['name']); ?></h2>

        <form>
            <div class="field" style="text-align: center; flex: 1 1 100%;">
                <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview">
            </div>

            <div class="row">
                <div class="field">
                    <label>Employee Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['Employeenumber']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['username']); ?>" readonly>
                </div>


                <div class="field">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['name']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Position</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['position']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Basic Salary</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['basic_salary']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Original Date of Appointment</label>
                    <input type="date" value="<?php echo htmlspecialchars($details['original_appointment_date']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Latest Appointment Date</label>
                    <input type="date" value="<?php echo htmlspecialchars($details['latest_appointment_date']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Salary Grade</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['salary_grade']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Step</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['step']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Gender</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['gender']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Subject Handled</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['subject_handled']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Marital Status</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['marital_status']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Birthdate</label>
                    <input type="date" value="<?php echo htmlspecialchars($details['birthdate']); ?>" readonly>
                </div>
                <div class="field" style="flex:1 1 100%;">
                    <label>Residential Address</label>
                    <textarea rows="2" readonly><?php echo htmlspecialchars($details['residential_address']); ?></textarea>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($details['email_address']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Contact Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['contact_number']); ?>" readonly>
                </div>
                <div class="field">
                    <label>GSIS Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['gsis_number']); ?>" readonly>
                </div>
                <div class="field">
                    <label>PhilHealth Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['philhealth_number']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Pag-IBIG Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['pagibig_number']); ?>" readonly>
                </div>
                <div class="field">
                    <label>TIN Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['tin_number']); ?>" readonly>
                </div>
                <div class="field">
                    <label>PRC License</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['prc_license']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Expiration Date (PRC)</label>
                    <input type="date" value="<?php echo htmlspecialchars($details['expiration_date']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Previous Employer</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['previous_employer']); ?>" readonly>
                </div>
                <div class="field">
                    <label>Plantilla Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($details['plantilla_number']); ?>" readonly>
                </div>
            </div>
        </form>

        <h2>Employee Documents</h2>
        <table class="document-table">
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
                    // Loop through our helper array
                    foreach ($doc_map as $col_name => $display_name) {
                        echo "<tr>";
                        echo "<td>" . $display_name . "</td>";

                        // Check if the file size is greater than 0
                        if (isset($documents[$col_name]) && $documents[$col_name] > 0) {
                            $link = "viewemployeedocument.php?id=" . $EmployeeID . "&doc=" . $col_name;
                            echo '<td><span class="status-uploaded">Uploaded</span></td>';
                            echo '<td><a href="' . $link . '" target="_blank" class="btn-view">View File</a></td>';
                        } else {
                            echo '<td><span class="status-missing">Not Uploaded</span></td>';
                            echo '<td>N/A</td>';
                        }
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="3">No document record found for this employee.</td></tr>';
                }
                ?>
            </tbody>
        </table>

    </div>

</body>

</html>