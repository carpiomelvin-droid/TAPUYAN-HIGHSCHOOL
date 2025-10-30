<?php
include_once '../db/conn.php';



//register and check if employee exist in the database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empID = trim($_POST['Employeenumber']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'staff';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT EmployeeID FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s',  $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Account already exists! Please log in.');window.location.href='../admin/index.php';</script>";
    } else {
        $sql = "INSERT INTO admin (Employeenumber, username, password, role ) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $empID, $username, $password_hash, $role);

        if ($stmt->execute()) {
            echo "<script>alert('New employee account created successfully!'); window.location.href='../admin/index.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
