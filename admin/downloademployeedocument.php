<?php
session_start();
include_once '../db/conn.php';

// Security check: Ensure admin is logged in
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    die("Access Denied.");
}

// --- 1. Get and Validate Parameters ---
$EmployeeID = $_GET['id'] ?? 0;
$doc_col = $_GET['doc'] ?? ''; // e.g., 'prc_file'

// List of allowed document column names to prevent SQL injection
$allowed_cols = [
    'prc_file',
    'dll_file',
    'saln_file',
    'ipcr_file',
    'diploma_file',
    'tor_file',
    'itr_file',
    'itr_file_2',
    'service_record_file',
    'appointment_file',
    'trainings_awards_file',
    'pds_file'
];

if ($EmployeeID == 0 || !in_array($doc_col, $allowed_cols)) {
    die("Error: Invalid document request.");
}

// --- 2. Get Employee Name (for the filename) ---
$sql_name = "SELECT name FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_name = $conn->prepare($sql_name);
$stmt_name->bind_param("i", $EmployeeID);
$stmt_name->execute();
$result_name = $stmt_name->get_result();
$employee = $result_name->fetch_assoc();
$stmt_name->close();

// Sanitize the name for a filename
$employee_name = $employee ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $employee['name']) : 'Employee';


// --- 3. Fetch the File Blob ---
// $doc_col is safe to use here because we validated it against the $allowed_cols array
$sql_doc = "SELECT $doc_col FROM employee_documents WHERE EmployeeID = ? LIMIT 1";
$stmt_doc = $conn->prepare($sql_doc);
$stmt_doc->bind_param("i", $EmployeeID);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
$file_data = $result_doc->fetch_assoc();
$stmt_doc->close();
$conn->close();

if (!$file_data || empty($file_data[$doc_col])) {
    die("File not found or is empty.");
}

$file_blob = $file_data[$doc_col];

// --- 4. Determine File Extension (from blob data) ---
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($file_blob);

$ext = '.bin'; // default extension
if (strpos($mime_type, 'pdf') !== false) {
    $ext = '.pdf';
} else if (strpos($mime_type, 'jpeg') !== false) {
    $ext = '.jpg';
} else if (strpos($mime_type, 'png') !== false) {
    $ext = '.png';
} else if (strpos($mime_type, 'gif') !== false) {
    $ext = '.gif';
}

// --- 5. Generate Filename and Send Headers ---
$doc_name = str_replace('_file', '', $doc_col); // 'prc_file' -> 'prc'
$filename = $employee_name . '_' . $doc_name . $ext; // e.g., "Juan_Dela_Cruz_prc.pdf"

// Send headers to force download
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . strlen($file_blob));

// Output the file data
echo $file_blob;
exit;
