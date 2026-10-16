<?php
session_start();
header('Content-Type: application/json');

include_once '../db/conn.php';

// 1. Verify user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // 2. Validate input fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit();
    }

    if (strlen($new_password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters long.']);
        exit();
    }

    // 3. Fetch current password hash from database
    $sql = "SELECT password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // 4. Verify the current password
        if (password_verify($current_password, $row['password'])) {

            // 5. Hash and update the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $update_sql = "UPDATE admin SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_password_hash, $username);

            if ($update_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>