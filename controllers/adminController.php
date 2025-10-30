<?php
include_once '../db/conn.php';
include_once '../admin/index.php';



// register and check if admin account exist in the database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT EmployeeID FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Account already exists! Please log in.');window.location.href='../index.php';</script>";
    } else {
        $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $password_hash);

        if ($stmt->execute()) {
            echo "<script>alert('Admin account created successfully!'); window.location.href='../index.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
