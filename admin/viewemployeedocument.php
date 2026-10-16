<?php
session_start();
include_once '../db/conn.php';

/* Optional: check admin login here */
// if (!isset($_SESSION['username'])) {
//     die("Access denied.");
// }

$EmployeeID = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$doc_column = $_GET['doc'] ?? '';

$allowed_documents = [
    'prc_file' => 'prc',
    'file_leave' => 'file_leave',
    'dll_file' => 'leave_credit',
    'saln_file' => 'saln',
    'ipcr_file' => 'ipcr',
    'diploma_file' => 'diploma',
    'tor_file' => 'tor',
    'itr_file' => 'itr',
    'itr_file_2' => 'itr_2nd_copy',
    'service_record_file' => 'service_record',
    'appointment_file' => 'appointment',
    'trainings_awards_file' => 'trainings_awards',
    'pds_file' => 'pds',
    'ICS' => 'ics'
];

if ($EmployeeID <= 0 || !array_key_exists($doc_column, $allowed_documents)) {
    die("Invalid request or document type.");
}

$baseFileName = $allowed_documents[$doc_column];

/* Safe because column name is strictly whitelisted */
$sql = "SELECT `$doc_column` AS file_data FROM employee_documents WHERE EmployeeID = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $EmployeeID);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || empty($row['file_data'])) {
    $stmt->close();
    $conn->close();
    die("File not found.");
}

$data = $row['file_data'];

/* Detect MIME type from binary data */
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($data);

if (!$mime_type) {
    $mime_type = 'application/octet-stream';
}

/* Map MIME to extension */
$mimeToExtension = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'text/csv' => 'csv',
    'text/plain' => 'txt',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];

$extension = $mimeToExtension[$mime_type] ?? 'bin';
$fileName = $baseFileName . '.' . $extension;

/* Only allow inline preview for browser-friendly files */
$inlineMimeTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
];

$isInline = in_array($mime_type, $inlineMimeTypes, true);

header("Content-Type: " . $mime_type);
header("Content-Length: " . strlen($data));
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if ($isInline) {
    header('Content-Disposition: inline; filename="' . $fileName . '"');
} else {
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
}

echo $data;

$stmt->close();
$conn->close();
exit;