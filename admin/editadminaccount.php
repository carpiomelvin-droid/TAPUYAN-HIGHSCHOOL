<?php
session_start();
include_once '../db/conn.php';

// 1. Security Check: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Access Denied.");
}

// 2. Get current admin's ID from session
$EmployeeID = $_SESSION['EmployeeID'];

// 3. Fetch admin's display name for header
$admin_display_name = $_SESSION['username'] ?? 'Admin'; // Fallback
$sql_name = "SELECT name FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_name = $conn->prepare($sql_name);
if ($stmt_name) {
    $stmt_name->bind_param("i", $EmployeeID);
    $stmt_name->execute();
    $result_name = $stmt_name->get_result();
    if ($row_name = $result_name->fetch_assoc()) {
        $admin_display_name = $row_name['name'];
    }
    $stmt_name->close();
}

// 4. Fetch current username from DB to pre-fill the form
$sql_user = "SELECT username FROM admin WHERE EmployeeID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $EmployeeID);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$admin = $result_user->fetch_assoc();

if (!$admin) {
    die("Admin account not found.");
}
$current_username = $admin['username'];
$stmt_user->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Account</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --danger-color: #dc3545;
            --danger-hover: #c82333;
            --success-color: #28a745;
            --success-hover: #218838;
            --secondary-color: #6c757d;
            --secondary-hover: #5a6268;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --header-height: 60px;
            --sidebar-bg: #2c3e50;
            --sidebar-text: #ecf0f1;
            --sidebar-hover: #34495e;
            --sidebar-active: #007bff;
            --sidebar-width: 240px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
        }

        /* --- Main Header --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            height: var(--header-height);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .header-logo strong {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .header-user span {
            font-weight: 500;
        }

        /* --- Base Button Styles --- */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #fff;
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-logout:hover {
            background: var(--danger-color);
            color: #fff;
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        /* --- Admin Dashboard Layout (Grid) --- */
        .admin-container {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            height: calc(100vh - var(--header-height));
        }

        /* --- Sidebar --- */
        .admin-sidebar {
            background-color: var(--sidebar-bg);
            padding: 1.5rem 1rem;
            height: 100%;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a,
        .sidebar-nav button {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--sidebar-text);
            border-radius: 6px;
            transition: background-color 0.2s;
            background: none;
            border: none;
            text-align: left;
            font-size: 1rem;
            font-family: inherit;
            cursor: pointer;
        }

        .sidebar-nav a:hover,
        .sidebar-nav button:hover {
            background-color: var(--sidebar-hover);
        }

        .sidebar-nav a.active {
            background-color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar-nav form .btn-logout {
            width: 100%;
            margin-top: 1rem;
            text-align: left;
        }

        /* --- Main Content Area --- */
        .admin-content {
            height: 100%;
            overflow-y: auto;
            /* --- NEW: Flexbox to center the card --- */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2.5rem;
        }

        /* --- NEW: Modal-Style Card --- */
        .modal-style-card {
            background: var(--card-bg);
            padding: 2rem 2.5rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--border-color);
        }

        .modal-style-card h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .modal-style-card p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        /* --- NEW: Modal Form Styles --- */
        .modal-form .form-field {
            margin-bottom: 1rem;
        }

        .modal-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .modal-form input[type="text"],
        .modal-form input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
        }

        .modal-form .btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            margin-top: 1rem;
        }

        hr {
            border: 0;
            border-top: 1px solid var(--border-color);
            margin: 1.5rem 0;
        }

        /* --- Responsive --- */
        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .admin-container {
                grid-template-columns: 1fr;
                height: auto;
            }

            .admin-sidebar {
                height: auto;
                width: 100%;
                display: flex;
                overflow-x: auto;
            }

            .sidebar-nav ul {
                display: flex;
                gap: 0.5rem;
            }

            .admin-content {
                height: auto;
                padding: 1.5rem;
                align-items: flex-start;
            }

            .modal-style-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="header-logo">
            <strong>Tapuyan NHS</strong> Admin Portal
        </div>
        <div class="header-user">
            <span>Welcome, <?php echo htmlspecialchars($admin_display_name); ?>!</span>
        </div>
    </header>

    <div class="admin-container">

        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li id="create-account"><button type="button" onclick="window.location.href='index.php'">Create Employee</button></li>
                    <li><a href="editadminaccount.php" class="active">Edit My Account</a></li>
                    <li>
                        <form action="../controllers/logout.php" method="post">
                            <button type="submit" class="btn btn-logout">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">

            <div class="modal-style-card">
                <h2>Edit My Admin Account</h2>
                <p>Here you can change your admin username or password.</p>

                <form id="edit-admin-form" class="modal-form" action="updateadminaccount.php" method="POST" onsubmit="return validatePassword();">
                    <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">

                    <div class="form-field">
                        <label for="username">Admin Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>
                    </div>

                    <hr>
                    <p style="margin-bottom: 1rem;"><strong>Change Password</strong> (Leave blank to keep current)</p>

                    <div class="form-field">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-field">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit" name="update" class="btn btn-success">Update Account</button>
                </form>
            </div>

        </main>
    </div>
    <script>
        // Simple JavaScript to check if passwords match
        function validatePassword() {
            var new_pass = document.getElementById('new_password').value;
            var confirm_pass = document.getElementById('confirm_password').value;

            // Only validate if new_pass is not empty
            if (new_pass !== "") {
                if (new_pass !== confirm_pass) {
                    alert("Error: New passwords do not match.");
                    return false; // Stops the form from submitting
                }
            }
            return true; // Allows the form to submit
        }
    </script>