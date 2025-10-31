<?php
include_once './db/conn.php';



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
</head>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Tapuyan NHS EMS</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --border-radius: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            /* A subtle gradient background */
            background: linear-gradient(135deg, #e6f0ff, var(--light-bg));

            /* --- Center the login card --- */
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 1rem;
        }

        /* --- The Login Card --- */
        .container-login {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            text-align: center;

            /* --- Minimal Animation: Fade in --- */
            animation: fadeInDrop 0.6s ease-out;
        }

        @keyframes fadeInDrop {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Header Text --- */
        .container-login h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            text-transform: uppercase;
        }


        /* --- The Form --- */
        .form-login {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-login input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;

            /* --- Minimal Animation: Focus --- */
            transition: all 0.2s ease-in-out;
        }

        /* Focus animation */
        .form-login input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
        }

        .form-login button {
            background-color: var(--primary-color);
            color: white;
            padding: 0.9rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;

            /* --- Minimal Animation: Hover --- */
            transition: background-color 0.2s ease;
        }

        /* Button hover animation */
        .form-login button:hover {
            background-color: var(--primary-hover);
        }

        /* --- Forgot Password Link --- */
        .login-footer {
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .login-subtext {
            margin-top: 60px;
        }

        .login-subttitle {
            color: var(--text-muted);
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container-login">
        <h2>Please Log in</h2>
        <p class="login-subttitle">Tapuyan NHS Employee Portal</p>
        <div class="login-subtext">
            <form action="./controllers/adminlogincontroller.php" method="post" class="form-login">
                <input type="text" name="username" placeholder="Enter username" required>
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">Log in</button>
            </form>
        </div>
    </div>
</body>

</html>

</html>
</body>

</html>