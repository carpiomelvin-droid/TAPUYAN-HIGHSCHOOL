<?php
session_start();
include_once '../db/conn.php';
/** @var mysqli $conn */

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$back = '../admin/leave_admin.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $back);
    exit();
}

$employeeId = (int) ($_POST['employee_id'] ?? 0);
$leaveType = $_POST['leave_type'] ?? 'vacation';
$documentType = trim($_POST['document_type'] ?? '');
$referenceNumber = trim($_POST['reference_number'] ?? '');
$start = $_POST['period_start'] ?? '';
$end = $_POST['period_end'] ?? '';
$status = $_POST['status'] ?? 'pending';
$remarks = trim($_POST['remarks'] ?? '');
$allowedTypes = ['vacation', 'sick', 'other'];
$allowedStatuses = ['pending', 'approved', 'rejected'];
$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

function fail_upload(string $message): void
{
    header('Location: ../admin/leave_admin.php?status=error&message=' . urlencode($message));
    exit();
}

if ($employeeId <= 0 || $documentType === '' || !$start || !$end || !in_array($leaveType, $allowedTypes, true) || !in_array($status, $allowedStatuses, true)) {
    fail_upload('Complete all required leave fields.');
}
$startDate = DateTime::createFromFormat('Y-m-d', $start);
$endDate = DateTime::createFromFormat('Y-m-d', $end);
if (!$startDate || $startDate->format('Y-m-d') !== $start || !$endDate || $endDate->format('Y-m-d') !== $end) {
    fail_upload('Enter valid leave period dates.');
}
if ($endDate < $startDate) {
    fail_upload('Leave period end cannot be earlier than the start date.');
}
if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
    fail_upload('Select a leave document before uploading.');
}
$file = $_FILES['attachment'];
if ($file['size'] > 10 * 1024 * 1024) {
    fail_upload('The selected file exceeds the 10 MB limit.');
}
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions, true)) {
    fail_upload('Select a PDF, DOC, DOCX, JPG, or PNG file.');
}

$days = (float) $startDate->diff($endDate)->days + 1;
$year = (int) $startDate->format('Y');
$fileData = file_get_contents($file['tmp_name']);
if ($fileData === false || $fileData === '') {
    fail_upload('The selected leave document could not be read. Please choose the file again.');
}
$mimeType = mime_content_type($file['tmp_name']) ?: ($file['type'] ?: 'application/octet-stream');

$conn->begin_transaction();
try {
    $employeeStmt = $conn->prepare("SELECT EmployeeID FROM admin WHERE EmployeeID = ? AND role = 'staff' LIMIT 1");
    if (!$employeeStmt) {
        throw new Exception('Unable to validate the selected employee.');
    }
    $employeeStmt->bind_param('i', $employeeId);
    $employeeStmt->execute();
    if (!$employeeStmt->get_result()->fetch_assoc()) {
        throw new Exception('The selected employee was not found.');
    }
    $employeeStmt->close();

    $creditStmt = $conn->prepare("INSERT INTO employee_leave_credits (EmployeeID, leave_year) VALUES (?, ?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP");
    if (!$creditStmt) {
        throw new Exception('Unable to initialize the employee leave credits.');
    }
    $creditStmt->bind_param('ii', $employeeId, $year);
    $creditStmt->execute();
    $creditStmt->close();

    $sql = "INSERT INTO leave_records (EmployeeID, uploaded_by, leave_year, leave_type, document_type, reference_number, period_start, period_end, days, status, remarks, file_name, file_data, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Unable to prepare the leave document record.');
    }
    $uploader = (int) $_SESSION['EmployeeID'];
    $null = null;
    // Parameter 13 is the BLOB. Bind it as binary data, then stream its contents.
    $stmt->bind_param('iiisssssdsssbs', $employeeId, $uploader, $year, $leaveType, $documentType, $referenceNumber, $start, $end, $days, $status, $remarks, $file['name'], $null, $mimeType);
    if (!$stmt->send_long_data(12, $fileData)) {
        throw new Exception('Unable to stream the leave document: ' . $stmt->error);
    }
    if (!$stmt->execute()) {
        throw new Exception('Unable to save the leave document: ' . $stmt->error);
    }
    $stmt->close();
    $conn->commit();
    header('Location: ' . $back . '?status=success&message=' . urlencode('Leave created successfully. The employee can now view the file and status.'));
} catch (Throwable $e) {
    $conn->rollback();
    header('Location: ' . $back . '?status=error&message=' . urlencode('Upload failed: ' . $e->getMessage()));
}
exit();
