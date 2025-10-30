<?php
include_once './db/conn.php';
include_once './includes/header.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="./css/login/containerLogin.css">
</head>

<body>
    <div class="container-login">
        <h2>Please Log in</h2>
        <form action="./controllers/adminlogincontroller.php" method="post" class="form-login">
            <input type="text" name="username" placeholder="Enter username" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Log in</button>
        </form>
        <div>
            <a href="forgotpassword.php">
                <span>Forgot password?</span>
            </a>
        </div>
    </div>
</body>

</html>
</body>

</html>