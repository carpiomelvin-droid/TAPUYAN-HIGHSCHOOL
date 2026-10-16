<?php
session_start();
include_once '../db/conn.php';
if (!isset($_SESSION['EmployeeID'])) {
    die('Access denied.');
}
$leaveId = (int) ($_GET['id'] ?? 0);
$employeeId = (int) $_SESSION['EmployeeID'];
$stmt = $conn->prepare('SELECT file_name, file_data, mime_type FROM leave_records WHERE leave_id = ? AND EmployeeID = ? LIMIT 1');
$stmt->bind_param('ii', $leaveId, $employeeId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$file || $file['file_data'] === '') {
    die('File not found or access denied.');
}
$name = str_replace(["\r", "\n", '"'], '', $file['file_name']);
$mime = $file['mime_type'] ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . strlen($file['file_data']));
header('X-Content-Type-Options: nosniff');
echo $file['file_data'];
exit();
