<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    die("Access denied.");
}

$EmployeeID = (int) $_SESSION['EmployeeID'];

$allowedColumns = [
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

if (!isset($_GET['file']) || !array_key_exists($_GET['file'], $allowedColumns)) {
    die("Invalid file request.");
}

$fileColumn = $_GET['file'];
$baseFileName = $allowedColumns[$fileColumn];

$sql = "SELECT `$fileColumn` AS file_data FROM employee_documents WHERE EmployeeID = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row || empty($row['file_data'])) {
    die("File not found.");
}

$fileData = $row['file_data'];

/* Detect MIME type from blob */
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($fileData);

if (!$mimeType) {
    $mimeType = 'application/octet-stream';
}

/* Map MIME type to extension */
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

$extension = $mimeToExtension[$mimeType] ?? 'bin';
$downloadName = $baseFileName . '.' . $extension;

/* Force file download */
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . strlen($fileData));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $fileData;
exit();
?>