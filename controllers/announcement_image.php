<?php
require_once '../db/conn.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit();
}

$stmt = $conn->prepare('SELECT image_data, image_mime_type FROM announcements WHERE announcement_id = ? AND is_published = 1 LIMIT 1');
if (!$stmt) {
    http_response_code(404);
    exit();
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$image || empty($image['image_data']) || empty($image['image_mime_type'])) {
    http_response_code(404);
    exit();
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($image['image_mime_type'], $allowedMimes, true)) {
    http_response_code(404);
    exit();
}

header('Content-Type: ' . $image['image_mime_type']);
header('Cache-Control: public, max-age=3600');
echo $image['image_data'];
