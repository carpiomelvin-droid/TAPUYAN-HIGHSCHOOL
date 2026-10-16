<?php
session_start();
require_once '../db/conn.php';

const ANNOUNCEMENT_MAX_IMAGES = 10;
const ANNOUNCEMENT_MAX_IMAGE_BYTES = 5 * 1024 * 1024;
const ANNOUNCEMENT_ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!isset($_SESSION['EmployeeID']) || ($_SESSION['role'] ?? 'staff') === 'staff') {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Invalid request.'));
    exit();
}

function announcement_redirect(string $status, string $message): void
{
    header('Location: ../admin/index.php?view=announcements&status=' . $status . '&message=' . urlencode($message));
    exit();
}

function announcement_ensure_schema(mysqli $conn): void
{
    $createTableSql = "CREATE TABLE IF NOT EXISTS announcements (
        announcement_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL DEFAULT 'General',
        body TEXT NOT NULL,
        external_url VARCHAR(2048) NULL,
        file_name VARCHAR(255) NULL,
        file_data LONGBLOB NULL,
        mime_type VARCHAR(150) NULL,
        image_name VARCHAR(255) NULL,
        image_data LONGBLOB NULL,
        image_mime_type VARCHAR(150) NULL,
        is_published TINYINT(1) NOT NULL DEFAULT 1,
        created_by INT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (announcement_id),
        KEY idx_announcements_published (is_published, created_at),
        CONSTRAINT fk_announcements_admin FOREIGN KEY (created_by) REFERENCES admin(EmployeeID) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($createTableSql)) {
        announcement_redirect('error', 'Unable to prepare announcements storage.');
    }

    // Keep existing installations compatible with the optional image feature.
    $announcementImageColumns = [
        'image_name' => 'VARCHAR(255) NULL AFTER mime_type',
        'image_data' => 'LONGBLOB NULL AFTER image_name',
        'image_mime_type' => 'VARCHAR(150) NULL AFTER image_data'
    ];
    foreach ($announcementImageColumns as $columnName => $definition) {
        $columnCheck = $conn->query("SHOW COLUMNS FROM announcements LIKE '" . $conn->real_escape_string($columnName) . "'");
        if ($columnCheck && $columnCheck->num_rows === 0) {
            $conn->query("ALTER TABLE announcements ADD COLUMN {$columnName} {$definition}");
        }
    }

    $createGallerySql = "CREATE TABLE IF NOT EXISTS announcement_images (
        image_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        announcement_id INT UNSIGNED NOT NULL,
        image_name VARCHAR(255) NULL,
        image_data LONGBLOB NULL,
        image_mime_type VARCHAR(150) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (image_id),
        KEY idx_announcement_images_announcement (announcement_id, sort_order, image_id),
        CONSTRAINT fk_announcement_images_announcement FOREIGN KEY (announcement_id)
            REFERENCES announcements (announcement_id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($createGallerySql)) {
        announcement_redirect('error', 'Unable to prepare announcement gallery storage.');
    }

    // One-time migration: copy legacy single images into the gallery so old
    // announcements show up in the multi-image UI without data loss.
    $conn->query("INSERT INTO announcement_images (announcement_id, image_name, image_data, image_mime_type, sort_order)
        SELECT a.announcement_id, a.image_name, a.image_data, a.image_mime_type, 0
        FROM announcements a
        WHERE a.image_data IS NOT NULL
          AND NOT EXISTS (
              SELECT 1 FROM announcement_images gi WHERE gi.announcement_id = a.announcement_id
          )");
}

/**
 * Normalize a (possibly multiple) file upload entry into a flat list.
 *
 * @return array<int, array{name:string, tmp_name:string, error:int, size:int}>
 */
function announcement_normalize_uploads(?array $fileEntry): array
{
    if ($fileEntry === null || !isset($fileEntry['error'])) {
        return [];
    }
    $files = [];
    if (is_array($fileEntry['error'])) {
        $count = count($fileEntry['error']);
        for ($i = 0; $i < $count; $i++) {
            $error = (int) $fileEntry['error'][$i];
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => (string) ($fileEntry['name'][$i] ?? ''),
                'tmp_name' => (string) ($fileEntry['tmp_name'][$i] ?? ''),
                'error' => $error,
                'size' => (int) ($fileEntry['size'][$i] ?? 0),
            ];
        }
        return $files;
    }
    if ((int) $fileEntry['error'] === UPLOAD_ERR_NO_FILE) {
        return [];
    }
    return [[
        'name' => (string) ($fileEntry['name'] ?? ''),
        'tmp_name' => (string) ($fileEntry['tmp_name'] ?? ''),
        'error' => (int) $fileEntry['error'],
        'size' => (int) ($fileEntry['size'] ?? 0),
    ]];
}

/**
 * Validate + read uploaded images. Returns [images, errorMessage].
 * Each image: ['name'=>..., 'data'=>..., 'mime'=>...]
 */
function announcement_collect_images(): array
{
    $uploads = announcement_normalize_uploads($_FILES['announcement_images'] ?? null);
    // Backward compatibility: old forms posted a single `announcement_image` field.
    foreach (announcement_normalize_uploads($_FILES['announcement_image'] ?? null) as $legacy) {
        $uploads[] = $legacy;
    }

    if (count($uploads) > ANNOUNCEMENT_MAX_IMAGES) {
        return [[], 'You can upload up to ' . ANNOUNCEMENT_MAX_IMAGES . ' images per announcement.'];
    }

    $images = [];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($uploads as $upload) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            return [[], 'One of the images failed to upload. Please try again.'];
        }
        if ($upload['size'] <= 0 || $upload['size'] > ANNOUNCEMENT_MAX_IMAGE_BYTES) {
            return [[], 'Each announcement image must be smaller than 5 MB.'];
        }
        if (!is_uploaded_file($upload['tmp_name'])) {
            return [[], 'One of the images failed to upload. Please try again.'];
        }
        $mime = $finfo->file($upload['tmp_name']);
        if (!in_array($mime, ANNOUNCEMENT_ALLOWED_IMAGE_MIMES, true)) {
            return [[], 'Announcement images must be JPG, PNG, GIF, or WEBP files.'];
        }
        $data = file_get_contents($upload['tmp_name']);
        if ($data === false || $data === '') {
            return [[], 'One of the images could not be read. Please try again.'];
        }
        $images[] = [
            'name' => basename($upload['name']),
            'data' => $data,
            'mime' => $mime,
        ];
    }
    return [$images, null];
}

function announcement_gallery_count(mysqli $conn, int $announcementId): int
{
    $stmt = $conn->prepare('SELECT COUNT(*) AS c FROM announcement_images WHERE announcement_id = ?');
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $announcementId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['c'] ?? 0);
}

function announcement_gallery_next_order(mysqli $conn, int $announcementId): int
{
    $stmt = $conn->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 AS next_order FROM announcement_images WHERE announcement_id = ?');
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $announcementId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['next_order'] ?? 0);
}

function announcement_insert_images(mysqli $conn, int $announcementId, array $images, int $startOrder): bool
{
    if (empty($images)) {
        return true;
    }
    // Straightforward loop with send_long_data for LONGBLOB payloads.
    foreach ($images as $index => $image) {
        $insert = $conn->prepare('INSERT INTO announcement_images (announcement_id, image_name, image_data, image_mime_type, sort_order) VALUES (?, ?, ?, ?, ?)');
        if (!$insert) {
            return false;
        }
        $null = null;
        $name = $image['name'];
        $mime = $image['mime'];
        $order = $startOrder + $index;
        $insert->bind_param('isssi', $announcementId, $name, $null, $mime, $order);
        $insert->send_long_data(2, $image['data']);
        if (!$insert->execute()) {
            $insert->close();
            return false;
        }
        $insert->close();
    }
    return true;
}

announcement_ensure_schema($conn);

$action = $_POST['action'] ?? 'create';
$id = (int) ($_POST['announcement_id'] ?? 0);
$adminId = (int) $_SESSION['EmployeeID'];

// Do not trust the session role alone. The creator must be an actual
// administrative account in the database, not an employee/staff account.
$creatorStmt = $conn->prepare(
    "SELECT EmployeeID FROM admin
     WHERE EmployeeID = ? AND role = 'superadmin' LIMIT 1"
);
$creatorIsValid = false;
if ($creatorStmt) {
    $creatorStmt->bind_param('i', $adminId);
    $creatorStmt->execute();
    $creatorResult = $creatorStmt->get_result();
    $creatorIsValid = $creatorResult && $creatorResult->num_rows === 1;
    $creatorStmt->close();
}
if (!$creatorIsValid) {
    announcement_redirect('error', 'Only superadmins can manage announcements.');
}

if ($action === 'toggle_publish') {
    if ($id <= 0) {
        announcement_redirect('error', 'Invalid announcement.');
    }
    $stmt = $conn->prepare('UPDATE announcements SET is_published = NOT is_published WHERE announcement_id = ?');
    if (!$stmt) {
        announcement_redirect('error', 'Unable to update announcement status.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $message = $stmt->affected_rows ? 'Announcement status updated.' : 'Announcement not found.';
    $status = $stmt->affected_rows ? 'success' : 'error';
    $stmt->close();
    announcement_redirect($status, $message);
}

if ($action === 'delete') {
    if ($id <= 0) {
        announcement_redirect('error', 'Invalid announcement.');
    }
    // Gallery rows are removed automatically via ON DELETE CASCADE.
    $stmt = $conn->prepare('DELETE FROM announcements WHERE announcement_id = ?');
    if (!$stmt) {
        announcement_redirect('error', 'Unable to delete announcement.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $message = $stmt->affected_rows ? 'Announcement deleted.' : 'Announcement not found.';
    $status = $stmt->affected_rows ? 'success' : 'error';
    $stmt->close();
    announcement_redirect($status, $message);
}

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '') ?: 'General';
$body = trim($_POST['body'] ?? '');
$externalUrl = trim($_POST['external_url'] ?? '');
$isPublished = 1;

if ($title === '' || $body === '' || mb_strlen($title) > 255 || mb_strlen($category) > 100) {
    announcement_redirect('error', 'Title, category, and announcement details are required.');
}

if ($externalUrl !== '') {
    if (mb_strlen($externalUrl) > 2048 || !filter_var($externalUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($externalUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        announcement_redirect('error', 'Please provide a valid HTTP or HTTPS URL.');
    }
}

[$newImages, $imageError] = announcement_collect_images();
if ($imageError !== null) {
    announcement_redirect('error', $imageError);
}

$fileName = null;
$fileData = null;
$mimeType = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $upload = $_FILES['attachment'];
    $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'text/plain',
        'text/csv',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 10 * 1024 * 1024) {
        announcement_redirect('error', 'The attachment must be smaller than 10 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($upload['tmp_name']);
    if (!in_array($mimeType, $allowedMimes, true)) {
        announcement_redirect('error', 'This file type is not allowed.');
    }
    $fileName = basename($upload['name']);
    $fileData = file_get_contents($upload['tmp_name']);
}

if ($action === 'update' && $id > 0) {
    // Handle gallery deletions first so the 10-image limit counts correctly.
    $deleteIds = [];
    foreach ((array) ($_POST['delete_image_ids'] ?? []) as $rawDeleteId) {
        $deleteId = (int) $rawDeleteId;
        if ($deleteId > 0) {
            $deleteIds[$deleteId] = true;
        }
    }
    // Legacy checkbox from older UI revisions.
    $removeLegacyImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

    if (!empty($deleteIds)) {
        $deleteStmt = $conn->prepare('DELETE FROM announcement_images WHERE announcement_id = ? AND image_id = ?');
        if ($deleteStmt) {
            foreach (array_keys($deleteIds) as $deleteId) {
                $deleteStmt->bind_param('ii', $id, $deleteId);
                $deleteStmt->execute();
            }
            $deleteStmt->close();
        }
    }
    if ($removeLegacyImage) {
        $stmt = $conn->prepare('UPDATE announcements SET image_name = NULL, image_data = NULL, image_mime_type = NULL WHERE announcement_id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        // Also drop the migrated copy of the legacy image, if present.
        $stmt = $conn->prepare('DELETE FROM announcement_images WHERE announcement_id = ? AND sort_order = 0');
        if ($stmt) {
            // Only deletes the migrated legacy row when it is the sole image; keeps
            // explicit multi-image galleries intact.
            $count = announcement_gallery_count($conn, $id);
            if ($count <= 1) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    $existingCount = announcement_gallery_count($conn, $id);
    if ($existingCount + count($newImages) > ANNOUNCEMENT_MAX_IMAGES) {
        announcement_redirect('error', 'An announcement can have up to ' . ANNOUNCEMENT_MAX_IMAGES . ' images. Remove some images first.');
    }

    if ($fileData !== null) {
        $sql = 'UPDATE announcements SET title=?, category=?, body=?, external_url=?, file_name=?, file_data=?, mime_type=? WHERE announcement_id=?';
        $stmt = $conn->prepare($sql);
        $null = null;
        if ($stmt) {
            $stmt->bind_param('sssssbsi', $title, $category, $body, $externalUrl, $fileName, $null, $mimeType, $id);
            $stmt->send_long_data(5, $fileData);
        }
    } else {
        $sql = 'UPDATE announcements SET title=?, category=?, body=?, external_url=? WHERE announcement_id=?';
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ssssi', $title, $category, $body, $externalUrl, $id);
        }
    }
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        announcement_redirect('error', 'Unable to update announcement.');
    }
    if ($stmt) {
        $stmt->close();
    }

    if (!empty($newImages)) {
        $nextOrder = announcement_gallery_next_order($conn, $id);
        if (!announcement_insert_images($conn, $id, $newImages, $nextOrder)) {
            announcement_redirect('error', 'Announcement updated, but some images could not be saved.');
        }
    }
    announcement_redirect('success', 'Announcement updated successfully.');
}

// Create flow.
if (count($newImages) > ANNOUNCEMENT_MAX_IMAGES) {
    announcement_redirect('error', 'You can upload up to ' . ANNOUNCEMENT_MAX_IMAGES . ' images per announcement.');
}

$sql = 'INSERT INTO announcements (title, category, body, external_url, file_name, file_data, mime_type, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
$stmt = $conn->prepare($sql);
$null = null;
if ($stmt) {
    $stmt->bind_param('sssssbsii', $title, $category, $body, $externalUrl, $fileName, $null, $mimeType, $isPublished, $adminId);
    if ($fileData !== null) {
        $stmt->send_long_data(5, $fileData);
    }
}
if (!$stmt || !$stmt->execute()) {
    if ($stmt) {
        $stmt->close();
    }
    announcement_redirect('error', 'Unable to create announcement.');
}
$newId = (int) $stmt->insert_id;
$stmt->close();

if (!empty($newImages)) {
    if (!announcement_insert_images($conn, $newId, $newImages, 0)) {
        announcement_redirect('error', 'Announcement created, but some images could not be saved.');
    }
}
announcement_redirect('success', 'Announcement created successfully.');
