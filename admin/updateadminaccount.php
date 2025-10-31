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
$EmployeeID = $_POST['EmployeeID'];
$username = $_POST['username'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password']; // Already checked by JS, but we double-check

// --- 3. Final Security Check ---
// Ensure the EmployeeID from the form matches the one in the session.
// This prevents an admin from trying to edit another admin's account.
if ($EmployeeID != $_SESSION['EmployeeID']) {
    die("Security check failed. You can only edit your own account.");
}

// --- 4. Initialize Query Parts ---
// We will build the query dynamically based on what the user wants to update.
$sql_parts = ["username = ?"]; // Username always updates
$params = [$username];
$types = "s"; // 's' for string (username)

// --- 5. Handle Password Change (if provided) ---
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

// --- 6. Build and Execute the Final Query ---
$sql = "UPDATE admin SET " . implode(", ", $sql_parts) . " WHERE EmployeeID = ?";

// Add the EmployeeID to the end of our parameters
$types .= "i"; // 'i' for integer (EmployeeID)
$params[] = $EmployeeID;

$stmt = $conn->prepare($sql);
// We use the "splat" operator (...) to pass our array of params
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // IMPORTANT: Update session username in case it changed
    $_SESSION['name'] = $username;

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
