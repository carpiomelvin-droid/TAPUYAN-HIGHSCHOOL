<?php
session_start();
require_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    http_response_code(403);
    exit('Access denied.');
}

$id = (int) ($_GET['id'] ?? 0);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] !== 'staff';
$sql = 'SELECT file_name, file_data, mime_type FROM announcements WHERE announcement_id = ?';
if (!$isAdmin) {
    $sql .= ' AND is_published = 1';
}
$sql .= ' LIMIT 1';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file || $file['file_data'] === null) {
    http_response_code(404);
    exit('File not found.');
}

$name = str_replace(["\r", "\n", '"'], '', basename((string) $file['file_name']));
$mime = $file['mime_type'] ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . ($name ?: 'announcement-attachment') . '"');
header('Content-Length: ' . strlen($file['file_data']));
header('Cache-Control: private, no-store');
echo $file['file_data'];
exit();
