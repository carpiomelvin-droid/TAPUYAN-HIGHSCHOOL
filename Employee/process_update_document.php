<?php
session_start();
header('Content-Type: application/json');
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$EmployeeID = (int) $_SESSION['EmployeeID'];
$file_column = trim($_POST['file_column'] ?? '');

if (empty($file_column)) {
    echo json_encode(['success' => false, 'message' => 'No document category specified.']);
    exit();
}

// Ensure the category is valid
$allowedColumns = [
    'dll_file',
    'prc_file',
    'saln_file',
    'ipcr_file',
    'diploma_file',
    'tor_file',
    'itr_file',
    'itr_file_2',
    'service_record_file',
    'appointment_file',
    'trainings_awards_file',
    'pds_file',
    'ICS'
];

if (!in_array($file_column, $allowedColumns)) {
    echo json_encode(['success' => false, 'message' => 'Invalid document category.']);
    exit();
}

// 🟢 Reformat HTML array files (new_files[]) into an iterable array
$uploadedFiles = [];
if (isset($_FILES['new_files'])) {
    $files = $_FILES['new_files'];
    if (is_array($files['name'])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $uploadedFiles[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'size' => $files['size'][$i]
                ];
            }
        }
    } elseif ($files['error'] === UPLOAD_ERR_OK) { // 🟢 FIXED: Changed illegal 'else elseif' to 'elseif'
        $uploadedFiles[] = $files;
    }
}

if (empty($uploadedFiles)) {
    echo json_encode(['success' => false, 'message' => 'No valid files were received. Please try again.']);
    exit();
}

$successCount = 0;
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO employee_document_files (EmployeeID, doc_column, file_name, file_data, mime_type, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    foreach ($uploadedFiles as $file) {
        // Limit individual file size to 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            continue;
        }

        $fileName = $file['name'];
        $mimeType = !empty($file['type']) ? $file['type'] : 'application/octet-stream';
        $fileData = file_get_contents($file['tmp_name']);

        $null = null;
        $stmt->bind_param("issbs", $EmployeeID, $file_column, $fileName, $null, $mimeType);
        $stmt->send_long_data(3, $fileData);

        if ($stmt->execute()) {
            $successCount++;
        }
    }
    $stmt->close();
    $conn->commit();

    if ($successCount > 0) {
        echo json_encode(['success' => true, 'message' => "Successfully uploaded $successCount file(s)! Old records were preserved."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload files. Ensure files are under 5MB.']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}