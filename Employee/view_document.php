<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    die("Access denied.");
}

$EmployeeID = (int) $_SESSION['EmployeeID'];
$file_id = isset($_GET['file_id']) ? (int) $_GET['file_id'] : 0;
$legacy_col = isset($_GET['file']) ? trim($_GET['file']) : '';

if ($file_id > 0) {
    // 🟢 View specific file from the new multi-file table
    $stmt = $conn->prepare("SELECT file_name, file_data, mime_type FROM employee_document_files WHERE file_id = ? AND EmployeeID = ? LIMIT 1");
    $stmt->bind_param("ii", $file_id, $EmployeeID);
    $stmt->execute();
    $file = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$file || empty($file['file_data'])) {
        die("File content is empty or not found.");
    }

    $fileName = $file['file_name'];
    $mimeType = !empty($file['mime_type']) ? $file['mime_type'] : 'application/octet-stream';

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . strlen($file['file_data']));
    echo $file['file_data'];
    exit();
} elseif (!empty($legacy_col)) {
    // 🟢 View legacy single-column file for backward compatibility
    $allowed = ['prc_file', 'file_leave', 'dll_file', 'saln_file', 'ipcr_file', 'diploma_file', 'tor_file', 'itr_file', 'itr_file_2', 'service_record_file', 'appointment_file', 'trainings_awards_file', 'pds_file', 'ICS'];
    if (!in_array($legacy_col, $allowed))
        die("Invalid column.");

    $stmt = $conn->prepare("SELECT `$legacy_col` FROM employee_documents WHERE EmployeeID = ? LIMIT 1");
    $stmt->bind_param("i", $EmployeeID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || empty($row[$legacy_col]))
        die("File not found.");

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Document_' . $legacy_col . '.pdf"');
    echo $row[$legacy_col];
    exit();
} else {
    die("Invalid file request.");
}