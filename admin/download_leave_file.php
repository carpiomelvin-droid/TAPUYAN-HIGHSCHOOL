<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    http_response_code(403);
    exit('Access denied.');
}

$leaveId = (int) ($_GET['id'] ?? 0);
if ($leaveId <= 0) {
    http_response_code(400);
    exit('Invalid leave file.');
}

$stmt = $conn->prepare('SELECT file_name, file_data, mime_type FROM leave_records WHERE leave_id = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    exit('Unable to retrieve leave file.');
}

$stmt->bind_param('i', $leaveId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file || $file['file_data'] === null || $file['file_data'] === '') {
    http_response_code(404);
    exit('File not found.');
}

$fileName = basename(str_replace(["\r", "\n", '"'], '', (string) $file['file_name']));
$mimeType = trim((string) ($file['mime_type'] ?? '')) ?: 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($file['file_data']));
header('X-Content-Type-Options: nosniff');
echo $file['file_data'];
exit();
