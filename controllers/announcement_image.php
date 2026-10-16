<?php
session_start();
require_once '../db/conn.php';

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Gallery mode: serve a specific image from announcement_images.
$imageId = (int) ($_GET['image_id'] ?? 0);
if ($imageId > 0) {
    $isAdmin = isset($_SESSION['EmployeeID']) && ($_SESSION['role'] ?? 'staff') !== 'staff';
    $sql = 'SELECT gi.image_data, gi.image_mime_type
            FROM announcement_images gi
            INNER JOIN announcements a ON a.announcement_id = gi.announcement_id
            WHERE gi.image_id = ?';
    if (!$isAdmin) {
        $sql .= ' AND a.is_published = 1';
    }
    $sql .= ' LIMIT 1';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(404);
        exit();
    }
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$image || empty($image['image_data']) || !in_array($image['image_mime_type'], $allowedMimes, true)) {
        http_response_code(404);
        exit();
    }
    header('Content-Type: ' . $image['image_mime_type']);
    header('Cache-Control: public, max-age=3600');
    echo $image['image_data'];
    exit();
}

// Legacy mode: first gallery image, falling back to the old single-image columns.
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit();
}

$isAdmin = isset($_SESSION['EmployeeID']) && ($_SESSION['role'] ?? 'staff') !== 'staff';

// Prefer the gallery so updated announcements render correctly.
$gallerySql = 'SELECT gi.image_data, gi.image_mime_type
    FROM announcement_images gi
    INNER JOIN announcements a ON a.announcement_id = gi.announcement_id
    WHERE gi.announcement_id = ?';
if (!$isAdmin) {
    $gallerySql .= ' AND a.is_published = 1';
}
$gallerySql .= ' ORDER BY gi.sort_order ASC, gi.image_id ASC LIMIT 1';
$stmt = $conn->prepare($gallerySql);
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $galleryImage = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if ($galleryImage && !empty($galleryImage['image_data']) && in_array($galleryImage['image_mime_type'], $allowedMimes, true)) {
        header('Content-Type: ' . $galleryImage['image_mime_type']);
        header('Cache-Control: public, max-age=3600');
        echo $galleryImage['image_data'];
        exit();
    }
}

// Fallback for announcements created before the gallery migration.
$legacySql = 'SELECT image_data, image_mime_type FROM announcements WHERE announcement_id = ?';
if (!$isAdmin) {
    $legacySql .= ' AND is_published = 1';
}
$legacySql .= ' LIMIT 1';
$stmt = $conn->prepare($legacySql);
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

if (!in_array($image['image_mime_type'], $allowedMimes, true)) {
    http_response_code(404);
    exit();
}

header('Content-Type: ' . $image['image_mime_type']);
header('Cache-Control: public, max-age=3600');
echo $image['image_data'];
