<?php
session_start();
include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT EmployeeID, Employeenumber, username, password, role FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 🟢 PREVENT ADMINS FROM LOGGING INTO EMPLOYEE PORTAL
        if ($user['role'] !== 'staff') {
            header("Location: ../index.php?error=wrong_role");
            exit();
        }
        // Verify password
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['EmployeeID'] = $user['EmployeeID'];
            $_SESSION['Employeenumber'] = $user['Employeenumber'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Send to Employee Dashboard
            header("Location: ../Employee/home.php");
            exit();
        } else {
            header("Location: ../index.php?error=invalid_password");
            exit();
        }
    } else {
        header("Location: ../index.php?error=not_found");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>