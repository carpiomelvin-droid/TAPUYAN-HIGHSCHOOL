<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

// Automatically detect the page they came from to redirect them back with alerts
$referer = isset($_SERVER['HTTP_REFERER']) ? strtok($_SERVER['HTTP_REFERER'], '?') : 'employee_send_message.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = (int) $_SESSION['EmployeeID'];

    // 🟢 Capture the array of multiple recipients
    $receiver_ids = isset($_POST['receiver_ids']) ? $_POST['receiver_ids'] : [];
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message_text'] ?? '');

    // 🟢 Validation: Ensure at least one recipient is selected, and fields aren't empty
    if (empty($receiver_ids) || empty($subject) || empty($message_text)) {
        header("Location: " . $referer . "?status=error&message=" . urlencode("Please complete all required fields."));
        exit();
    }

    // --- ATTACHMENT PROCESSING ---
    $has_file = false;
    $file_name = '';
    $file_type = '';
    $file_data = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $has_file = true;
        $file_name = $_FILES['attachment']['name'];
        $file_type = $_FILES['attachment']['type'];
        $file_data = file_get_contents($_FILES['attachment']['tmp_name']);
    }

    // Use a transaction to ensure either ALL messages are sent successfully, or none are (rollback on fail)
    $conn->begin_transaction();

    try {
        foreach ($receiver_ids as $r_id) {
            $receiver_id = (int) $r_id;

            // 🟢 Get recipient's role dynamically from the database
            $roleSql = "SELECT role FROM admin WHERE EmployeeID = ? LIMIT 1";
            $roleStmt = $conn->prepare($roleSql);
            $roleStmt->bind_param("i", $receiver_id);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $recipient = $roleResult->fetch_assoc();
            $roleStmt->close();

            // Set role structure correctly
            $receiver_role = 'employee';
            if ($recipient && $recipient['role'] === 'superadmin') {
                $receiver_role = 'admin';
            }

            // Insert into messages table
            $insertMsgSql = "INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, subject, message_text, sent_at)
                             VALUES (?, 'employee', ?, ?, ?, ?, NOW())";
            $msgStmt = $conn->prepare($insertMsgSql);
            if (!$msgStmt) {
                throw new Exception($conn->error);
            }
            $msgStmt->bind_param("iisss", $sender_id, $receiver_id, $receiver_role, $subject, $message_text);
            $msgStmt->execute();

            $message_id = $conn->insert_id;
            $msgStmt->close();

            // If an attachment is included, insert it for this message ID
            if ($has_file) {
                $insertFileSql = "INSERT INTO message_files (message_id, file_name, file_data, mime_type) VALUES (?, ?, ?, ?)";
                $fileStmt = $conn->prepare($insertFileSql);
                if (!$fileStmt) {
                    throw new Exception($conn->error);
                }
                $null = null; // Placeholder for send_long_data
                $fileStmt->bind_param("isbs", $message_id, $file_name, $null, $file_type);
                $fileStmt->send_long_data(2, $file_data);
                $fileStmt->execute();
                $fileStmt->close();
            }
        }

        $conn->commit();
        header("Location: " . $referer . "?status=success&message=" . urlencode("Message sent successfully!"));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: " . $referer . "?status=error&message=" . urlencode("Failed to send message: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: " . $referer);
    exit();
}