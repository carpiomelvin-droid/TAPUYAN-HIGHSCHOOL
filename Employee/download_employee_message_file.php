<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    die("Access denied.");
}

$file_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$EmployeeID = (int) $_SESSION['EmployeeID'];

if ($file_id <= 0) {
    die("Invalid file ID.");
}

$sql = "SELECT
            mf.file_name,
            mf.file_data,
            mf.mime_type
        FROM message_files mf
        INNER JOIN messages m ON mf.message_id = m.message_id
        WHERE mf.file_id = ?
          AND m.receiver_id = ?
          AND m.receiver_role = 'employee'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $file_id, $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$file) {
    die("File not found or access denied.");
}

$fileName = trim($file['file_name'] ?? '');
$mimeType = trim($file['mime_type'] ?? '');
$fileData = $file['file_data'] ?? '';

if ($fileData === '') {
    die("Empty file.");
}

if ($mimeType === '') {
    $mimeType = 'application/octet-stream';
}

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

if ($fileName === '') {
    $ext = $mimeToExtension[$mimeType] ?? 'bin';
    $fileName = 'attachment.' . $ext;
} else {
    $hasExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    if ($hasExtension === '') {
        $ext = $mimeToExtension[$mimeType] ?? 'bin';
        $fileName .= '.' . $ext;
    }
}

$safeFileName = str_replace(["\r", "\n", '"'], '', $fileName);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
header('Content-Length: ' . strlen($fileData));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

echo $fileData;
exit();
?>