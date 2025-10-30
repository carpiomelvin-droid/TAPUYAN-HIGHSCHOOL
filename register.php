<?php
session_start();
include_once './db/conn.php';
include_once './includes/header.php';



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/register/container.css">
</head>

<body>
    <div class="container-register">
        <h2>Sign up here</h2>
        <form action="./controllers/adminController.php" method="post" class="form-register">
            <input type="text" placeholder="Enter username" id="username" name="username" required>
            <input type="password" placeholder="Enter password" id="password" name="password" required>
            <button type="submit">Register</button>
        </form>
        <span>Do you have an account? &nbsp; <a href="index.php">Log in</a></span>
    </div>
</body>

</html>