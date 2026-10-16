<?php
session_start();
include_once '../db/conn.php';

$EmployeeID = $_SESSION['EmployeeID'] ?? 0;

$current_username = trim((string) ($_SESSION['username'] ?? ''));
$current_full_name = '';
$current_avatar = null;
$current_avatar_mime = '';
$sql_user = "SELECT a.username, d.name, d.avatar_image, d.avatar_mime_type
    FROM admin a
    LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID
    WHERE a.EmployeeID = ? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
if ($stmt_user) {
    $stmt_user->bind_param("i", $EmployeeID);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($row_user = $result_user->fetch_assoc()) {
        $current_username = trim((string) $row_user['username']);
        $current_full_name = trim((string) ($row_user['name'] ?? ''));
        $current_avatar = $row_user['avatar_image'] ?? null;
        $current_avatar_mime = trim((string) ($row_user['avatar_mime_type'] ?? ''));
        $_SESSION['username'] = $current_username;
    }
    $stmt_user->close();
}

if ($current_username === '') {
    $current_username = 'Admin';
}
$admin_display_name = $current_username;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$announcement_status = $_GET['status'] ?? '';
$announcement_message = $_GET['message'] ?? '';
$announcement_edit_id = (int) ($_GET['edit_announcement'] ?? 0);
$announcement_edit = null;
$announcement_rows = [];

$conn->query("CREATE TABLE IF NOT EXISTS announcements (
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
    PRIMARY KEY (announcement_id), KEY idx_announcements_published (is_published, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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

$announcementResult = $conn->query('SELECT announcement_id, title, category, body, external_url, file_name, image_name, is_published, created_at FROM announcements ORDER BY created_at DESC');
if ($announcementResult) {
    while ($announcementRow = $announcementResult->fetch_assoc()) {
        $announcement_rows[] = $announcementRow;
        if ((int) $announcementRow['announcement_id'] === $announcement_edit_id) {
            $announcement_edit = $announcementRow;
        }
    }
}

$search_query = trim($_GET['search_query'] ?? '');
$requested_view = $_GET['view'] ?? ($search_query !== '' ? 'personnel' : 'announcements');
$activeAdminView = in_array($requested_view, ['announcements', 'personnel'], true)
    ? $requested_view
    : 'announcements';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* =========================================================
   ADMIN ANNOUNCEMENT DASHBOARD STYLES
   ========================================================= */

        .announcement-admin-container {
            min-width: 0;
            background: #ffffff;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 8px;
            padding: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        .announcement-admin-header {
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .announcement-admin-header h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-desc {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        /* Empty State */
        .announcement-empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .announcement-empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
        }

        /* Announcement Cards Grid */
        .admin-announcement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
            gap: 1.25rem;
        }

        /* Card Container */
        .admin-announcement-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.25rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .admin-announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .admin-announcement-card.is-draft {
            border-left: 4px solid #94a3b8;
            background: #fafafa;
        }

        .admin-announcement-card.is-published {
            border-left: 4px solid #16a34a;
        }

        /* Header & Icons inside Card */
        .admin-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 0.75rem;
        }

        .admin-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .admin-card-icon.blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .admin-card-icon.green {
            background: #f0fdf4;
            color: #16a34a;
        }

        .admin-card-icon.purple {
            background: #faf5ff;
            color: #7e22ce;
        }

        .admin-card-icon.orange {
            background: #fff7ed;
            color: #c2410c;
        }

        .admin-card-icon.red {
            background: #fef2f2;
            color: #dc2626;
        }

        .admin-card-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            min-width: 0;
            flex-wrap: wrap;
        }

        /* Category & Status Badges */
        .category-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .blue-tag {
            background: #eff6ff;
            color: #2563eb;
        }

        .green-tag {
            background: #f0fdf4;
            color: #16a34a;
        }

        .purple-tag {
            background: #faf5ff;
            color: #7e22ce;
        }

        .orange-tag {
            background: #fff7ed;
            color: #c2410c;
        }

        .red-tag {
            background: #fef2f2;
            color: #dc2626;
        }

        .status-badge {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .status-published {
            background: #dcfce7;
            color: #15803d;
        }

        .status-draft {
            background: #f1f5f9;
            color: #64748b;
        }

        /* Body Content */
        .admin-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.4rem;
            line-height: 1.35;
        }

        .admin-card-text {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Attachments Links */
        .admin-card-attachments {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            font-size: 0.78rem;
            margin-bottom: 0.75rem;
        }

        .att-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .att-link:hover {
            text-decoration: underline;
        }

        .att-file {
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Footer & Action Buttons */
        .admin-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .card-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .admin-card-actions,
        .admin-card-actions form {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .publish-toggle {
            position: relative;
            width: 44px;
            height: 24px;
            flex: 0 0 44px;
            padding: 0;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #cbd5e1;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease;
        }

        .publish-toggle::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .25);
            transition: transform .2s ease;
        }

        .publish-toggle[aria-pressed="true"] {
            background: #16a34a;
            border-color: #15803d;
        }

        .publish-toggle[aria-pressed="true"]::after {
            transform: translateX(20px);
        }

        .publish-toggle:hover {
            border-color: #64748b;
        }

        .publish-toggle[aria-pressed="true"]:hover {
            background: #15803d;
            border-color: #166534;
        }

        .publish-toggle:focus-visible,
        .btn-icon-action:focus-visible {
            outline: 3px solid rgba(37, 99, 235, .25);
            outline-offset: 2px;
        }

        .btn-icon-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .btn-edit:hover {
            background: #fefce8;
            color: #ca8a04;
            border-color: #fef08a;
        }

        .btn-delete:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        :root {
            --primary-color: #1e293b;
            --primary-hover: #0f172a;
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
            --success-color: #10b981;
            --success-hover: #059669;
            --secondary-color: #3b82f6;
            --secondary-hover: #2563eb;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-color: #334155;
            --text-muted: #64748b;
            --header-height: 70px;
            --sidebar-bg: #ffffff;
            --sidebar-text: #1e293b;
            --sidebar-hover: #f1f5f9;
            --sidebar-active: #e2e8f0;
            --sidebar-width: 240px;
            --border-radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.5;
            height: 100vh;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* --- Buttons --- */
        .btn {
            padding: 0.65rem 1.25rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            box-shadow: none;
        }

        .btn-logout:hover {
            background: var(--danger-color);
            color: #ffffff;
        }

        .btn-view {
            background-color: var(--secondary-color);
        }

        .btn-view:hover {
            background-color: var(--secondary-hover);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
        }

        .btn-success {
            background-color: #2563eb;
        }

        .btn-success:hover {
            background-color: #1d4ed8;
        }

        /* --- Workspace Architecture --- */
        .admin-container {
            min-height: calc(100vh - var(--header-height));
        }

        .admin-content {
            margin-left: 270px;
            height: calc(100vh - var(--header-height));
            overflow: auto;
            padding: 2rem;
            background-color: #f1f5f9;
        }

        .admin-view {
            display: none;
            min-height: 100%;
        }

        .admin-view.active {
            display: flex;
            flex-direction: column;
        }

        .admin-welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
            padding: 1.25rem 1.4rem;
            color: #fff;
            background: #172554;
            border-left: 5px solid #facc15;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
        }

        .admin-welcome-copy {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .admin-welcome-avatar {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            color: #172554;
            background: #facc15;
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .admin-welcome h1 {
            margin: 0 0 .2rem;
            color: #fff;
            font-size: 1.25rem;
            letter-spacing: 0;
        }

        .admin-welcome p {
            color: #cbd5e1;
            font-size: .82rem;
        }

        .admin-welcome-date {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .65rem;
            color: #e2e8f0;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 6px;
            font-size: .75rem;
            white-space: nowrap;
        }

        .announcement-management {
            margin-bottom: 1.25rem;
        }

        .announcement-tabs {
            display: flex;
            gap: 0.2rem;
            padding: 0.2rem;
            margin-bottom: 1rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            width: 300px;
            /* Reduced width */
        }

        .announcement-tab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            min-height: 28px;
            /* Reduced from 34px */
            padding: 0.25rem 0.5rem;
            /* Reduced padding */
            border: 1px solid transparent;
            border-radius: 4px;
            background: transparent;
            color: #64748b;
            font: inherit;
            font-size: 0.72rem;
            /* Reduced font size */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .announcement-tab:hover {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            /* Soft hover tint */
        }

        .announcement-tab[aria-selected="true"] {
            color: #ffffff;
            background: #2563eb;
            /* Vibrant primary accent */
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }

        .announcement-tab:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }

        .announcement-tab-panel {
            display: none;
        }

        .announcement-tab-panel.active {
            display: block;
        }

        /* --- REDESIGNED: Create Announcement Form Panel --- */
        .announcement-form-panel {
            padding: 1.75rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .announcement-form-panel h2 {
            margin-bottom: 1.25rem;
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .announcement-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .announcement-form-grid .full-width {
            grid-column: span 2;
        }

        .announcement-form-grid label {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            color: #334155;
            font-size: 0.825rem;
            font-weight: 600;
        }

        .announcement-form-grid input[type="text"],
        .announcement-form-grid input[type="url"],
        .announcement-form-grid textarea {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            font-size: 0.875rem;
            color: #0f172a;
            background-color: #f8fafc;
            transition: all 0.2s ease-in-out;
        }

        .announcement-form-grid input:focus,
        .announcement-form-grid textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .announcement-form-grid textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* --- REDESIGNED: Choose File Component --- */
        .announcement-file-field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .announcement-file-field>span {
            font-size: 0.825rem;
            font-weight: 600;
            color: #334155;
        }

        .announcement-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .announcement-file-wrapper {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .announcement-file-wrapper:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .announcement-file-picker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.9rem;
            color: #2563eb;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.825rem;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .announcement-file-picker:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .announcement-file-input:focus-visible+.announcement-file-picker {
            outline: 3px solid rgba(37, 99, 235, .25);
            outline-offset: 2px;
        }

        .announcement-file-name {
            overflow: hidden;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .announcement-form-actions {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .announcement-record {
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .announcement-record:first-of-type {
            padding-top: 0;
        }

        .announcement-record:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .announcement-record h3 {
            margin: .2rem 0;
            font-size: 1rem;
            color: var(--primary-color);
        }

        .announcement-record p {
            color: var(--text-muted);
            font-size: .85rem;
            white-space: pre-line;
        }

        .announcement-record-meta {
            display: flex;
            gap: .6rem;
            align-items: center;
            flex-wrap: wrap;
            color: var(--text-muted);
            font-size: .75rem;
        }

        .announcement-status {
            padding: .15rem .45rem;
            border-radius: 4px;
            font-weight: 700;
        }

        .announcement-status.published {
            color: #166534;
            background: #dcfce7;
        }

        .announcement-status.draft {
            color: #92400e;
            background: #fef3c7;
        }

        .announcement-record-actions {
            display: flex;
            gap: .45rem;
            margin-top: .65rem;
        }

        .announcement-record-actions .btn {
            padding: .35rem .65rem;
            font-size: .75rem;
        }

        .announcement-alert {
            position: relative;
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: 1rem;
            padding: .65rem 2.4rem .65rem .85rem;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: .82rem;
            box-shadow: var(--shadow-sm);
            animation: announcement-alert-in .2s ease-out;
        }

        .announcement-alert::before {
            content: '';
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .announcement-alert-close {
            position: absolute;
            top: 50%;
            right: .55rem;
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: currentColor;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .announcement-alert-close:hover {
            background: rgba(15, 23, 42, .08);
        }

        .announcement-alert-close:focus-visible {
            outline: 2px solid currentColor;
            outline-offset: 1px;
        }

        .announcement-alert.is-closing {
            animation: announcement-alert-out .2s ease-in forwards;
        }

        @keyframes announcement-alert-in {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes announcement-alert-out {
            to {
                opacity: 0;
                transform: translateY(-4px);
            }
        }

        .announcement-alert.success {
            color: #166534;
            background: #dcfce7;
            border-color: #bbf7d0;
        }

        .announcement-alert.error {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fecaca;
        }

        @media (prefers-reduced-motion: reduce) {

            .announcement-alert,
            .announcement-alert.is-closing {
                animation: none;
            }
        }

        .admin-announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
        }

        .admin-announcement-header h1 {
            margin: .25rem 0 .35rem;
            color: var(--primary-color);
            font-size: 1.6rem;
        }

        .admin-announcement-header p,
        .admin-announcement-card p {
            color: var(--text-muted);
            font-size: .92rem;
        }

        .admin-eyebrow,
        .admin-announcement-label {
            color: var(--secondary-color);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .admin-announcement-icon {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            color: var(--secondary-color);
            background: #eff6ff;
            border-radius: 8px;
            font-size: 1.5rem;
        }

        .admin-announcement-list {
            display: grid;
            gap: 1rem;
        }

        .admin-announcement-card {
            min-width: 0;
            min-height: 250px;
            box-shadow: var(--shadow-sm);
        }

        .admin-card-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 8px;
        }

        .admin-card-icon.blue {
            color: #2563eb;
            background: #eff6ff;
        }

        .admin-card-icon.green {
            color: #16a34a;
            background: #f0fdf4;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.02em;
        }

        /* --- Enhanced Search Suite --- */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-bar input[type="search"] {
            padding: 0.6rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            min-width: 280px;
            outline: none;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .search-bar input[type="search"]:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .search-bar button[type="submit"] {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--primary-color);
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease-in-out;
        }

        .search-bar button[type="submit"]:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .search-bar a {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem;
            transition: color 0.2s;
        }

        .search-bar a:hover {
            color: var(--danger-color);
        }

        /* --- Component Cards & Data Tables --- */
        .content-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            text-transform: capitalize;
        }

        .employee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-table th,
        .employee-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
            font-size: 0.9rem;
        }

        .employee-table th {
            background-color: var(--light-bg);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 2px solid var(--border-color);
        }

        .employee-table tbody tr:last-child td {
            border-bottom: none;
        }

        .employee-table tbody tr {
            transition: background-color 0.2s ease-in-out;
        }

        .employee-table tbody tr:hover {
            background-color: var(--light-bg);
        }

        .no-name {
            font-style: italic;
            color: var(--text-muted);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons form {
            display: inline-block;
        }

        .no-results {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* --- Overlay Modals Systems --- */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: calc(var(--border-radius) * 1.5);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 480px;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .announcement-modal-content {
            width: min(92%, 640px);
            max-height: 90vh;
            overflow-y: auto;
            padding: 1.75rem;
        }

        .announcement-modal-content h3 {
            padding-right: 2rem;
        }

        .announcement-modal-form .announcement-form-grid input,
        .announcement-modal-form .announcement-form-grid textarea {
            background: #fff;
        }

        .announcement-modal-form .announcement-form-actions {
            justify-content: flex-end;
        }

        .modal-close {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s, background-color 0.2s;
            line-height: 1;
            border-radius: 6px;
        }

        .modal-close:hover {
            color: var(--primary-color);
        }

        .modal-content h3 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        .modal-subtitle {
            color: var(--text-muted);
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
        }

        .modal-form .form-field {
            margin-bottom: 1.25rem;
        }

        .modal-form label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .modal-form input[type="text"],
        .modal-form input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease-in-out;
            background-color: var(--light-bg);
        }

        .modal-form input[type="text"]:focus,
        .modal-form input[type="password"]:focus {
            border-color: var(--secondary-color);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .profile-upload {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem;
            border: 1px dashed #b8c4d4;
            border-radius: 10px;
            background: #f8fafc;
        }

        .profile-upload input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .profile-upload-button {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem .8rem;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            text-transform: none;
            letter-spacing: normal;
        }

        .profile-upload-button:hover {
            background: #1d4ed8;
        }

        .profile-upload-name {
            min-width: 0;
            color: var(--text-muted);
            font-size: .88rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 400;
        }

        .admin-password-field {
            position: relative;
        }

        .admin-password-field input {
            padding-right: 2.7rem;
        }

        .admin-password-toggle {
            position: absolute;
            top: 50%;
            right: .4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            margin: 0;
            padding: 0;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
        }

        .admin-password-toggle:hover {
            color: var(--secondary-color);
        }

        .modal-form .btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.95rem;
            margin-top: 1rem;
        }

        .divider {
            border: 0;
            border-top: 1px solid var(--border-color);
            margin: 1.5rem 0;
        }

        .helper-text {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .action-buttons .btn {
            min-width: 75px;
            font-size: 0.8rem;
            padding: 0.45rem 0.9rem;
        }

        /* --- Viewport Adaptive Breakpoints --- */
        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .admin-container {
                min-height: calc(100vh - var(--header-height));
            }

            .admin-content {
                margin-left: 0;
                height: auto;
                min-height: calc(100vh - var(--header-height));
                padding: 1.5rem;
            }
        }

        @media (max-width: 900px) {
            .announcement-tab {
                min-height: 40px;
                padding-inline: .6rem;
            }
        }

        @media (max-width: 640px) {
            .announcement-form-grid {
                grid-template-columns: 1fr;
            }

            .announcement-form-grid .full-width {
                grid-column: span 1;
            }

            .announcement-file-wrapper {
                flex-direction: column;
                align-items: flex-start;
            }

            .announcement-tabs {
                flex-direction: column;
            }

            .admin-welcome,
            .admin-welcome-copy {
                align-items: flex-start;
            }

            .admin-welcome {
                flex-direction: column;
            }

            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                width: 100%;
            }

            .search-bar input {
                flex: 1;
                min-width: 0;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .action-buttons form,
            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeradmin.php'; ?>
    <div class="admin-container">
        <?php include_once '../includes/sidenav.php'; ?>
        <main class="admin-content">
            <section id="admin-announcements-view"
                class="admin-view <?php echo $activeAdminView === 'announcements' ? 'active' : ''; ?>">
                <div class="admin-welcome">
                    <div class="admin-welcome-copy">
                        <div class="admin-welcome-avatar"><i class="fa-solid fa-user-shield"></i></div>
                        <div>
                            <h1>Welcome, <span style="text-transform: capitalize;">
                                    <?php echo htmlspecialchars($admin_display_name, ENT_QUOTES, 'UTF-8'); ?>
                                </span></h1>
                            <p>Manage school personnel records and administrative services from your workspace.</p>
                        </div>
                    </div>
                    <div class="admin-welcome-date">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo date('F j, Y'); ?>
                    </div>
                </div>

                <?php if ($announcement_message !== ''): ?>
                    <div class="announcement-alert <?php echo $announcement_status === 'success' ? 'success' : 'error'; ?>"
                        role="status" aria-live="polite" data-alert-timeout="5000">
                        <span><?php echo htmlspecialchars($announcement_message, ENT_QUOTES, 'UTF-8'); ?></span>
                        <button type="button" class="announcement-alert-close" aria-label="Dismiss notification">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php endif; ?>
                <div class="announcement-management">
                    <div class="announcement-tabs" role="tablist" aria-label="Announcement workspace">
                        <button type="button" class="announcement-tab" id="announcement-create-tab" role="tab"
                            aria-controls="announcement-create-panel" aria-selected="true">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            <span>Create Announcement</span>
                        </button>
                        <button type="button" class="announcement-tab" id="announcement-manage-tab" role="tab"
                            aria-controls="announcement-manage-panel" aria-selected="false" tabindex="-1">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            <span>Announcement Management</span>
                        </button>
                    </div>
                    <div id="announcement-create-panel" class="announcement-tab-panel active" role="tabpanel"
                        aria-labelledby="announcement-create-tab">
                        <div class="announcement-form-panel" id="announcement-form">
                            <h2><i class="fa-solid fa-pen-to-square"></i>
                                <?php echo $announcement_edit ? 'Edit Announcement' : 'Create Announcement'; ?></h2>
                            <form action="../controllers/announcement_controller.php" method="post"
                                enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action"
                                    value="<?php echo $announcement_edit ? 'update' : 'create'; ?>">
                                <?php if ($announcement_edit): ?><input type="hidden" name="announcement_id"
                                        value="<?php echo (int) $announcement_edit['announcement_id']; ?>"><?php endif; ?>
                                <div class="announcement-form-grid">
                                    <label>Title <input type="text" name="title" maxlength="255" required
                                            placeholder="Enter announcement title..."
                                            value="<?php echo htmlspecialchars($announcement_edit['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label>
                                    <label>Category <input type="text" name="category" maxlength="100"
                                            placeholder="e.g. General, Urgent..."
                                            value="<?php echo htmlspecialchars($announcement_edit['category'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>"></label>
                                    <label class="full-width">Announcement details <textarea name="body"
                                            placeholder="Write the announcement details here..."
                                            required><?php echo htmlspecialchars($announcement_edit['body'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label>
                                    <label class="full-width">Optional URL <input type="url" name="external_url"
                                            placeholder="https://example.com"
                                            value="<?php echo htmlspecialchars($announcement_edit['external_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label>
                                    <div class="announcement-file-field full-width">
                                        <span>Optional announcement image</span>
                                        <div class="announcement-file-wrapper">
                                            <input id="announcement_image" class="announcement-file-input" type="file"
                                                name="announcement_image" accept=".jpg,.jpeg,.png,.gif,.webp">
                                            <label class="announcement-file-picker" for="announcement_image">
                                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                                <span>Choose image</span>
                                            </label>
                                            <span id="announcement-image-name" class="announcement-file-name">No image
                                                selected</span>
                                        </div>
                                    </div>
                                    <div class="announcement-file-field full-width">
                                        <span>Optional attachment</span>
                                        <div class="announcement-file-wrapper">
                                            <input id="announcement_attachment" class="announcement-file-input"
                                                type="file" name="attachment"
                                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv,.doc,.docx,.xls,.xlsx">
                                            <label class="announcement-file-picker" for="announcement_attachment">
                                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                                <span>Choose file</span>
                                            </label>
                                            <span id="announcement-file-name" class="announcement-file-name">No file
                                                selected</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="announcement-form-actions">
                                    <button class="btn btn-success" type="submit"><i
                                            class="fa-solid fa-paper-plane"></i>
                                        <?php echo $announcement_edit ? 'Update' : 'Publish Announcement'; ?></button>
                                    <?php if ($announcement_edit): ?><a class="btn btn-view"
                                            href="index.php?view=announcements">Cancel</a><?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="announcement-manage-panel" class="announcement-tab-panel" role="tabpanel"
                        aria-labelledby="announcement-manage-tab" hidden>
                        <div class="announcement-admin-container">
                            <div class="announcement-admin-header">
                                <div>
                                    <h2><i class="fa-solid fa-bullhorn"></i> Announcement Management</h2>
                                    <p class="section-desc">Create, publish, edit, and manage school-wide announcements.
                                    </p>
                                </div>
                            </div>

                            <?php if (empty($announcement_rows)): ?>
                                <div class="announcement-empty-state">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <h3>No Announcements Created Yet</h3>
                                    <p>Use the form above to post your first announcement for employees.</p>
                                </div>
                            <?php else: ?>
                                <div class="admin-announcement-grid">
                                    <?php foreach ($announcement_rows as $row): ?>
                                        <?php
                                        // Dynamic Category Icon & Color Mapping
                                        $cat = strtolower(trim($row['category'] ?? ''));
                                        switch (true) {
                                            case str_contains($cat, 'admin'):
                                                $iconClass = 'fa-users';
                                                $colorClass = 'purple';
                                                break;
                                            case str_contains($cat, 'deadline') || str_contains($cat, 'urgent'):
                                                $iconClass = 'fa-clock';
                                                $colorClass = 'orange';
                                                break;
                                            case str_contains($cat, 'health') || str_contains($cat, 'safety'):
                                                $iconClass = 'fa-heart-pulse';
                                                $colorClass = 'red';
                                                break;
                                            case str_contains($cat, 'service') || str_contains($cat, 'record'):
                                                $iconClass = 'fa-calendar-check';
                                                $colorClass = 'green';
                                                break;
                                            default:
                                                $iconClass = 'fa-bullhorn';
                                                $colorClass = 'blue';
                                                break;
                                        }

                                        $isPublished = (bool) ($row['is_published'] ?? false);
                                        ?>
                                        <article
                                            class="admin-announcement-card <?php echo $isPublished ? 'is-published' : 'is-draft'; ?>">
                                            <div class="admin-card-header">
                                                <div class="admin-card-icon <?php echo $colorClass; ?>">
                                                    <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                                </div>
                                                <div class="admin-card-meta">
                                                    <span class="category-badge <?php echo $colorClass; ?>-tag">
                                                        <?php echo htmlspecialchars($row['category']); ?>
                                                    </span>
                                                    <span
                                                        class="status-badge <?php echo $isPublished ? 'status-published' : 'status-draft'; ?>">
                                                        <i
                                                            class="fa-solid <?php echo $isPublished ? 'fa-check-circle' : 'fa-eye-slash'; ?>"></i>
                                                        <?php echo $isPublished ? 'Published' : 'Draft'; ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="admin-card-body">
                                                <?php if (!empty($row['image_name'])): ?>
                                                    <img src="../controllers/announcement_image.php?id=<?php echo (int) $row['announcement_id']; ?>"
                                                        alt=""
                                                        style="display:block; width:100%; height:180px; object-fit:contain; object-position:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:.75rem;">
                                                <?php endif; ?>
                                                <h3 class="admin-card-title">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </h3>
                                                <p class="admin-card-text">
                                                    <?php echo htmlspecialchars($row['body']); ?>
                                                </p>

                                                <?php if (!empty($row['external_url']) || !empty($row['file_name'])): ?>
                                                    <div class="admin-card-attachments">
                                                        <?php if (!empty($row['external_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($row['external_url']); ?>"
                                                                target="_blank" class="att-link">
                                                                <i class="fa-solid fa-link"></i> External Link
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($row['file_name'])): ?>
                                                            <span class="att-file">
                                                                <i class="fa-solid fa-paperclip"></i>
                                                                <?php echo htmlspecialchars($row['file_name']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="admin-card-footer">
                                                <span class="card-date">
                                                    <i class="fa-regular fa-calendar"></i>
                                                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                                </span>
                                                <div class="admin-card-actions">
                                                    <form method="POST" action="../controllers/announcement_controller.php">
                                                        <input type="hidden" name="csrf_token"
                                                            value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="action" value="toggle_publish">
                                                        <input type="hidden" name="announcement_id"
                                                            value="<?php echo (int) $row['announcement_id']; ?>">
                                                        <button type="submit" class="publish-toggle"
                                                            aria-label="<?php echo $isPublished ? 'Unpublish announcement' : 'Publish announcement'; ?>"
                                                            aria-pressed="<?php echo $isPublished ? 'true' : 'false'; ?>"
                                                            title="<?php echo $isPublished ? 'Unpublish announcement' : 'Publish announcement'; ?>"></button>
                                                    </form>

                                                    <button type="button" class="btn-icon-action btn-edit announcement-edit-btn"
                                                        title="Edit Announcement" aria-label="Edit announcement"
                                                        data-id="<?php echo (int) $row['announcement_id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-category="<?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-body="<?php echo htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-url="<?php echo htmlspecialchars($row['external_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-file-name="<?php echo htmlspecialchars($row['file_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>

                                                    <form method="POST" action="../controllers/announcement_controller.php"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                        <input type="hidden" name="csrf_token"
                                                            value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="announcement_id"
                                                            value="<?php echo (int) $row['announcement_id']; ?>">
                                                        <button type="submit" class="btn-icon-action btn-delete" title="Delete">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="admin-personnel-view"
                class="admin-view <?php echo $activeAdminView === 'personnel' ? 'active' : ''; ?>">
                <div class="content-header">
                    <h1>Employee Dashboard</h1>

                    <form class="search-bar" action="index.php" method="get">
                        <input type="hidden" name="view" value="personnel">
                        <input type="search" id="search" name="search_query" placeholder="Search Employee Name"
                            value="<?php echo htmlspecialchars($search_query); ?>" />
                        <button type="submit">🔍</button>
                        <?php if (!empty($search_query)): ?>
                            <a href="index.php?view=personnel">Clear</a>
                        <?php endif; ?>
                    </form>

                </div>

                <div class="content-card">
                    <?php
                    $base_sql = "SELECT a.EmployeeID, a.Employeenumber, a.username, d.name
                             FROM admin AS a
                             LEFT JOIN employee_details AS d ON a.EmployeeID = d.EmployeeID";

                    $where_conditions = ["a.role = 'staff'"];
                    $params = [];
                    $types = "";

                    if ($search_query !== '') {
                        $where_conditions[] = "(d.name LIKE ? OR a.username LIKE ? OR a.Employeenumber LIKE ?)";
                        $search_term = "%" . $search_query . "%";
                        $params[] = $search_term;
                        $params[] = $search_term;
                        $params[] = $search_term;
                        $types .= "sss";
                    }

                    $sql = $base_sql . " WHERE " . implode(" AND ", $where_conditions) . " ORDER BY d.name ASC, a.username ASC";

                    $stmt = $conn->prepare($sql);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        echo "<table class='employee-table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Employee Number</th>";
                        echo "<th>Full Name</th>";
                        echo "<th>Action</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        while ($row = $result->fetch_assoc()) {
                            if (!empty($row['name'])) {
                                $display_name = htmlspecialchars($row['name']);
                            } else {
                                $display_name = '<i class="no-name">' . htmlspecialchars($row['username']) . ' (Username)</i>';
                            }

                            $emp_number = htmlspecialchars($row['Employeenumber']);
                            $view_link = "viewemployee.php?id=" . urlencode($row['EmployeeID']);
                            $employee_id = $row['EmployeeID'];

                            echo "<tr>";
                            echo "<td>" . $emp_number . "</td>";
                            echo "<td>" . $display_name . "</td>";
                            echo '<td><div class="action-buttons">';
                            echo '<a href="' . $view_link . '" class="btn btn-view">View</a>';
                            echo '<form action="deleteemployee.php" method="POST" onsubmit="return confirm(\'Are you sure you want to PERMANENTLY delete this employee? This action will remove all their details and documents and cannot be undone.\');">';
                            echo '<input type="hidden" name="EmployeeID" value="' . $employee_id . '">';
                            echo '<button type="submit" class="btn btn-danger">Delete</button>';
                            echo '</form>';
                            echo '</div></td>';
                            echo "</tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        if (!empty($search_query)) {
                            echo "<p class='no-results'>No employees found matching your search.</p>";
                        } else {
                            echo "<p class='no-results'>No staff employees found in the system.</p>";
                        }
                    }
                    $stmt->close();
                    ?>
                </div>
            </section>
        </main>
    </div>

    <div id="create-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close" id="closeCreateModal">&times;</span>
            <h3>Create Employee Account</h3>

            <form action="../controllers/employeeController.php" method="post" class="modal-form">
                <div class="form-field">
                    <label for="emp_num">Employee Number</label>
                    <input id="emp_num" type="text" placeholder="Enter employee Number" name="Employeenumber" required>
                </div>
                <div class="form-field">
                    <label for="emp_user">Username</label>
                    <input id="emp_user" type="text" placeholder="Enter username" name="username" required>
                </div>
                <div class="form-field">
                    <label for="emp_pass">Password</label>
                    <div class="admin-password-field">
                        <input id="emp_pass" type="password" placeholder="Enter password" name="password" required>
                        <button type="button" class="admin-password-toggle" data-password-target="emp_pass"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                </div>

                <input type="submit" value="Create Account" class="btn btn-success">
            </form>
        </div>
    </div>

    <div id="announcement-edit-modal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-content announcement-modal-content" role="dialog" aria-modal="true"
            aria-labelledby="announcement-edit-heading">
            <button type="button" class="modal-close" id="closeAnnouncementEditModal"
                aria-label="Close edit announcement dialog">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
            <h3 id="announcement-edit-heading">Edit Announcement</h3>
            <p class="modal-subtitle">Update the announcement details without leaving this page.</p>

            <form action="../controllers/announcement_controller.php" method="post" enctype="multipart/form-data"
                class="announcement-modal-form">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="announcement_id" id="announcement-edit-id">
                <div class="announcement-form-grid">
                    <label for="announcement-edit-title-input">Title
                        <input id="announcement-edit-title-input" type="text" name="title" maxlength="255" required>
                    </label>
                    <label for="announcement-edit-category">Category
                        <input id="announcement-edit-category" type="text" name="category" maxlength="100">
                    </label>
                    <label for="announcement-edit-body" class="full-width">Announcement details
                        <textarea id="announcement-edit-body" name="body" required></textarea>
                    </label>
                    <label for="announcement-edit-url" class="full-width">Optional URL
                        <input id="announcement-edit-url" type="url" name="external_url"
                            placeholder="https://example.com">
                    </label>
                    <div class="announcement-file-field full-width">
                        <span>Optional replacement announcement image</span>
                        <div class="announcement-file-wrapper">
                            <input id="announcement-edit-image" class="announcement-file-input" type="file"
                                name="announcement_image" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <label class="announcement-file-picker" for="announcement-edit-image">
                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                <span>Choose image</span>
                            </label>
                            <span id="announcement-edit-image-name" class="announcement-file-name">No replacement image
                                selected</span>
                        </div>
                    </div>
                    <div class="announcement-file-field full-width">
                        <span>Optional replacement attachment</span>
                        <div class="announcement-file-wrapper">
                            <input id="announcement-edit-attachment" class="announcement-file-input" type="file"
                                name="attachment"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv,.doc,.docx,.xls,.xlsx">
                            <label class="announcement-file-picker" for="announcement-edit-attachment">
                                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                <span>Choose file</span>
                            </label>
                            <span id="announcement-edit-file-name" class="announcement-file-name">No replacement
                                selected</span>
                        </div>
                    </div>
                </div>
                <div class="announcement-form-actions">
                    <button type="button" class="btn btn-view" id="cancelAnnouncementEdit">Cancel</button>
                    <button class="btn btn-success" type="submit">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close" id="closeEditModal">&times;</span>
            <h3>Edit My Admin Account</h3>
            <p class="modal-subtitle">Update your public profile, username, or password.</p>

            <form action="updateadminaccount.php" method="POST" enctype="multipart/form-data" class="modal-form"
                onsubmit="return validatePassword();">
                <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">

                <div class="form-field">
                    <label for="edit_full_name">Full Name</label>
                    <input id="edit_full_name" type="text" name="full_name" maxlength="150" value=""
                        placeholder="Enter your full name" required>
                </div>

                <div class="form-field">
                    <label for="edit_avatar">Profile Avatar</label>
                    <div class="profile-upload">
                        <input id="edit_avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <label class="profile-upload-button" for="edit_avatar">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                            Browse image
                        </label>
                        <span id="edit-avatar-name" class="profile-upload-name">No image selected</span>
                    </div>
                    <small>Optional · JPG, PNG, GIF, or WEBP · Maximum 5 MB</small>
                </div>

                <div class="form-field">
                    <label for="edit_username">Admin Username</label>
                    <input id="edit_username" type="text" name="username"
                        value="<?php echo htmlspecialchars($current_username, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <hr class="divider">

                <p class="helper-text"><strong>Change Password</strong> (Leave blank to keep current password)</p>

                <div class="form-field">
                    <label for="new_password">New Password</label>
                    <div class="admin-password-field">
                        <input id="new_password" type="password" name="new_password">
                        <button type="button" class="admin-password-toggle" data-password-target="new_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="form-field">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="admin-password-field">
                        <input id="confirm_password" type="password" name="confirm_password">
                        <button type="button" class="admin-password-toggle" data-password-target="confirm_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                </div>

                <input type="submit" name="update" value="Update Account" class="btn btn-success">
            </form>
        </div>
    </div>

    <script>
        const editAvatarInput = document.getElementById('edit_avatar');
        const editAvatarName = document.getElementById('edit-avatar-name');
        if (editAvatarInput && editAvatarName) {
            editAvatarInput.addEventListener('change', function () {
                editAvatarName.textContent = this.files.length ? this.files[0].name : 'No image selected';
            });
        }

        const announcementFileInput = document.getElementById('announcement_attachment');
        const announcementFileName = document.getElementById('announcement-file-name');
        const announcementImageInput = document.getElementById('announcement_image');
        const announcementImageName = document.getElementById('announcement-image-name');
        const announcementEditModal = document.getElementById('announcement-edit-modal');
        const closeAnnouncementEditModal = document.getElementById('closeAnnouncementEditModal');
        const cancelAnnouncementEdit = document.getElementById('cancelAnnouncementEdit');
        const announcementEditFileInput = document.getElementById('announcement-edit-attachment');
        const announcementEditFileName = document.getElementById('announcement-edit-file-name');
        const announcementEditImageInput = document.getElementById('announcement-edit-image');
        const announcementEditImageName = document.getElementById('announcement-edit-image-name');

        function closeAnnouncementEditor() {
            if (!announcementEditModal) return;
            announcementEditModal.style.display = 'none';
            announcementEditModal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('.announcement-edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                if (!announcementEditModal) return;
                document.getElementById('announcement-edit-id').value = this.dataset.id || '';
                document.getElementById('announcement-edit-title-input').value = this.dataset.title || '';
                document.getElementById('announcement-edit-category').value = this.dataset.category || 'General';
                document.getElementById('announcement-edit-body').value = this.dataset.body || '';
                document.getElementById('announcement-edit-url').value = this.dataset.url || '';
                announcementEditFileName.textContent = this.dataset.fileName
                    ? `Current file: ${this.dataset.fileName}`
                    : 'No replacement selected';
                announcementEditModal.style.display = 'flex';
                announcementEditModal.setAttribute('aria-hidden', 'false');
                document.getElementById('announcement-edit-title-input').focus();
            });
        });

        if (announcementEditImageInput && announcementEditImageName) {
            announcementEditImageInput.addEventListener('change', function () {
                announcementEditImageName.textContent = this.files.length > 0
                    ? this.files[0].name
                    : 'No replacement image selected';
            });
        }

        if (announcementEditFileInput && announcementEditFileName) {
            announcementEditFileInput.addEventListener('change', function () {
                announcementEditFileName.textContent = this.files.length > 0
                    ? this.files[0].name
                    : 'No replacement selected';
            });
        }

        closeAnnouncementEditModal?.addEventListener('click', closeAnnouncementEditor);
        cancelAnnouncementEdit?.addEventListener('click', closeAnnouncementEditor);
        announcementEditModal?.addEventListener('click', function (event) {
            if (event.target === announcementEditModal) closeAnnouncementEditor();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && announcementEditModal?.style.display === 'flex') {
                closeAnnouncementEditor();
            }
        });

        if (announcementImageInput && announcementImageName) {
            announcementImageInput.addEventListener('change', function () {
                announcementImageName.textContent = this.files.length > 0 ? this.files[0].name : 'No image selected';
            });
        }

        if (announcementFileInput && announcementFileName) {
            announcementFileInput.addEventListener('change', function () {
                announcementFileName.textContent = this.files.length > 0
                    ? this.files[0].name
                    : 'No file selected';
            });
        }

        document.querySelectorAll('[data-alert-timeout]').forEach(alert => {
            const dismissAlert = () => {
                alert.classList.add('is-closing');
                window.setTimeout(() => alert.remove(), 220);
            };
            alert.querySelector('.announcement-alert-close')?.addEventListener('click', dismissAlert);
            window.setTimeout(dismissAlert, Number(alert.dataset.alertTimeout) || 5000);
        });

        const announcementTabs = document.querySelectorAll('.announcement-tab');
        const announcementPanels = document.querySelectorAll('.announcement-tab-panel');

        function showAnnouncementTab(tab) {
            const targetId = tab.getAttribute('aria-controls');
            announcementTabs.forEach(item => {
                const isSelected = item === tab;
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                item.tabIndex = isSelected ? 0 : -1;
            });
            announcementPanels.forEach(panel => {
                const isActive = panel.id === targetId;
                panel.hidden = !isActive;
                panel.classList.toggle('active', isActive);
            });
        }

        announcementTabs.forEach((tab, index) => {
            tab.addEventListener('click', () => showAnnouncementTab(tab));
            tab.addEventListener('keydown', event => {
                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
                event.preventDefault();
                const nextIndex = event.key === 'ArrowRight'
                    ? (index + 1) % announcementTabs.length
                    : (index - 1 + announcementTabs.length) % announcementTabs.length;
                announcementTabs[nextIndex].focus();
                showAnnouncementTab(announcementTabs[nextIndex]);
            });
        });

        const adminNavItems = document.querySelectorAll('[data-admin-view]');
        const adminViews = document.querySelectorAll('.admin-view');
        const adminSidebar = document.getElementById('adminSidebar');

        adminNavItems.forEach(item => {
            item.addEventListener('click', function (event) {
                const view = this.dataset.adminView;
                if (!view) return;
                event.preventDefault();
                adminNavItems.forEach(nav => nav.classList.remove('active'));
                adminViews.forEach(panel => panel.classList.remove('active'));
                this.classList.add('active');
                const target = document.getElementById(`admin-${view}-view`);
                if (target) target.classList.add('active');
                window.history.replaceState({}, document.title, `index.php?view=${encodeURIComponent(view)}`);
                if (adminSidebar) adminSidebar.classList.remove('show');
            });
        });

        const createModal = document.getElementById('create-account-form');
        const createBtn = document.getElementById('create-account');
        const closeCreateBtn = document.getElementById('closeCreateModal');

        if (createBtn && createModal) {
            createBtn.addEventListener('click', function () {
                createModal.style.display = 'flex';
            });
        }

        if (closeCreateBtn && createModal) {
            closeCreateBtn.addEventListener('click', function () {
                createModal.style.display = 'none';
            });

            createModal.addEventListener('click', function (event) {
                if (event.target === createModal) {
                    createModal.style.display = 'none';
                }
            });
        }

        const editModal = document.getElementById('edit-account-form');
        const editBtn = document.getElementById('openEditModal');
        const closeEditBtn = document.getElementById('closeEditModal');

        if (editBtn && editModal) {
            editBtn.addEventListener('click', function () {
                editModal.style.display = 'flex';
            });
        }

        if (closeEditBtn && editModal) {
            closeEditBtn.addEventListener('click', function () {
                editModal.style.display = 'none';
            });

            editModal.addEventListener('click', function (event) {
                if (event.target === editModal) {
                    editModal.style.display = 'none';
                }
            });
        }

        document.querySelectorAll('.admin-password-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.passwordTarget);
                if (!input) return;
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                this.querySelector('i').className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                this.title = isHidden ? 'Hide password' : 'Show password';
            });
        });

        function validatePassword() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass !== "" && newPass !== confirmPass) {
                alert("Error: New passwords do not match.");
                return false;
            }
            return true;
        }

    </script>
</body>

</html>