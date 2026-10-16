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
$admin_display_name = $_SESSION['username'] ?? 'Admin';
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

// 4. Fetch current account profile and username to pre-fill the form
$sql_user = "SELECT a.username, d.name, d.avatar_image, d.avatar_mime_type
    FROM admin a
    LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID
    WHERE a.EmployeeID = ? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $EmployeeID);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$admin = $result_user->fetch_assoc();

if (!$admin) {
    die("Admin account not found.");
}
$current_username = $admin['username'];
$current_full_name = trim((string) ($admin['name'] ?? ''));
$current_avatar = $admin['avatar_image'] ?? null;
$current_avatar_mime = trim((string) ($admin['avatar_mime_type'] ?? ''));
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
            --danger-color: #dc3545;
            --success-color: #ffff00ff;
            --success-hover: #f4f408ff;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --header-height: 60px;
            --sidebar-bg: #ffffffff;
            --sidebar-text: #050505ff;
            --sidebar-hover: #f4f408ff;
            --sidebar-active: #ffff00ff;
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

        .btn {
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #000;
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

        .admin-container {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            height: calc(100vh - var(--header-height));
        }

        .admin-sidebar {
            background-color: var(--sidebar-bg);
            padding: 1.5rem 1rem;
            height: 100%;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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

        .sidebar-nav .active {
            background-color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar-nav form .btn-logout {
            width: 100%;
            margin-top: 1rem;
            text-align: left;
        }

        .admin-content {
            height: 100%;
            overflow: hidden;
            padding: 2rem;
            background-color: #ebecedff;
        }

        /* modal same behavior as create employee */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 0.75rem;
            right: 1rem;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        .modal-content h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

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

        .profile-upload {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem;
            border: 1px dashed #b8c4d4;
            border-radius: 10px;
            background: #f8fafc;
        }

        .profile-upload input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .profile-upload-button {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem .8rem;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .profile-upload-button:hover {
            background: #1d4ed8;
        }

        .profile-upload-name {
            min-width: 0;
            color: var(--text-muted);
            font-size: .88rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 2.7rem;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: .4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            margin: 0;
            padding: 0;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1;
        }

        .modal-form .btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .divider {
            border: 0;
            border-top: 1px solid var(--border-color);
            margin: 1.2rem 0;
        }

        .helper-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

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

            .sidebar-nav li {
                margin-bottom: 0;
            }

            .admin-content {
                height: auto;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeradmin.php'; ?>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><button type="button" onclick="window.location.href='index.php'">Create Employee</button></li>
                    <li>
                        <button type="button" id="openEditModal">Edit My Account</button>
                    </li>
                    <li>
                        <form action="../controllers/logout.php" method="post">
                            <button type="submit" class="btn btn-logout">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content"></main>
    </div>

    <div id="edit-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h3>Edit My Admin Account</h3>
            <p class="modal-subtitle">Update your public profile, username, or password.</p>

            <form id="edit-admin-form" action="updateadminaccount.php" method="POST" enctype="multipart/form-data"
                class="modal-form" onsubmit="return validatePassword();">
                <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">

                <div class="form-field">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" maxlength="150" value=""
                        placeholder="Enter your full name" required>
                </div>

                <div class="form-field">
                    <label for="avatar">Profile Avatar</label>
                    <div class="profile-upload">
                        <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <label class="profile-upload-button" for="avatar">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                            Browse image
                        </label>
                        <span id="avatar-name" class="profile-upload-name">No image selected</span>
                    </div>
                    <small>Optional · JPG, PNG, GIF, or WEBP · Maximum 5 MB</small>
                </div>

                <div class="form-field">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username"
                        value="<?php echo htmlspecialchars($current_username); ?>" required>
                </div>

                <hr class="divider">

                <p class="helper-text"><strong>Change Password</strong> (Leave blank to keep current password)</p>

                <div class="form-field">
                    <label for="new_password">New Password</label>
                    <div class="password-field">
                        <input type="password" id="new_password" name="new_password">
                        <button type="button" class="password-toggle" data-password-target="new_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="form-field">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password">
                        <button type="button" class="password-toggle" data-password-target="confirm_password"
                            aria-label="Show password" title="Show password"><i class="fa-solid fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                </div>

                <input type="submit" name="update" value="Update Account" class="btn btn-success">
            </form>
        </div>
    </div>

    <script>
        const avatarInput = document.getElementById('avatar');
        const avatarName = document.getElementById('avatar-name');
        if (avatarInput && avatarName) {
            avatarInput.addEventListener('change', function () {
                avatarName.textContent = this.files.length ? this.files[0].name : 'No image selected';
            });
        }

        document.querySelectorAll('.password-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.passwordTarget);
                if (!input) return;
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                this.querySelector('i').className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                this.title = isHidden ? 'Hide password' : 'Show password';
            });
        });

        const modal = document.getElementById('edit-account-form');
        const btn = document.getElementById('openEditModal');
        const span = document.querySelector('#edit-account-form .modal-close');

        btn.onclick = function () {
            modal.style.display = 'flex';
        }

        span.onclick = function () {
            modal.style.display = 'none';
        }

        modal.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function validatePassword() {
            var new_pass = document.getElementById('new_password').value;
            var confirm_pass = document.getElementById('confirm_password').value;

            if (new_pass !== "") {
                if (new_pass !== confirm_pass) {
                    alert("Error: New passwords do not match.");
                    return false;
                }
            }
            return true;
        }
    </script>
</body>

</html>