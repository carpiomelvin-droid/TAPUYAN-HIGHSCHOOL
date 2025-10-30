<?php
session_start();
include_once '../db/conn.php';
include_once '../includes/header.php';

// Login functionality for admin and staff
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admin WHERE username = ? AND (role = 'superadmin' OR role = 'staff') LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['EmployeeID'] = $row['EmployeeID'];
            if ($row['role'] == "superadmin") {
                header("Location: ../admin/index.php");
                exit;
            } else {
                header("Location: ../Employee/home.php");
                exit;
            }
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='../index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='../index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
