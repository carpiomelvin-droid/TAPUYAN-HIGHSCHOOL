<?php
session_start();
require_once '../db/conn.php';

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
    header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Unable to prepare announcements storage.'));
    exit();
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
    header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Only superadmins can manage announcements.'));
    exit();
}

if ($action === 'toggle_publish') {
    if ($id <= 0) {
        $message = 'Invalid announcement.';
        $status = 'error';
    } else {
        $stmt = $conn->prepare('UPDATE announcements SET is_published = NOT is_published WHERE announcement_id = ?');
        if (!$stmt) {
            $message = 'Unable to update announcement status.';
            $status = 'error';
        } else {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = $stmt->affected_rows ? 'Announcement status updated.' : 'Announcement not found.';
            $status = $stmt->affected_rows ? 'success' : 'error';
            $stmt->close();
        }
    }
    header('Location: ../admin/index.php?view=announcements&status=' . $status . '&message=' . urlencode($message));
    exit();
}

if ($action === 'delete') {
    if ($id <= 0) {
        $message = 'Invalid announcement.';
        $status = 'error';
    } else {
        $stmt = $conn->prepare('DELETE FROM announcements WHERE announcement_id = ?');
        if (!$stmt) {
            $message = 'Unable to delete announcement.';
            $status = 'error';
        } else {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = $stmt->affected_rows ? 'Announcement deleted.' : 'Announcement not found.';
            $status = $stmt->affected_rows ? 'success' : 'error';
            $stmt->close();
        }
    }
    header('Location: ../admin/index.php?view=announcements&status=' . $status . '&message=' . urlencode($message));
    exit();
}

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '') ?: 'General';
$body = trim($_POST['body'] ?? '');
$externalUrl = trim($_POST['external_url'] ?? '');
$isPublished = 1;

if ($title === '' || $body === '' || mb_strlen($title) > 255 || mb_strlen($category) > 100) {
    header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Title, category, and announcement details are required.'));
    exit();
}

if ($externalUrl !== '') {
    if (mb_strlen($externalUrl) > 2048 || !filter_var($externalUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($externalUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Please provide a valid HTTP or HTTPS URL.'));
        exit();
    }
}

$fileName = null;
$fileData = null;
$mimeType = null;
$imageName = null;
$imageData = null;
$imageMimeType = null;
if (isset($_FILES['announcement_image']) && $_FILES['announcement_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $imageUpload = $_FILES['announcement_image'];
    if ($imageUpload['error'] !== UPLOAD_ERR_OK || $imageUpload['size'] > 5 * 1024 * 1024) {
        header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('The announcement image must be smaller than 5 MB.'));
        exit();
    }
    $imageFinfo = new finfo(FILEINFO_MIME_TYPE);
    $imageMimeType = $imageFinfo->file($imageUpload['tmp_name']);
    $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($imageMimeType, $allowedImageMimes, true)) {
        header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('Announcement images must be JPG, PNG, GIF, or WEBP files.'));
        exit();
    }
    $imageName = basename($imageUpload['name']);
    $imageData = file_get_contents($imageUpload['tmp_name']);
}
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
        header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('The attachment must be smaller than 10 MB.'));
        exit();
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($upload['tmp_name']);
    if (!in_array($mimeType, $allowedMimes, true)) {
        header('Location: ../admin/index.php?view=announcements&status=error&message=' . urlencode('This file type is not allowed.'));
        exit();
    }
    $fileName = basename($upload['name']);
    $fileData = file_get_contents($upload['tmp_name']);
}

if ($action === 'update' && $id > 0) {
    if ($imageData !== null && $fileData !== null) {
        $sql = 'UPDATE announcements SET title=?, category=?, body=?, external_url=?, file_name=?, file_data=?, mime_type=?, image_name=?, image_data=?, image_mime_type=? WHERE announcement_id=?';
        $stmt = $conn->prepare($sql);
        $null = null;
        if ($stmt) {
            $stmt->bind_param('sssssbssbsi', $title, $category, $body, $externalUrl, $fileName, $null, $mimeType, $imageName, $null, $imageMimeType, $id);
            $stmt->send_long_data(5, $fileData);
            $stmt->send_long_data(8, $imageData);
        }
    } elseif ($imageData !== null) {
        $sql = 'UPDATE announcements SET title=?, category=?, body=?, external_url=?, image_name=?, image_data=?, image_mime_type=? WHERE announcement_id=?';
        $stmt = $conn->prepare($sql);
        $null = null;
        if ($stmt) {
            $stmt->bind_param('sssssbsi', $title, $category, $body, $externalUrl, $imageName, $null, $imageMimeType, $id);
            $stmt->send_long_data(5, $imageData);
        }
    } elseif ($fileData !== null) {
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
        $message = 'Unable to update announcement.';
        $status = 'error';
    } else {
        $message = 'Announcement updated successfully.';
        $status = 'success';
    }
    if ($stmt)
        $stmt->close();
} else {
    $sql = 'INSERT INTO announcements (title, category, body, external_url, file_name, file_data, mime_type, image_name, image_data, image_mime_type, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $null = null;
    if ($stmt) {
        $stmt->bind_param('sssssbssbsii', $title, $category, $body, $externalUrl, $fileName, $null, $mimeType, $imageName, $null, $imageMimeType, $isPublished, $adminId);
        if ($fileData !== null) {
            $stmt->send_long_data(5, $fileData);
        }
        if ($imageData !== null) {
            $stmt->send_long_data(8, $imageData);
        }
    }
    if (!$stmt || !$stmt->execute()) {
        $message = 'Unable to create announcement.';
        $status = 'error';
    } else {
        $message = 'Announcement created successfully.';
        $status = 'success';
    }
    if ($stmt)
        $stmt->close();
}

header('Location: ../admin/index.php?view=announcements&status=' . $status . '&message=' . urlencode($message));
exit();
