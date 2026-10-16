<?php
session_start();
include_once '../db/conn.php';

// 1. Security Check: Block unauthorized downloads
if (!isset($_SESSION['EmployeeID'])) {
    die("Unauthorized access. Please log in first.");
}

// 2. Clear any existing output buffers to prevent file corruption
if (ob_get_level()) {
    ob_end_clean();
}

// 3. Set up HTTP Headers for immediate Excel / CSV download
$filename = "Staff_Profiling_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// 4. Open the PHP output stream
$output = fopen('php://output', 'w');

// 🟢 CRITICAL: Add UTF-8 Byte Order Mark (BOM) so MS Excel opens special characters cleanly
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// 5. Exact 24 Column Headers as requested
$headers = [
    'EMPLOYEE NUMBER',
    'NAME',
    'POSITION',
    'BASIC SALARY',
    'ORIGINAL DATE OF APPOINTMENT',
    'LATEST DATE OF APPOINTMENT',
    'SALARY GRADE',
    'STEP',
    'GENDER',
    'SUBJECT HANDLED',
    'MARITAL STATUS',
    'BIRTHDATE',
    'RESIDENTIAL ADDRESS',
    'EMAIL ADDRESS',
    'CONTACT NUMBER',
    'GSIS NUMBER',
    'PHILHEALTH NUMBER',
    'PAG IBIG NUMBER',
    'TIN NUMBER',
    'PRC LICENSE',
    'EXPIRATION DATE',
    'PREVIOUS EMPLOYER',
    'PLANTILLA NUMBER'
];

// Write the Headers to the file
fputcsv($output, $headers);

// 6. Fetch all actual column names from employee_details to prevent SQL errors
$actual_columns = [];
$col_result = $conn->query("SHOW COLUMNS FROM employee_details");
if ($col_result) {
    while ($col_row = $col_result->fetch_assoc()) {
        $actual_columns[] = $col_row['Field'];
    }
}

// 7. Map Template Headers to potential database field names (case-insensitive & flexible)
$concept_mappings = [
    'EMPLOYEE NUMBER' => ['employee_number', 'employee_id', 'emp_no', 'emp_num', 'EmployeeID', 'EmployeeNumber'],
    'NAME' => ['name', 'full_name', 'employee_name', 'Name'],
    'POSITION' => ['position', 'job_title', 'role_name', 'Position'],
    'BASIC SALARY' => ['basic_salary', 'salary', 'basic_pay', 'BasicSalary'],
    'ORIGINAL DATE OF APPOINTMENT' => ['original_date_of_appointment', 'original_appointment', 'original_date', 'OriginalDateOfAppointment'],
    'LATEST DATE OF APPOINTMENT' => ['latest_date_of_appointment', 'latest_appointment', 'latest_date', 'LatestDateOfAppointment'],
    'SALARY GRADE' => ['salary_grade', 'sg', 'grade', 'SalaryGrade'],
    'STEP' => ['step', 'salary_step', 'Step'],
    'GENDER' => ['gender', 'sex', 'Gender'],
    'SUBJECT HANDLED' => ['subject_handled', 'subject', 'subjects', 'SubjectHandled'],
    'MARITAL STATUS' => ['marital_status', 'civil_status', 'status', 'MaritalStatus'],
    'BIRTHDATE' => ['birthdate', 'birth_date', 'dob', 'date_of_birth', 'Birthdate'],
    'RESIDENTIAL ADDRESS' => ['residential_address', 'address', 'home_address', 'ResidentialAddress'],
    'EMAIL ADDRESS' => ['email_address', 'email', 'email_add', 'EmailAddress'],
    'CONTACT NUMBER' => ['contact_number', 'contact_no', 'contact', 'phone_number', 'phone', 'ContactNumber'],
    'GSIS NUMBER' => ['gsis_number', 'gsis_no', 'gsis', 'GsisNumber'],
    'PHILHEALTH NUMBER' => ['philhealth_number', 'philhealth_no', 'philhealth', 'PhilhealthNumber'],
    'PAG IBIG NUMBER' => ['pag_ibig_number', 'pag_ibig_no', 'pagibig', 'pag_ibig', 'PagIbigNumber'],
    'TIN NUMBER' => ['tin_number', 'tin_no', 'tin', 'TinNumber'],
    'PRC LICENSE' => ['prc_license', 'prc_no', 'prc', 'PrcLicense'],
    'EXPIRATION DATE' => ['expiration_date', 'expiry_date', 'prc_expiration', 'ExpirationDate'],
    'PREVIOUS EMPLOYER' => ['previous_employer', 'prev_employer', 'last_employer', 'PreviousEmployer'],
    'PLANTILLA NUMBER' => ['plantilla_number', 'plantilla_no', 'plantilla', 'PlantillaNumber']
];

// Resolve mappings against your actual database columns
$resolved_concept = [];
foreach ($concept_mappings as $concept => $possibilities) {
    $found = false;
    foreach ($possibilities as $possibility) {
        foreach ($actual_columns as $actual_col) {
            if (strtolower($actual_col) === strtolower($possibility)) {
                $resolved_concept[$concept] = $actual_col;
                $found = true;
                break 2;
            }
        }
    }
    if (!$found) {
        $resolved_concept[$concept] = null; // Will output N/A
    }
}

// 8. Query all employee profiling records
$sql = "SELECT * FROM employee_details ORDER BY name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $csv_row = [];

        // Loop through each of the 24 requested columns
        foreach ($headers as $header) {
            $db_col = $resolved_concept[$header] ?? null;

            // 🟢 Check if column exists AND value is not empty/null
            if ($db_col !== null && isset($row[$db_col]) && trim((string) $row[$db_col]) !== '') {
                $csv_row[] = trim((string) $row[$db_col]);
            } else {
                // 🟢 Populate with "N/A" if there is no data
                $csv_row[] = 'N/A';
            }
        }

        fputcsv($output, $csv_row);
    }
} else {
    // If no employees exist at all, generate one sample row filled with N/A
    $empty_row = array_fill(0, count($headers), 'N/A');
    fputcsv($output, $empty_row);
}

fclose($output);
exit();