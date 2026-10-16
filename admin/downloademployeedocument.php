<?php
session_start();
include_once '../db/conn.php';

// --- 1. Security Check: Ensure admin is logged in ---
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'admin')) {
    die("Access Denied.");
}

// --- 2. Get and Validate Parameters ---
$file_id = isset($_GET['file_id']) ? (int) $_GET['file_id'] : 0;
$action = isset($_GET['action']) && $_GET['action'] === 'view' ? 'view' : 'download';

if ($file_id <= 0) {
    die("Error: Invalid file request.");
}

// --- 3. Fetch File Record and Employee Name ---
// Note: Assumes your BLOB column in 'employee_document_files' is named 'file_data'
// (or 'file_blob' depending on your schema setup). Adjust the column name below if needed.
$sql = "SELECT
            f.file_name,
            f.doc_column,
            f.file_data,
            e.name AS employee_name
        FROM employee_document_files AS f
        LEFT JOIN employee_details AS e ON f.EmployeeID = e.EmployeeID
        WHERE f.file_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();
$file_record = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$file_record || empty($file_record['file_data'])) {
    die("Error: File not found or empty in database archive.");
}

$file_blob = $file_record['file_data'];
$original_name = mb_convert_encoding($file_record['file_name'], 'UTF-8', 'UTF-8');
$doc_col = $file_record['doc_column'];
$emp_name = $file_record['employee_name'] ?? 'Employee';

// --- 4. Sanitize and Format Filename ---
// Clean up employee name (e.g., "Juan Dela Cruz" -> "Juan_Dela_Cruz")
$clean_emp_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $emp_name);
$clean_doc_name = str_replace('_file', '', $doc_col);

// If the original filename exists, attach it; otherwise, generate a standard name
if (!empty($original_name)) {
    $filename = $clean_emp_name . '_' . $clean_doc_name . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $original_name);
} else {
    $filename = $clean_emp_name . '_' . $clean_doc_name . '.bin';
}

// --- 5. Determine Accurate MIME Type ---
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($file_blob);

// Fallback if finfo fails to return a valid MIME type
if (empty($mime_type)) {
    $mime_type = 'application/octet-stream';
}

// Ensure proper extension exists if filename lacked one
if (pathinfo($filename, PATHINFO_EXTENSION) === '') {
    if (strpos($mime_type, 'pdf') !== false) {
        $filename .= '.pdf';
    } elseif (strpos($mime_type, 'jpeg') !== false) {
        $filename .= '.jpg';
    } elseif (strpos($mime_type, 'png') !== false) {
        $filename .= '.png';
    } elseif (strpos($mime_type, 'gif') !== false) {
        $filename .= '.gif';
    } elseif (strpos($mime_type, 'word') !== false || strpos($mime_type, 'officedocument') !== false) {
        $filename .= '.docx';
    } else {
        $filename .= '.bin';
    }
}

// --- 6. Send HTTP Headers and Output Blob ---
// Clear any output buffers to prevent corrupting binary files
if (ob_get_level()) {
    ob_end_clean();
}

header("Content-Type: " . $mime_type);
header("Content-Length: " . strlen($file_blob));
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

// Switch between browser inline display (view) or attachment (download)
if ($action === 'view') {
    header("Content-Disposition: inline; filename=\"" . $filename . "\"");
} else {
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
}

// Output file binary stream
echo $file_blob;
exit;
?>