<?php
session_start();
include_once '../db/conn.php';

$EmployeeID = $_SESSION['EmployeeID'] ?? null;
$display_name = $_SESSION['name'] ?? null;

if ($EmployeeID) {
    $sql = "SELECT name FROM employee_details WHERE EmployeeID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $EmployeeID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $display_name = $row['name'];
        }
        $stmt->close();
    }
}

$announcements = [];
$announcementResult = $conn->query("SELECT a.announcement_id, a.title, a.category, a.body, a.external_url, a.file_name,
    a.image_name, a.created_at, a.updated_at,
    COALESCE(NULLIF(d.name, ''), NULLIF(ad.username, ''), 'Administration') AS creator_name,
    d.avatar_image AS creator_avatar, d.avatar_mime_type AS creator_avatar_mime
    FROM announcements a
    INNER JOIN admin ad
        ON ad.EmployeeID = a.created_by
       AND ad.role = 'superadmin'
    LEFT JOIN employee_details d ON d.EmployeeID = ad.EmployeeID
    WHERE a.is_published = 1 ORDER BY a.created_at DESC");
if ($announcementResult) {
    while ($announcementRow = $announcementResult->fetch_assoc()) {
        $announcements[] = $announcementRow;
    }
}

// Batch-load up to 10 gallery images per published announcement.
$announcementGalleries = [];
if (!empty($announcements)) {
    $announcementIds = array_map(static fn($row) => (int) $row['announcement_id'], $announcements);
    $announcementIds = array_values(array_unique(array_filter($announcementIds)));
    if (!empty($announcementIds)) {
        $idList = implode(',', $announcementIds);
        $galleryResult = $conn->query("SELECT image_id, announcement_id, image_name FROM announcement_images WHERE announcement_id IN ({$idList}) ORDER BY announcement_id ASC, sort_order ASC, image_id ASC");
        if ($galleryResult) {
            while ($galleryRow = $galleryResult->fetch_assoc()) {
                $galleryAnnouncementId = (int) $galleryRow['announcement_id'];
                if (!isset($announcementGalleries[$galleryAnnouncementId])) {
                    $announcementGalleries[$galleryAnnouncementId] = [];
                }
                if (count($announcementGalleries[$galleryAnnouncementId]) < 10) {
                    $announcementGalleries[$galleryAnnouncementId][] = $galleryRow;
                }
            }
        }
    }
}

$employeenumber = "";
$details = [
    'employee_number' => '',
    'name' => '',
    'position' => '',
    'basic_salary' => '',
    'original_appointment_date' => '',
    'latest_appointment_date' => '',
    'salary_grade' => '',
    'step' => '',
    'gender' => '',
    'subject_handled' => '',
    'marital_status' => '',
    'birthdate' => '',
    'residential_address' => '',
    'email_address' => '',
    'contact_number' => '',
    'gsis_number' => '',
    'philhealth_number' => '',
    'pagibig_number' => '',
    'tin_number' => '',
    'prc_license' => '',
    'expiration_date' => '',
    'previous_employer' => '',
    'plantilla_number' => '',
    'avatar_image' => null,
    'avatar_mime_type' => '',
    'educational_attainment' => '',
    'eligibility' => ''
];

$avatar_src = "../src/images/avatar.png";

if ($EmployeeID) {
    $sql_admin = "SELECT Employeenumber FROM admin WHERE EmployeeID = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    if ($stmt_admin) {
        $stmt_admin->bind_param("i", $EmployeeID);
        $stmt_admin->execute();
        $stmt_admin->bind_result($employeenumber);
        $stmt_admin->fetch();
        $stmt_admin->close();
    }

    $details['employee_number'] = $employeenumber;

    $sql_details = "SELECT * FROM employee_details WHERE EmployeeID = ?";
    $stmt_details = $conn->prepare($sql_details);
    if ($stmt_details) {
        $stmt_details->bind_param("i", $EmployeeID);
        $stmt_details->execute();
        $result = $stmt_details->get_result();

        if ($result->num_rows > 0) {
            $db_data = $result->fetch_assoc();
            $details = array_merge($details, $db_data);
            $details['employee_number'] = $employeenumber;

            if (!empty($details['avatar_image']) && !empty($details['avatar_mime_type'])) {
                $avatar_src = "data:" . $details['avatar_mime_type'] . ";base64," . base64_encode($details['avatar_image']);
            }
        }
        $stmt_details->close();
    }
}

function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_name_upper($value)
{
    return strtoupper(trim((string) $value));
}

function format_capitalized($value)
{
    $value = trim((string) $value);
    if ($value === '')
        return '';
    return ucwords(strtolower($value));
}

function format_email($value)
{
    return strtolower(trim((string) $value));
}

function format_date_pretty($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00')
        return '';
    $timestamp = strtotime($value);
    return ($timestamp === false) ? $value : date('F j, Y', $timestamp);
}

function format_currency_php($value)
{
    if ($value === null || $value === '')
        return '';
    if (!is_numeric($value))
        return (string) $value;
    return '₱' . number_format((float) $value, 2);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Personnel Administration | Tapuyan National High School</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --success: #10b981;
            --success-hover: #059669;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-color: #334155;
            --text-muted: #64748b;
            --shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 8px -1px rgba(0, 0, 0, 0.03);
            --border-radius: 12px;
            --header-height: 65px;
            --sidebar-width: 270px;
            --transition: all 0.2s ease-in-out;
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

        /* --- Layout Architecture with Fixed Sidenav --- */
        .app-layout {
            display: flex;
            height: calc(100vh - var(--header-height));
            width: 100%;
            position: relative;
        }

        /* Fixed Sidebar Alignment */
        .app-sidebar {
            position: fixed !important;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            z-index: 100;
        }

        /* Main Scrollable Viewport */
        .app-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-width: 0;
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }

        .employee-content-shell,
        .profile-view {
            min-height: 100%;
        }

        .employee-view {
            display: none;
        }

        .employee-view.active {
            display: block;
        }

        .employee-welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            margin: 2rem 2rem 0;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #0ea5e9 100%);
            border-radius: var(--border-radius);
            box-shadow: 0 12px 28px -12px rgba(37, 99, 235, .55);
        }

        .employee-welcome[hidden] {
            display: none;
        }

        .employee-welcome-copy {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .employee-welcome-avatar {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            color: #2563eb;
            background: #ffffff;
            border-radius: 10px;
            font-size: 1.15rem;
        }

        .employee-welcome h1 {
            margin: 0 0 .15rem;
            color: #ffffff;
            font-size: 1.35rem;
            line-height: 1.25;
        }

        .employee-welcome p {
            margin: 0;
            color: rgba(255, 255, 255, .86);
            font-size: .86rem;
        }

        .employee-welcome-date {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-shrink: 0;
            padding: .55rem .75rem;
            color: #ffffff;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 700;
        }

        .employee-content-shell {
            padding: 1.25rem 2rem 2rem;
            background: var(--light-bg);
        }

        .view-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.25rem;
        }

        .view-eyebrow,
        .announcement-label {
            display: block;
            color: var(--accent);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .announcement-edited {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-left: .35rem;
            padding: .15rem .4rem;
            color: #92400e;
            background: #fef3c7;
            border-radius: 4px;
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: none;
            vertical-align: middle;
        }

        .view-heading h1 {
            margin: .25rem 0 .35rem;
            color: var(--primary);
            font-size: 1.6rem;
        }

        .view-heading p,


        .view-heading-icon {
            color: var(--accent);
            font-size: 2rem;
            padding: .75rem;
            background: #eff6ff;
            border-radius: 8px;
        }


        /* Announcement Cards & Typography Enhancements */
        .announcement-creator {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .8rem;
        }

        .announcement-creator-avatar {
            width: 2.45rem;
            height: 2.45rem;
            flex: 0 0 2.45rem;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #dbeafe;
            background: #eff6ff;
        }

        .announcement-creator-copy {
            min-width: 0;
            color: var(--text-muted);
            font-size: .78rem;
            line-height: 1.35;
        }

        .announcement-creator-copy strong {
            color: var(--primary);
            font-size: .86rem;
        }

        .announcement-creator-copy time {
            display: block;
            margin-top: .1rem;
        }

        .announcement-card {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            margin-bottom: 25px;
            padding: 1.35rem 1.5rem;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.08);
        }

        .announcement-card-image {
            display: block;
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 420px;
            object-fit: contain;
            object-position: center;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin: .85rem 0 1rem;
        }

        .announcement-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
            gap: .5rem;
            margin: 0 0 1rem;
        }

        .announcement-gallery a {
            display: block;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
        }

        .announcement-gallery img {
            display: block;
            width: 100%;
            height: 76px;
            object-fit: cover;
            transition: transform .2s ease;
        }

        .announcement-gallery a:hover img {
            transform: scale(1.05);
        }

        .announcement-banner {
            position: relative;
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            margin: .85rem 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background: #0f172a;
            isolation: isolate;
        }

        .announcement-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: var(--ann-bg);
            background-size: cover;
            background-position: center;
            filter: blur(22px) brightness(.65);
            transform: scale(1.12);
        }

        .announcement-banner img {
            position: relative;
            z-index: 1;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform .25s ease;
        }

        .announcement-banner:hover img {
            transform: scale(1.01);
        }

        .announcement-photo-count {
            position: absolute;
            right: .6rem;
            bottom: .6rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .6rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, .75);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
        }

        .announcement-card-body {
            flex: 1;
            min-width: 0;
        }

        .announcement-card h2 {
            margin: 0.25rem 0 0.4rem;
            color: var(--primary);
            font-size: 1.08rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .announcement-meta {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: wrap;
            font-size: 0.78rem;
        }

        .announcement-date {
            color: var(--text-muted);
            font-weight: 500;
        }

        .meta-dot {
            color: #cbd5e1;
        }

        /* Category Color Badges */
        .announcement-card-icon.blue {
            color: #2563eb;
            background: #eff6ff;
        }

        .blue-text {
            color: #2563eb;
        }

        .announcement-card-icon.green {
            color: #16a34a;
            background: #f0fdf4;
        }

        .green-text {
            color: #16a34a;
        }

        .announcement-card-icon.purple {
            color: #7e22ce;
            background: #faf5ff;
        }

        .purple-text {
            color: #7e22ce;
        }

        .announcement-card-icon.orange {
            color: #c2410c;
            background: #fff7ed;
        }

        .orange-text {
            color: #c2410c;
        }

        .announcement-card-icon.red {
            color: #dc2626;
            background: #fef2f2;
        }

        .red-text {
            color: #dc2626;
        }

        /* Action Links and Download Pill Buttons */
        .announcement-actions {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-top: 0.85rem;
        }

        .announcement-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .announcement-btn.btn-link {
            color: var(--accent);
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
        }

        .announcement-btn.btn-link:hover {
            background-color: #dbeafe;
            color: var(--accent-hover);
        }

        .announcement-btn.btn-file {
            color: #475569;
            background-color: #f1f5f9;
            border: 1px solid var(--border-color);
        }

        .announcement-btn.btn-file:hover {
            background-color: #e2e8f0;
            color: var(--primary);
        }


        .embedded-view {
            height: calc(100vh - var(--header-height) - 4rem);
            min-height: 520px;
        }

        .embedded-view iframe {
            width: 100%;
            height: 100%;
            min-height: 520px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: #fff;
        }

        .profile-view {
            background: var(--light-bg);
        }

        #employee-form {
            display: grid;
            grid-template-columns: 310px 1fr;
            min-height: 100%;
        }

        /* Modern & Unobtrusive Placeholder Styling */
        input::placeholder,
        textarea::placeholder {
            color: #94a3b8;
            opacity: 0.55;
            font-weight: 400;
            text-transform: none;
            font-style: italic;
            font-size: 0.88rem;
        }

        /* --- Custom Toast Styling --- */
        .toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            min-width: 300px;
            background: #1e293b;
            color: #fff;
            padding: 16px 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            opacity: 0;
            transform: translateY(15px);
            transition: var(--transition);
            z-index: 10000;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid var(--accent);
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            border-left-color: var(--success);
        }

        .toast.error {
            border-left-color: var(--danger);
        }

        .print-only {
            display: none;
        }

        /* --- Buttons --- */
        .btn {
            padding: 0.65rem 1.25rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
        }

        .btn-success {
            background-color: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        .btn-pdf {
            background-color: #ffffff;
            color: var(--primary-light);
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-pdf:hover {
            background-color: #f1f5f9;
            border-color: var(--text-muted);
        }

        .header-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }

        #btn-save {
            display: none;
        }

        /* --- Profile Sidebar Layout --- */
        .profile-sidebar {
            background-color: var(--card-bg);
            padding: 2.5rem 1.5rem;
            text-align: center;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .avatar-container {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .avatar-preview {
            width: 130px;
            height: 130px;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 0 0 1px var(--border-color);
            border-radius: 50%;
            object-fit: cover;
            transition: var(--transition);
        }

        .avatar-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--accent);
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 20px;
            background: #eff6ff;
            transition: var(--transition);
        }

        .avatar-upload-label:hover {
            background: #dbeafe;
        }

        input[type="file"] {
            display: none;
        }

        .profile-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
            margin-top: 0.75rem;
            width: 100%;
        }

        .profile-position {
            font-size: 0.85rem;
            color: var(--danger);
            font-weight: 600;
            margin-bottom: 0.75rem;
            background-color: var(--danger-bg);
            padding: 2px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .profile-id {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
            background-color: var(--light-bg);
            padding: 0.35rem 1rem;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            display: inline-block;
        }

        /* --- Profile Content/Details Panels --- */
        .profile-details {
            padding: 2rem;
            background-color: var(--light-bg);
        }

        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.25rem;
        }

        .details-header h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .detail-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .detail-card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            padding: 1.25rem 1.75rem;
            color: var(--primary-light);
            border-bottom: 1px solid var(--border-color);
            background-color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-content {
            padding: 1.75rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field.full-width {
            grid-column: 1 / -1;
        }

        .field label {
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text-color);
            background-color: var(--light-bg);
            outline: none;
            transition: var(--transition);
            text-transform: uppercase;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--accent);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .field textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:read-only,
        textarea:read-only,
        select:disabled {
            background-color: #f8fafc;
            color: #475569;
            cursor: not-allowed;
            border-color: #e2e8f0;
            font-weight: 500;
        }

        /* --- Viewports Responsiveness --- */
        @media (max-width: 1200px) {
            #employee-form {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding: 2rem;
            }
        }

        @media (max-width: 1024px) {
            body {
                overflow: auto;
                height: auto;
            }

            .app-sidebar {
                transform: translateX(-100%);
            }

            .app-sidebar.show {
                transform: translateX(0);
            }

            .app-main {
                margin-left: 0;
                height: auto;
                overflow: visible;
            }

            .app-layout {
                height: auto;
            }
        }

        @media (max-width: 640px) {
            .employee-welcome {
                align-items: flex-start;
                flex-direction: column;
                margin: 1rem 1rem 0;
                padding: 1rem;
            }

            .employee-welcome-date {
                align-self: stretch;
                justify-content: center;
            }

            .details-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.25rem;
            }

            .header-buttons {
                width: 100%;
            }

            .header-buttons .btn {
                flex: 1;
            }

            .card-content {
                grid-template-columns: 1fr;
            }

            .profile-details {
                padding: 1rem;
            }

            .employee-content-shell {
                padding: 1rem;
            }
        }

        /* --- Print Styling --- */
        @media print {
            body {
                background: #fff !important;
                overflow: visible !important;
                height: auto !important;
                color: #000;
                font-size: 11pt;
            }

            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .app-sidebar,
            .profile-sidebar,
            .profile-details,
            .header-buttons,
            #btn-edit,
            #btn-save {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div id="toast" class="toast"></div>

    <!-- Header Navigation -->
    <?php include_once '../includes/headeremployee.php'; ?>

    <!-- Main App Container (Fixed Sidebar + Main Workstation) -->
    <div class="app-layout">

        <!-- Sidenav Include -->
        <?php $activeEmployeeView = 'announcements.php'; ?>
        <?php include_once '../includes/employeeSidenav.php'; ?>

        <!-- Main Workstation Viewport -->
        <main class="app-main">
            <section id="employee-welcome" class="employee-welcome" aria-label="Employee welcome">
                <div class="employee-welcome-copy">
                    <div class="employee-welcome-avatar" aria-hidden="true">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h1>Welcome, <span
                                style="text-transform: capitalize;"><?php echo esc($display_name ?: 'Employee'); ?></span>
                        </h1>
                        <p>Access your personnel information and school services from your workspace.</p>
                    </div>
                </div>
                <div class="employee-welcome-date">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    <?php echo date('F j, Y'); ?>
                </div>
            </section>

            <section id="employee-content-shell" class="employee-content-shell">
                <div id="announcement-view" class="employee-view active">
                    <div class="view-heading">
                        <div>
                            <span class="view-eyebrow">Employee Portal</span>
                            <h1>Announcements</h1>
                            <span>Stay updated with the latest notices and activities from Tapuyan National High School.
                            </span>
                        </div>
                        <i class="fa-solid fa-bullhorn view-heading-icon" aria-hidden="true"></i>
                    </div>

                    <div class="announcement-list">
                        <?php if (empty($announcements)): ?>
                            <article class="announcement-card empty-card">
                                <div class="announcement-card-icon blue"><i class="fa-solid fa-circle-info"></i></div>
                                <div class="announcement-card-body">
                                    <h2>No announcements yet</h2>
                                    <p>Published school announcements will appear here.</p>
                                </div>
                            </article>
                        <?php endif; ?>

                        <?php foreach ($announcements as $announcement): ?>
                            <?php
                            $announcementWasEdited = strtotime($announcement['updated_at']) > strtotime($announcement['created_at']);

                            // Map categories to dynamic FontAwesome icons and theme colors
                            $cat = strtolower(trim($announcement['category'] ?? ''));
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
                            ?>
                            <article class="announcement-card">
                                <div class="announcement-card-icon <?php echo $colorClass; ?>">
                                    <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="announcement-card-body">
                                    <?php
                                    $creatorAvatar = '../src/images/avatar.png';
                                    if (!empty($announcement['creator_avatar']) && !empty($announcement['creator_avatar_mime'])) {
                                        $creatorAvatar = 'data:' . esc($announcement['creator_avatar_mime']) . ';base64,' . base64_encode($announcement['creator_avatar']);
                                    }
                                    ?>
                                    <div class="announcement-creator">
                                        <img class="announcement-creator-avatar" src="<?php echo esc($creatorAvatar); ?>"
                                            alt="">
                                        <div class="announcement-creator-copy">
                                            <div><strong><?php echo esc($announcement['creator_name']); ?></strong> posted
                                                an announcement.</div>
                                            <time
                                                datetime="<?php echo esc(date('c', strtotime($announcement['created_at']))); ?>">
                                                <?php echo esc(date('M j, Y g:i A', strtotime($announcement['created_at']))); ?>
                                            </time>
                                        </div>
                                    </div>
                                    <div class="announcement-meta">
                                        <span class="announcement-label <?php echo $colorClass; ?>-text">
                                            <?php echo esc($announcement['category']); ?>
                                        </span>
                                        <?php if ($announcementWasEdited): ?>
                                            <span class="announcement-edited">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i> Edited
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h2><?php echo esc($announcement['title']); ?></h2>
                                    <?php
                                    $employeeGallery = $announcementGalleries[(int) $announcement['announcement_id']] ?? [];
                                    $employeeLegacyUrl = !empty($announcement['image_name'])
                                        ? '../controllers/announcement_image.php?id=' . (int) $announcement['announcement_id']
                                        : '';
                                    $employeeMainUrl = !empty($employeeGallery)
                                        ? '../controllers/announcement_image.php?image_id=' . (int) $employeeGallery[0]['image_id']
                                        : $employeeLegacyUrl;
                                    ?>
                                    <?php if ($employeeMainUrl !== ''): ?>
                                        <a class="announcement-banner" href="<?php echo $employeeMainUrl; ?>"
                                            target="_blank" rel="noopener"
                                            style="--ann-bg:url('<?php echo $employeeMainUrl; ?>');"
                                            title="Click to view full image">
                                            <img src="<?php echo $employeeMainUrl; ?>"
                                                alt="<?php echo esc($announcement['title']); ?>" loading="lazy">
                                            <?php if (count($employeeGallery) > 1): ?>
                                                <span class="announcement-photo-count"><i class="fa-solid fa-images"></i>
                                                    <?php echo count($employeeGallery); ?> photos</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (count($employeeGallery) > 1): ?>
                                        <div class="announcement-gallery">
                                            <?php foreach (array_slice($employeeGallery, 1) as $galleryImage): ?>
                                                <?php $employeeThumbUrl = '../controllers/announcement_image.php?image_id=' . (int) $galleryImage['image_id']; ?>
                                                <a href="<?php echo $employeeThumbUrl; ?>" target="_blank"
                                                    rel="noopener"
                                                    title="<?php echo esc($galleryImage['image_name'] ?? ''); ?>">
                                                    <img src="<?php echo $employeeThumbUrl; ?>"
                                                        alt="<?php echo esc($announcement['title']); ?>" loading="lazy">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <p><?php echo esc($announcement['body']); ?></p>

                                    <?php if (!empty($announcement['external_url']) || !empty($announcement['file_name'])): ?>
                                        <div class="announcement-actions">
                                            <?php if (!empty($announcement['external_url'])): ?>
                                                <a href="<?php echo esc($announcement['external_url']); ?>" target="_blank"
                                                    rel="noopener noreferrer" class="announcement-btn btn-link">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Link
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($announcement['file_name'])): ?>
                                                <a href="../controllers/download_announcement.php?id=<?php echo (int) $announcement['announcement_id']; ?>"
                                                    class="announcement-btn btn-file">
                                                    <i class="fa-solid fa-paperclip"></i>
                                                    <?php echo esc($announcement['file_name']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="embedded-view" class="employee-view embedded-view" aria-live="polite">
                    <iframe id="employee-view-frame" title="Employee portal content" src="about:blank"></iframe>
                </div>
            </section>

            <section id="profile-view" class="employee-view profile-view">

                <!-- Print Canvas Layout -->
                <div id="print-area" class="print-only">
                    <div class="print-header">
                        <h1>Employee Profile Sheet</h1>
                        <p>Tapuyan National High School</p>
                    </div>
                    <div class="print-section">
                        <h2>Personal Identity Information</h2>
                        <div class="print-grid">
                            <div class="print-item"><span>Full Name:</span>
                                <?php echo esc(format_name_upper($details['name'])); ?></div>
                            <div class="print-item"><span>Birthdate:</span>
                                <?php echo esc(format_date_pretty($details['birthdate'])); ?></div>
                            <div class="print-item"><span>Gender:</span>
                                <?php echo esc(format_capitalized($details['gender'])); ?></div>
                            <div class="print-item"><span>Civil Status:</span>
                                <?php echo esc(format_capitalized($details['marital_status'])); ?></div>
                            <div class="print-item"><span>Email Target:</span>
                                <?php echo esc(format_email($details['email_address'])); ?></div>
                            <div class="print-item"><span>Contact Line:</span>
                                <?php echo esc($details['contact_number']); ?></div>
                            <div class="print-item full"><span>Residence Address:</span>
                                <?php echo esc(format_capitalized($details['residential_address'])); ?></div>
                        </div>
                    </div>
                    <div class="print-section">
                        <h2>Organizational Appointment Data</h2>
                        <div class="print-grid">
                            <div class="print-item"><span>Assigned Position:</span>
                                <?php echo esc(format_capitalized($details['position'])); ?></div>
                            <div class="print-item"><span>Subject Domain:</span>
                                <?php echo esc(format_capitalized($details['subject_handled'])); ?></div>
                            <div class="print-item"><span>Base Salary:</span>
                                <?php echo esc(format_currency_php($details['basic_salary'])); ?></div>
                            <div class="print-item"><span>Salary Grade Level:</span>
                                <?php echo esc($details['salary_grade']); ?></div>
                            <div class="print-item"><span>Step Allocation:</span> <?php echo esc($details['step']); ?>
                            </div>
                            <div class="print-item"><span>Plantilla Number Key:</span>
                                <?php echo esc(format_capitalized($details['plantilla_number'])); ?></div>
                            <div class="print-item"><span>Original Deployment:</span>
                                <?php echo esc(format_date_pretty($details['original_appointment_date'])); ?></div>
                            <div class="print-item"><span>Latest Designation Date:</span>
                                <?php echo esc(format_date_pretty($details['latest_appointment_date'])); ?></div>
                            <div class="print-item full"><span>Academic Attainment:</span>
                                <?php echo esc(format_capitalized($details['educational_attainment'])); ?></div>
                            <div class="print-item full"><span>Board Eligibility:</span>
                                <?php echo esc(format_capitalized($details['eligibility'])); ?></div>
                            <div class="print-item full"><span>Historical Employer:</span>
                                <?php echo esc(format_capitalized($details['previous_employer'])); ?></div>
                        </div>
                    </div>
                </div>

                <form id="employee-form" action="../controllers/employeedatacontroller.php" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="EmployeeID" value="<?php echo esc($_SESSION['EmployeeID'] ?? ''); ?>">

                    <!-- Profile Card Left Panel -->
                    <aside class="profile-sidebar">
                        <div class="avatar-container">
                            <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview"
                                id="avatar-display">
                        </div>

                        <label for="avatar" class="avatar-upload-label" id="avatar-label" style="display:none;">
                            <i class="fa-solid fa-camera"></i> Change Avatar
                        </label>
                        <input id="avatar" name="avatar" type="file" accept="image/*" onchange="validateAvatar(this)">

                        <h1 class="profile-name"><?php echo esc(format_name_upper($details['name'])); ?></h1>
                        <p class="profile-position"><?php echo esc(format_capitalized($details['position'])); ?></p>
                        <span class="profile-id">ID: <?php echo esc($details['employee_number']); ?></span>

                        <input id="employee_number" name="employee_number" type="hidden"
                            value="<?php echo esc($details['employee_number']); ?>">
                    </aside>

                    <!-- Profile Details Right Panel -->
                    <section class="profile-details">
                        <div class="details-header">
                            <h1>Personnel Administration</h1>
                            <div class="header-buttons">
                                <a href="download_profile_pdf.php" class="btn btn-pdf"><i
                                        class="fa-solid fa-file-pdf"></i>
                                    Download PDF</a>
                                <button id="btn-edit" type="button" class="btn btn-primary"><i
                                        class="fa-solid fa-user-pen"></i> Edit Profile</button>
                                <button id="btn-save" type="submit" name="save" class="btn btn-success"><i
                                        class="fa-solid fa-cloud-arrow-up"></i> Save Updates</button>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="detail-card">
                            <h2><i class="fa-solid fa-id-card" style="color:var(--accent);"></i> Personal Information
                                Matrix
                            </h2>
                            <div class="card-content">
                                <div class="field">
                                    <label for="name">Full Identity Name *</label>
                                    <input id="name" name="name" type="text" required maxlength="150"
                                        placeholder="e.g. JUAN DELA CRUZ"
                                        value="<?php echo esc(format_name_upper($details['name'])); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="birthdate">Date of Birth</label>
                                    <input id="birthdate" name="birthdate" type="date"
                                        value="<?php echo esc($details['birthdate']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="gender">Gender Status</label>
                                    <select id="gender" name="gender" disabled>
                                        <option value="">-- Select --</option>
                                        <option value="Male" <?php if ($details['gender'] == 'Male')
                                            echo 'selected'; ?>>Male
                                        </option>
                                        <option value="Female" <?php if ($details['gender'] == 'Female')
                                            echo 'selected'; ?>>
                                            Female</option>
                                        <option value="Other" <?php if ($details['gender'] == 'Other')
                                            echo 'selected'; ?>>
                                            Other</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="marital_status">Civil Marital Status</label>
                                    <select id="marital_status" name="marital_status" disabled>
                                        <option value="">-- Select --</option>
                                        <option value="Single" <?php if ($details['marital_status'] == 'Single')
                                            echo 'selected'; ?>>Single</option>
                                        <option value="Married" <?php if ($details['marital_status'] == 'Married')
                                            echo 'selected'; ?>>Married</option>
                                        <option value="Widowed" <?php if ($details['marital_status'] == 'Widowed')
                                            echo 'selected'; ?>>Widowed</option>
                                        <option value="Separated" <?php if ($details['marital_status'] == 'Separated')
                                            echo 'selected'; ?>>Separated</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="email_address">Corporate Email Connection</label>
                                    <input id="email_address" name="email_address" type="email" maxlength="150"
                                        placeholder="name@example.com"
                                        value="<?php echo esc(format_email($details['email_address'])); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="contact_number">Primary Mobile Contact Line</label>
                                    <input id="contact_number" name="contact_number" type="text" maxlength="20"
                                        placeholder="09xxxxxxxxx" value="<?php echo esc($details['contact_number']); ?>"
                                        readonly>
                                </div>
                                <div class="field full-width">
                                    <label for="residential_address">Primary Residential Location Address</label>
                                    <textarea id="residential_address" name="residential_address" rows="2"
                                        placeholder="Street, Barangay, Municipality, City"
                                        readonly><?php echo esc(format_capitalized($details['residential_address'])); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details -->
                        <div class="detail-card">
                            <h2><i class="fa-solid fa-briefcase" style="color:var(--danger);"></i> Institutional
                                Employment
                                Details</h2>
                            <div class="card-content">
                                <div class="field">
                                    <label for="position">Designated Functional Position</label>
                                    <input id="position" name="position" type="text" maxlength="100"
                                        placeholder="Teacher I"
                                        value="<?php echo esc(format_capitalized($details['position'])); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="subject_handled">Instructional Subject Domain Handled</label>
                                    <input id="subject_handled" name="subject_handled" type="text" maxlength="150"
                                        placeholder="Mathematics Department"
                                        value="<?php echo esc(format_capitalized($details['subject_handled'])); ?>"
                                        readonly>
                                </div>
                                <div class="field">
                                    <label for="basic_salary_display">Active Monthly Basic Salary</label>
                                    <input id="basic_salary_display" type="text" placeholder="0.00"
                                        value="<?php echo $details['basic_salary'] !== '' ? esc(number_format((float) $details['basic_salary'], 2)) : ''; ?>"
                                        readonly>
                                    <input id="basic_salary" name="basic_salary" type="hidden"
                                        value="<?php echo esc($details['basic_salary']); ?>">
                                </div>
                                <div class="field">
                                    <label for="salary_grade">Salary Grade Level</label>
                                    <input id="salary_grade" name="salary_grade" type="text" maxlength="10"
                                        placeholder="11" value="<?php echo esc($details['salary_grade']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="step">Step Incremental Key</label>
                                    <input id="step" name="step" type="text" maxlength="10" placeholder="1"
                                        value="<?php echo esc($details['step']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="plantilla_number">Item Plantilla Reference Number</label>
                                    <input id="plantilla_number" name="plantilla_number" type="text" maxlength="100"
                                        value="<?php echo esc(format_capitalized($details['plantilla_number'])); ?>"
                                        readonly>
                                </div>
                                <div class="field">
                                    <label for="original_appointment_date">Original Date of Commission
                                        Appointment</label>
                                    <input id="original_appointment_date" name="original_appointment_date" type="date"
                                        value="<?php echo esc($details['original_appointment_date']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="latest_appointment_date">Latest Appointment Progression Date</label>
                                    <input id="latest_appointment_date" name="latest_appointment_date" type="date"
                                        value="<?php echo esc($details['latest_appointment_date']); ?>" readonly>
                                </div>
                                <div class="field full-width">
                                    <label for="educational_attainment">Academic/Educational Attainment
                                        Background</label>
                                    <textarea id="educational_attainment" name="educational_attainment" rows="2"
                                        readonly><?php echo esc(format_capitalized($details['educational_attainment'])); ?></textarea>
                                </div>
                                <div class="field full-width">
                                    <label for="eligibility">Professional PRC/Civil Service Eligibility</label>
                                    <textarea id="eligibility" name="eligibility" rows="2"
                                        readonly><?php echo esc(format_capitalized($details['eligibility'])); ?></textarea>
                                </div>
                                <div class="field full-width">
                                    <label for="previous_employer">Prior Historic Institutional Employer</label>
                                    <input id="previous_employer" name="previous_employer" type="text" maxlength="150"
                                        value="<?php echo esc(format_capitalized($details['previous_employer'])); ?>"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <!-- ID & Mandates -->
                        <div class="detail-card">
                            <h2><i class="fa-solid fa-file-shield" style="color:var(--success);"></i> IDs & Licenses
                            </h2>
                            <div class="card-content">
                                <div class="field">
                                    <label for="gsis_number">GSIS Policy Number</label>
                                    <input id="gsis_number" name="gsis_number" type="text" maxlength="50"
                                        value="<?php echo esc($details['gsis_number']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="philhealth_number">PhilHealth Identification Key</label>
                                    <input id="philhealth_number" name="philhealth_number" type="text" maxlength="50"
                                        value="<?php echo esc($details['philhealth_number']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="pagibig_number">Pag-IBIG MID Core Number</label>
                                    <input id="pagibig_number" name="pagibig_number" type="text" maxlength="50"
                                        value="<?php echo esc($details['pagibig_number']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="tin_number">TIN Revenue Tax Reference</label>
                                    <input id="tin_number" name="tin_number" type="text" maxlength="50"
                                        value="<?php echo esc($details['tin_number']); ?>" readonly>
                                </div>
                                <div class="field">
                                    <label for="prc_license">PRC Professional Registry License</label>
                                    <input id="prc_license" name="prc_license" type="text" maxlength="100"
                                        value="<?php echo esc(strtoupper(trim((string) $details['prc_license']))); ?>"
                                        readonly>
                                </div>
                                <div class="field">
                                    <label for="expiration_date">PRC ID Validity Expiration Date</label>
                                    <input id="expiration_date" name="expiration_date" type="date"
                                        value="<?php echo esc($details['expiration_date']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>
            </section>
        </main>
    </div>

    <script>
        const employeeViews = document.querySelectorAll('.employee-view');
        const employeeFrame = document.getElementById('employee-view-frame');
        const employeeNavItems = document.querySelectorAll('[data-employee-view]');
        const profileView = document.getElementById('profile-view');
        const announcementView = document.getElementById('announcement-view');
        const embeddedView = document.getElementById('embedded-view');
        const employeeContentShell = document.getElementById('employee-content-shell');
        const employeeWelcome = document.getElementById('employee-welcome');

        function showEmployeeView(view) {
            employeeViews.forEach(item => item.classList.remove('active'));
            employeeNavItems.forEach(item => item.classList.remove('active'));
            employeeWelcome.hidden = view !== 'announcements.php';
            employeeContentShell.style.display = view === 'profile' ? 'none' : 'block';

            const selectedNav = document.querySelector(`[data-employee-view="${view}"]`);
            if (selectedNav) selectedNav.classList.add('active');

            if (view === 'profile') {
                profileView.classList.add('active');
                return;
            }

            if (view === 'announcements.php') {
                announcementView.classList.add('active');
                return;
            }

            embeddedView.classList.add('active');
            employeeFrame.src = view;
        }

        employeeNavItems.forEach(item => {
            item.addEventListener('click', function (event) {
                const view = this.dataset.employeeView;
                if (!view) return;
                event.preventDefault();
                showEmployeeView(view);
                window.history.replaceState({}, document.title, `home.php?view=${encodeURIComponent(view)}`);

                const sidebar = document.getElementById('appSidebar');
                if (sidebar) sidebar.classList.remove('show');
            });
        });

        const initialView = new URLSearchParams(window.location.search).get('view') || 'announcements.php';
        showEmployeeView(initialView);

        const btnEdit = document.getElementById('btn-edit');
        const btnSave = document.getElementById('btn-save');
        const form = document.getElementById('employee-form');
        const toast = document.getElementById('toast');
        const avatarLabel = document.getElementById('avatar-label');

        function showToast(message, type = 'success') {
            if (!toast) return;
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            setTimeout(() => { toast.className = 'toast'; }, 3000);
        }

        function validateAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    showToast('File size limit exceeded! Max allowed size is 5MB.', 'error');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('avatar-display').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        function formatSalaryInput(value) {
            value = value.replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            const integerPart = parts[0] || '';
            const decimalPart = parts[1] || '';
            const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            if (parts.length > 1) {
                return formattedInteger + '.' + decimalPart.slice(0, 2);
            }
            return formattedInteger;
        }

        function syncSalaryRawValue() {
            const displayInput = document.getElementById('basic_salary_display');
            const hiddenInput = document.getElementById('basic_salary');
            if (!displayInput || !hiddenInput) return;
            hiddenInput.value = displayInput.value.replace(/,/g, '');
        }

        if (btnEdit && form) {
            btnEdit.addEventListener('click', function () {
                const readOnlyFields = form.querySelectorAll('input[readonly], textarea[readonly]');
                readOnlyFields.forEach(field => {
                    if (field.id !== 'employee_number') {
                        field.readOnly = false;
                    }
                });

                const disabledFields = form.querySelectorAll('select[disabled]');
                disabledFields.forEach(field => {
                    field.disabled = false;
                });

                if (avatarLabel) avatarLabel.style.display = 'inline-flex';

                const salaryDisplay = document.getElementById('basic_salary_display');
                if (salaryDisplay) { salaryDisplay.readOnly = false; }
                if (btnSave) { btnSave.style.display = 'inline-flex'; }
                this.style.display = 'none';

                const firstField = document.getElementById('name');
                if (firstField) { firstField.focus(); }
            });
        }

        const salaryDisplay = document.getElementById('basic_salary_display');
        if (salaryDisplay) {
            salaryDisplay.addEventListener('input', function () {
                this.value = formatSalaryInput(this.value);
                syncSalaryRawValue();
            });

            salaryDisplay.addEventListener('blur', function () {
                const rawValue = this.value.replace(/,/g, '');
                if (rawValue !== '' && !isNaN(rawValue)) {
                    this.value = Number(rawValue).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    syncSalaryRawValue();
                }
            });
        }

        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const message = params.get('message');
            if (status && message) {
                showToast(message, status);
                const url = new URL(window.location);
                url.searchParams.delete('status');
                url.searchParams.delete('message');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        })();

    </script>
</body>

</html>