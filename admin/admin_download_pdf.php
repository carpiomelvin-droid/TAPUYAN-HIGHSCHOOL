<?php
session_start();
include_once '../db/conn.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure admin is logged in (Adjust session key if yours differs)
if (!isset($_SESSION['username'])) {
    die("Access denied. Please log in as an administrator.");
}

// Get targeted Employee ID from URL query parameters
$EmployeeID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($EmployeeID <= 0) {
    die("Error: No valid employee ID specified.");
}

// Initializing the safe structural details default matrix
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
    'educational_attainment' => '',
    'eligibility' => '',
    'avatar_image' => '',
    'avatar_mime_type' => ''
];

// 1. Fetch Employee login identifier number from the 'admin' accounts root table
$sql_admin = "SELECT Employeenumber FROM admin WHERE EmployeeID = ? LIMIT 1";
$stmt_admin = $conn->prepare($sql_admin);
if ($stmt_admin) {
    $stmt_admin->bind_param("i", $EmployeeID);
    $stmt_admin->execute();
    $stmt_admin->bind_result($employeenumber);
    $stmt_admin->fetch();
    $stmt_admin->close();
    $details['employee_number'] = $employeenumber ?? 'N/A';
}

// 2. Fetch Personal and Profile metrics via general row extraction query
$sql_details = "SELECT * FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_details = $conn->prepare($sql_details);
if ($stmt_details) {
    $stmt_details->bind_param("i", $EmployeeID);
    $stmt_details->execute();
    $result = $stmt_details->get_result();

    if ($result->num_rows > 0) {
        $db_data = $result->fetch_assoc();
        $details = array_merge($details, $db_data);
    }
    $stmt_details->close();
}

// Text and String sanitizer utility function
function e($value)
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
        return 'N/A';
    return ucwords(strtolower($value));
}

function format_date_pretty($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00')
        return 'N/A';
    $timestamp = strtotime($value);
    return ($timestamp === false) ? e($value) : date('F j, Y', $timestamp);
}

function format_currency_php($value)
{
    if ($value === null || $value === '')
        return 'N/A';
    if (!is_numeric($value))
        return e($value);
    return '&#8369;' . number_format((float) $value, 2);
}

// Calculate active duration runtime length of service metrics dynamically
$length_of_service = 'N/A';
if (!empty($details['original_appointment_date']) && $details['original_appointment_date'] !== '0000-00-00') {
    $startDate = new DateTime($details['original_appointment_date']);
    $endDate = new DateTime('2026-07-11'); // Synchronized setup marker point
    $interval = $startDate->diff($endDate);
    if ($interval->y > 0) {
        $length_of_service = $interval->y . ($interval->y == 1 ? ' Year' : ' Years');
    } else {
        $length_of_service = $interval->m . ($interval->m == 1 ? ' Month' : ' Months');
    }
}

// Base64 conversion framework loading for standard assets
$logoPath = realpath('../src/images/logo.png');
$logoHtml = '';
if ($logoPath && file_exists($logoPath)) {
    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoHtml = '<img src="data:image/' . $logoType . ';base64,' . $logoData . '" alt="School Logo" class="logo">';
}

// Profile photo engine construction parsing BLOB binaries
$avatarHtml = '<div class="photo-placeholder">4x4 Photo</div>';
if (!empty($details['avatar_image'])) {
    $mimeType = !empty($details['avatar_mime_type']) ? $details['avatar_mime_type'] : 'image/jpeg';
    $avatarData = base64_encode($details['avatar_image']);
    $avatarHtml = '<img src="data:' . $mimeType . ';base64,' . $avatarData . '" class="avatar-img">';
}

$fontPath = realpath('/Employee/UnifrakturMaguntia-Regular.ttf');

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employee Information Profile Sheet</title>
    <style>
        @font-face {
            font-family: "UnifrakturMaguntia";
            src: url("' . $fontPath . '") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @page {
            margin: 20px;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        .page-border {
            border: 2px solid #222;
            padding: 30px 25px;
            min-height: 94%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .header-table td {
            padding: 0;
            vertical-align: top;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 6px;
        }

        .logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .header-text-center {
            text-align: center;
        }

        .republic-text {
            font-size: 12px;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
        }

        .dept-text {
            font-family: "UnifrakturMaguntia", serif;
            font-size: 24px;
            margin: 0 0 4px 0;
            font-weight: normal;
        }

        .region-text {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #222;
        }

        .school-info-text {
            font-size: 10.5px;
            margin: 1px 0;
            color: #333;
            text-transform: uppercase;
        }

        .photo-box-cell {
            width: 120px;
            text-align: right;
        }

        .photo-frame {
            width: 160px;
            height: 160px;
            border: 1px solid #444;
            background-color: #f7f9fa;
            text-align: center;
            overflow: hidden;
        }

        .photo-placeholder {
            font-size: 11px;
            color: #777;
            line-height: 115px;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .profile-table td {
            padding: 6px 0;
            vertical-align: bottom;
            font-size: 12px;
        }

        .field-label {
            width: 28%;
            font-weight: bold;
            color: #222;
        }

        .field-value {
            width: 72%;
            border-bottom: 1px solid #000;
            padding-left: 5px;
            font-weight: normal;
        }
    </style>
</head>
<body>

<div class="page-border">

    <table class="header-table">
        <tr>
            <td style="width: 120px;"></td>
            <td class="header-text-center">
                <div class="logo-container">' . $logoHtml . '</div>
                <p class="republic-text">Republic of the Philippines</p>
                <h1 class="dept-text">Department of Education</h1>
                <p class="region-text">MIMAROPA REGION</p>
                <div class="school-info-text">Schools Division of Marinduque</div>
                <div class="school-info-text">Gasan District</div>
                <div class="school-info-text" style="font-weight: bold;">Tapuyan National High School</div>
                <div class="school-info-text" style="font-size: 9.5px;">Tapuyan, Gasan, Marinduque</div>
            </td>
            <td class="photo-box-cell">
                <div class="photo-frame">
                    ' . $avatarHtml . '
                </div>
            </td>
        </tr>
    </table>

    <table class="profile-table">
        <tr>
            <td class="field-label">Name:</td>
            <td class="field-value">' . e(format_name_upper($details['name'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Employee No.:</td>
            <td class="field-value">' . e($details['employee_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">License Number:</td>
            <td class="field-value">' . e(strtoupper((string) $details['prc_license'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Plantilla Number:</td>
            <td class="field-value">' . e($details['plantilla_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">PAG-IBIG Number:</td>
            <td class="field-value">' . e($details['pagibig_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">GSIS Number:</td>
            <td class="field-value">' . e($details['gsis_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">Philhealth:</td>
            <td class="field-value">' . e($details['philhealth_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">Tin Number:</td>
            <td class="field-value">' . e($details['tin_number']) . '</td>
        </tr>
        <tr>
            <td class="field-label">Email Address:</td>
            <td class="field-value">' . e(strtolower((string) $details['email_address'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Birthday:</td>
            <td class="field-value">' . e(format_date_pretty($details['birthdate'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Date of Employment:</td>
            <td class="field-value">' . e(format_date_pretty($details['original_appointment_date'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Length of Service:</td>
            <td class="field-value">' . e($length_of_service) . '</td>
        </tr>
        <tr>
            <td class="field-label">Designation:</td>
            <td class="field-value">' . e(format_capitalized($details['position'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Grade Assignment:</td>
            <td class="field-value">' . e(format_capitalized($details['subject_handled'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Educational Attainment:</td>
            <td class="field-value">' . e(format_capitalized($details['educational_attainment'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Eligibility:</td>
            <td class="field-value">' . e(format_capitalized($details['eligibility'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Address:</td>
            <td class="field-value">' . e(format_capitalized($details['residential_address'])) . '</td>
        </tr>
        <tr>
            <td class="field-label">Salary Grade and Step:</td>
            <td class="field-value">' . (!empty($details['salary_grade']) ? 'SG ' . e($details['salary_grade']) : 'N/A') . (!empty($details['step']) ? ' / Step ' . e($details['step']) : '') . '</td>
        </tr>
        <tr>
            <td class="field-label">Basic Salary:</td>
            <td class="field-value">' . format_currency_php($details['basic_salary']) . '</td>
        </tr>
        <tr>
            <td class="field-label">Cellphone Number:</td>
            <td class="field-value">' . e($details['contact_number']) . '</td>
        </tr>
    </table>

</div>

</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$fileName = 'Employee_Profile_' . preg_replace('/[^A-Za-z0-9_-]/', '_', format_name_upper($details['name']) ?: 'Employee') . '.pdf';
$dompdf->stream($fileName, ['Attachment' => true]);
exit();