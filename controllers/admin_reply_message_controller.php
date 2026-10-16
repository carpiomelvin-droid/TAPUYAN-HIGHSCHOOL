<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Invalid request."));
    exit();
}

$sender_id = (int) $_SESSION['EmployeeID'];
$sender_role = 'admin';

$receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$receiver_role = 'employee';

$subject = trim($_POST['subject'] ?? '');
$message_text = trim($_POST['message_text'] ?? '');
$original_message_id = isset($_POST['original_message_id']) ? (int) $_POST['original_message_id'] : 0;

if ($receiver_id <= 0 || $subject === '' || $message_text === '') {
    header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Please complete all required fields."));
    exit();
}

/* Validate receiver exists in employee_details */
$checkEmployee = $conn->prepare("SELECT EmployeeID FROM employee_details WHERE EmployeeID = ? LIMIT 1");
$checkEmployee->bind_param("i", $receiver_id);
$checkEmployee->execute();
$employeeResult = $checkEmployee->get_result();
$employeeExists = $employeeResult->fetch_assoc();
$checkEmployee->close();

if (!$employeeExists) {
    header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Employee not found."));
    exit();
}

/* Insert reply message */
$sql = "INSERT INTO messages (
            sender_id,
            sender_role,
            receiver_id,
            receiver_role,
            subject,
            message_text,
            sender_deleted,
            receiver_deleted
        ) VALUES (?, ?, ?, ?, ?, ?, 0, 0)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Prepare failed: " . $conn->error));
    exit();
}

$stmt->bind_param("isisss", $sender_id, $sender_role, $receiver_id, $receiver_role, $subject, $message_text);

if (!$stmt->execute()) {
    $stmt->close();
    header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Reply send failed: " . $stmt->error));
    exit();
}

$message_id = $stmt->insert_id;
$stmt->close();

/* Optional attachment */
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Attachment upload failed."));
        exit();
    }

    $fileName = $_FILES['attachment']['name'];
    $tmpName = $_FILES['attachment']['tmp_name'];
    $fileSize = $_FILES['attachment']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileSize > 5 * 1024 * 1024) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Attachment is too large. Maximum is 5MB."));
        exit();
    }

    $allowedRules = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp']
    ];

    if (!array_key_exists($fileExt, $allowedRules)) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Invalid attachment type."));
        exit();
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedRules[$fileExt], true)) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Invalid attachment MIME type."));
        exit();
    }

    $fileData = file_get_contents($tmpName);
    if ($fileData === false) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Failed to read attachment."));
        exit();
    }

    $sqlFile = "INSERT INTO message_files (message_id, file_name, file_data, mime_type)
                VALUES (?, ?, ?, ?)";

    $stmtFile = $conn->prepare($sqlFile);

    if (!$stmtFile) {
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Attachment prepare failed: " . $conn->error));
        exit();
    }

    $null = null;
    $stmtFile->bind_param("ibss", $message_id, $fileName, $fileData, $mimeType);


    if (!$stmtFile->execute()) {
        $stmtFile->close();
        header("Location: ../admin/admin_messages.php?status=error&message=" . urlencode("Attachment save failed: " . $stmtFile->error));
        exit();
    }

    $stmtFile->close();
}

$conn->close();

if ($original_message_id > 0) {
    header("Location: ../admin/view_admin_message.php?id=" . $original_message_id . "&status=success&message=" . urlencode("Reply sent successfully."));
    exit();
}

header("Location: ../admin/admin_messages.php?status=success&message=" . urlencode("Reply sent successfully."));
exit();