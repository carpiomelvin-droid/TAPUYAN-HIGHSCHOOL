<?php
session_start();
include_once '../db/conn.php';

// --- 1. Security Checks ---
// Check 1: Must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update'])) {
    die("Invalid request method.");
}
// Check 2: Must be a logged-in admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Access Denied. You do not have permission.");
}

// --- 2. Get Data ---
$EmployeeID = (int) ($_POST['EmployeeID'] ?? 0);
$username = trim((string) ($_POST['username'] ?? ''));
$full_name = trim((string) ($_POST['full_name'] ?? ''));
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password']; // Already checked by JS, but we double-check

// --- 3. Final Security Check ---
// Ensure the EmployeeID from the form matches the one in the session.
// This prevents an admin from trying to edit another admin's account.
if ($EmployeeID != $_SESSION['EmployeeID']) {
    die("Security check failed. You can only edit your own account.");
}

// --- 4. Validate profile fields and optional avatar ---
if ($username === '' || mb_strlen($username) > 255 || $full_name === '' || mb_strlen($full_name) > 150) {
    die("Username and full name are required.");
}

$avatarData = null;
$avatarMime = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    $avatarUpload = $_FILES['avatar'];
    if ($avatarUpload['error'] !== UPLOAD_ERR_OK || $avatarUpload['size'] > 5 * 1024 * 1024) {
        die("The avatar must be smaller than 5 MB.");
    }
    $avatarFinfo = new finfo(FILEINFO_MIME_TYPE);
    $avatarMime = $avatarFinfo->file($avatarUpload['tmp_name']);
    if (!in_array($avatarMime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
        die("Avatar must be a JPG, PNG, GIF, or WEBP image.");
    }
    $avatarData = file_get_contents($avatarUpload['tmp_name']);
}

// --- 5. Update account credentials ---
$sql_parts = ["username = ?"];
$params = [$username];
$types = "s";

// --- 6. Handle Password Change (if provided) ---
if (!empty($new_password)) {
    // Server-side check that passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Error: New passwords do not match.'); window.history.back();</script>";
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Add password to our query
    $sql_parts[] = "password = ?";
    $params[] = $hashed_password;
    $types .= "s"; // 's' for string (password)
}

// --- 7. Build and Execute the account query ---
$sql = "UPDATE admin SET " . implode(", ", $sql_parts) . " WHERE EmployeeID = ?";

// Add the EmployeeID to the end of our parameters
$types .= "i"; // 'i' for integer (EmployeeID)
$params[] = $EmployeeID;

$stmt = $conn->prepare($sql);
// We use the "splat" operator (...) to pass our array of params
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $profileCheck = $conn->prepare(
        "SELECT id FROM employee_details WHERE EmployeeID = ? ORDER BY id ASC LIMIT 1"
    );
    if (!$profileCheck) {
        die("Unable to read the administrator profile.");
    }
    $profileCheck->bind_param('i', $EmployeeID);
    $profileCheck->execute();
    $profileResult = $profileCheck->get_result();
    $existingProfile = $profileResult ? $profileResult->fetch_assoc() : null;
    $profileCheck->close();

    if ($existingProfile) {
        if ($avatarData !== null) {
            $profileStmt = $conn->prepare(
                "UPDATE employee_details
                 SET name = ?, avatar_image = ?, avatar_mime_type = ?
                 WHERE id = ?"
            );
            $profileNull = null;
            $profileStmt->bind_param('sbsi', $full_name, $profileNull, $avatarMime, $existingProfile['id']);
            $profileStmt->send_long_data(1, $avatarData);
        } else {
            $profileStmt = $conn->prepare(
                "UPDATE employee_details SET name = ? WHERE id = ?"
            );
            $profileStmt->bind_param('si', $full_name, $existingProfile['id']);
        }
    } else {
        $profileStmt = $conn->prepare(
            "INSERT INTO employee_details (EmployeeID, name, avatar_image, avatar_mime_type)
             VALUES (?, ?, ?, ?)"
        );
        $profileNull = null;
        $profileStmt->bind_param('isss', $EmployeeID, $full_name, $profileNull, $avatarMime);
        if ($avatarData !== null) {
            $profileStmt->send_long_data(2, $avatarData);
        }
    }
    if (!$profileStmt || !$profileStmt->execute()) {
        if ($profileStmt) {
            $profileStmt->close();
        }
        die("Unable to save the administrator profile.");
    }
    $profileStmt->close();

    $_SESSION['username'] = $username;
    $_SESSION['name'] = $full_name;

    echo "<script>
            alert('Account updated successfully!');
            window.location.href = 'index.php';
          </script>";
} else {
    echo "<script>
            alert('Error updating account: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
}

$stmt->close();
$conn->close();
