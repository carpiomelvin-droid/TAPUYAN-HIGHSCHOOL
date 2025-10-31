<?php
session_start();
include_once '../db/conn.php';
include_once '../includes/deletefunction.php';

// --- 2. SECURITY CHECK: Ensure user is an admin ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Access Denied. You do not have permission to perform this action.");
}

// --- 3. SECURITY CHECK: Ensure it's a POST request and ID is set ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['EmployeeID'])) {
    die("Invalid Request.");
}

$EmployeeID = $_POST['EmployeeID'];

// --- 4. CALL THE FUNCTION ---
// The function returns true for success or false for failure
if (deleteEmployee($conn, $EmployeeID)) {

    // --- 5. RESPOND WITH SUCCESS ---
    echo "<script>
            alert('Employee account deleted successfully.');
            window.location.href = 'index.php';
          </script>";
} else {

    // --- 6. RESPOND WITH FAILURE ---
    echo "<script>
            alert('Error deleting account. The operation was rolled back.');
            window.location.href = 'index.php';
          </script>";
}
$conn->close();
exit;
