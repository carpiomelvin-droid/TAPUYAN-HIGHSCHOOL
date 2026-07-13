<?php
include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empID = trim($_POST['Employeenumber']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'staff';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $status = '';
    $title = '';
    $message = '';

    $check_sql = "SELECT EmployeeID FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $status = "error";
        $title = "Account Exists";
        $message = "This username already exists. Please use a different username.";
    } else {
        $sql = "INSERT INTO admin (Employeenumber, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $empID, $username, $password_hash, $role);

        if ($stmt->execute()) {
            $status = "success";
            $title = "Account Created";
            $message = "New employee account created successfully.";
        } else {
            $status = "error";
            $title = "Creation Failed";
            $message = "Something went wrong while creating the account.";
        }

        $stmt->close();
    }

    $check_stmt->close();
    $conn->close();
} else {
    die("Invalid Request.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: transparent;
        }

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            width: 360px;
            max-width: calc(100% - 32px);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.16);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            z-index: 9999;
            animation: slideIn 0.35s ease, fadeOut 0.35s ease 1.9s forwards;
            border-left: 5px solid;
        }

        .toast.success {
            border-left-color: #22c55e;
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .toast.success .toast-icon {
            background: rgba(34, 197, 94, 0.12);
            color: #16a34a;
        }

        .toast.error .toast-icon {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 0.92rem;
            color: #6b7280;
            line-height: 1.45;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(30px);
            }
        }
    </style>
</head>

<body>

    <div class="toast <?php echo $status; ?>">
        <div class="toast-icon">
            <?php echo $status === 'success' ? '✓' : '✕'; ?>
        </div>
        <div class="toast-content">
            <div class="toast-title"><?php echo htmlspecialchars($title); ?></div>
            <div class="toast-message"><?php echo htmlspecialchars($message); ?></div>
        </div>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = "../admin/index.php";
        }, 2200);
    </script>
</body>

</html>