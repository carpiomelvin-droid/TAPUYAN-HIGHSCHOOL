<?php
include_once './db/conn.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
    <link rel="icon" type="image/png" href="src/images/logo.png">
    <link rel="stylesheet" href="css/register/container.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container-register">
        <h2>Sign up here</h2>
        <form action="./controllers/adminController.php" method="post" class="form-register">
            <input type="text" placeholder="Enter username" id="username" name="username" required>
            <div class="password-field">
                <input type="password" placeholder="Enter password" id="password" name="password" required>
                <button type="button" class="password-toggle" data-password-target="password" aria-label="Show password"
                    title="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
            </div>
            <button type="submit">Register</button>
        </form>
        <span>Do you have an account? &nbsp; <a href="index.php">Log in</a></span>
    </div>
</body>

<script>
    document.querySelector('.password-toggle')?.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.passwordTarget);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        this.querySelector('i').className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        this.title = isHidden ? 'Hide password' : 'Show password';
    });
</script>
</body>

</html>