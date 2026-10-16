<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    if (isset($_POST['file_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit();
    }
    header("Location: ../index.php");
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];

// 1. Handle JSON single-file delete from the new multi-file table
if (isset($_POST['file_id'])) {
    header('Content-Type: application/json');
    $file_id = (int) $_POST['file_id'];

    if ($file_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid file ID.']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM employee_document_files WHERE file_id = ? AND EmployeeID = ?");
    $stmt->bind_param("ii", $file_id, $EmployeeID);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();

    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'File not found or access denied.']);
    }
    exit();
}

// 2. Handle legacy form submission (clears legacy single-column value)
$file_col = trim($_POST['file'] ?? '');
$allowed = ['prc_file', 'file_leave', 'dll_file', 'saln_file', 'ipcr_file', 'diploma_file', 'tor_file', 'itr_file', 'itr_file_2', 'service_record_file', 'appointment_file', 'trainings_awards_file', 'pds_file', 'ICS'];

if (!in_array($file_col, $allowed)) {
    header("Location: documents.php?status=error&message=" . urlencode("Invalid document category."));
    exit();
}

$stmt = $conn->prepare("UPDATE employee_documents SET `$file_col` = NULL WHERE EmployeeID = ?");
$stmt->bind_param("i", $EmployeeID);
$stmt->execute();
$stmt->close();

header("Location: documents.php?status=success&message=" . urlencode("Legacy file removed."));
exit();