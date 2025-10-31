<?php
session_start();
include_once '../db/conn.php';
// include_once '../includes/headeradmin.php'; // Make sure to include your admin header

// 1. Security Check: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Access Denied.");
}

// 2. Get current admin's ID from session
$EmployeeID = $_SESSION['EmployeeID'];

// 3. Fetch current username from DB to pre-fill the form
$sql = "SELECT username FROM admin WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $EmployeeID);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Admin account not found.");
}
$current_username = $admin['username'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Account</title>
    <style>
        /* Add your form styles here */
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .form-container {
            max-width: 500px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-container div {
            margin-bottom: 15px;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-container input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <a href="index.php">&laquo; Back to Dashboard</a>
    <div class="form-container">
        <h2>Edit My Admin Account</h2>
        <p>Here you can change your admin username or password.</p>

        <form id="edit-admin-form" action="updateadminaccount.php" method="POST" onsubmit="return validatePassword();">
            <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">

            <div>
                <label for="username">Admin Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>
            </div>

            <hr>
            <p><strong>Change Password</strong> (Leave blank to keep current password)</p>

            <div>
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" name="update" class="btn-submit">Update Account</button>
        </form>
    </div>

    <script>
        // Simple JavaScript to check if passwords match
        function validatePassword() {
            var new_pass = document.getElementById('new_password').value;
            var confirm_pass = document.getElementById('confirm_password').value;

            if (new_pass !== confirm_pass) {
                alert("Error: New passwords do not match.");
                return false; // Stops the form from submitting
            }
            return true; // Allows the form to submit
        }
    </script>
</body>

</html>