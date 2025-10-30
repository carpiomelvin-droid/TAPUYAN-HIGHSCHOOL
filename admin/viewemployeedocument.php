<?php
session_start();
include_once '../db/conn.php';

// Check for admin login, etc.
// ...

// 1. Get the EmployeeID and the document column name from the URL
$EmployeeID = $_GET['id'] ?? 0;
$doc_column = $_GET['doc'] ?? '';

// 2. A *crucial* security whitelist. This prevents SQL injection.
//    Only column names in this list are allowed.
$allowed_documents = [
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

if ($EmployeeID == 0 || !in_array($doc_column, $allowed_documents)) {
    die("Invalid request or document type.");
}

// 3. Prepare the SQL query dynamically (this is safe now)
//    We fetch the one specific BLOB column
$sql = "SELECT $doc_column FROM employee_documents WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $EmployeeID);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || empty($row[$doc_column])) {
    die("File not found.");
}

$data = $row[$doc_column];

// 4. Detect the MIME type from the file's binary data
//    This is the magic that tells the browser if it's a PDF or an image.
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($data);

// 5. Send the correct headers and the file data
header("Content-Type: " . $mime_type);
header("Content-Length: " . strlen($data));
// This tells the browser to display the file, not download it
header("Content-Disposition: inline");

echo $data;

$stmt->close();
$conn->close();
exit;
