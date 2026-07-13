<?php
session_start();
include_once '../db/conn.php';

// --- 1. WELCOME LOGIC ---
$EmployeeID = $_SESSION['EmployeeID'] ?? 0;
$admin_display_name = $_SESSION['username'] ?? 'Admin';

// get admin display name
$sql_admin = "SELECT name FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_admin = $conn->prepare($sql_admin);

if ($stmt_admin) {
    $stmt_admin->bind_param("i", $EmployeeID);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($row_admin = $result_admin->fetch_assoc()) {
        $admin_display_name = $row_admin['name'];
    }
    $stmt_admin->close();
}

// get current username for edit modal
$current_username = $_SESSION['username'] ?? 'Admin';
$sql_user = "SELECT username FROM admin WHERE EmployeeID = ? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
if ($stmt_user) {
    $stmt_user->bind_param("i", $EmployeeID);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($row_user = $result_user->fetch_assoc()) {
        $current_username = $row_user['username'];
    }
    $stmt_user->close();
}

$search_query = trim($_GET['search_query'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #ffff00ff;
            --primary-hover: #f4f408ff;
            --danger-color: #fb1930ff;
            --danger-hover: #c82333;
            --success-color: #ffff00ff;
            --success-hover: #f4f408ff;
            --secondary-color: #ffff00ff;
            --secondary-hover: #f4f408ff;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --header-height: 60px;
            --sidebar-bg: #ffffffff;
            --sidebar-text: #010101ff;
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
            color: #010101ff;
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-logout:hover {
            background: var(--danger-color);
            color: #ff0000ff;
        }

        .btn-view {
            background-color: var(--secondary-color);
        }

        .btn-view:hover {
            background-color: var(--secondary-hover);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: #fff;
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
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
            margin-top: -25px;
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

        .sidebar-nav a.active,
        .sidebar-nav button.active {
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
            display: flex;
            flex-direction: column;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .search-bar {
            display: flex;
        }

        .search-bar input[type="search"] {
            padding: 0.6rem 1rem;
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 6px 0 0 6px;
            font-size: 1rem;
            min-width: 250px;
        }

        .search-bar button[type="submit"] {
            padding: 0 1rem;
            border: 1px solid var(--primary-color);
            background-color: var(--primary-color);
            color: white;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            font-size: 1rem;
        }

        .search-bar a {
            margin-left: 0.5rem;
            align-self: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .content-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: 380px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .employee-table {
            width: 100%;
            border-collapse: collapse;
            text-transform: uppercase;
        }

        .employee-table th,
        .employee-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .employee-table th {
            background-color: var(--light-bg);
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .employee-table tbody tr:last-child td {
            border-bottom: none;
        }

        .employee-table tbody tr:hover {
            background-color: #fcfcfc;
        }

        .no-name {
            font-style: italic;
            color: var(--text-muted);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons form {
            display: inline-block;
        }

        .no-results {
            padding: 2rem;
            text-align: center;
            color: var(--text-muted);
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 8px;
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

        .action-buttons .btn {
            min-width: 80px;
            font-size: 0.8em;
            height: 100%;
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

        @media (max-width: 600px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                width: 100%;
            }

            .search-bar input {
                flex: 1;
                min-width: 0;
            }

            .action-buttons {
                flex-direction: column;
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
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><button type="button" id="create-account">Create Employee</button></li>
                    <li><button type="button" id="openEditModal">Edit My Account</button></li>
                    <li>
                        <form action="../controllers/logout.php" method="post">
                            <button type="submit" class="btn btn-logout">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="content-header">
                <h1>Employee Dashboard</h1>
                <form class="search-bar" action="" method="get">
                    <input type="search" id="search" name="search_query" placeholder="Search Employee Name"
                        value="<?php echo htmlspecialchars($search_query); ?>" />
                    <button type="submit">🔍</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="index.php">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="content-card">
                <?php
                $base_sql = "SELECT a.EmployeeID, a.Employeenumber, a.username, d.name
                             FROM admin AS a
                             LEFT JOIN employee_details AS d ON a.EmployeeID = d.EmployeeID";

                $where_conditions = ["a.role = 'staff'"];
                $params = [];
                $types = "";

                if ($search_query !== '') {
                    $where_conditions[] = "(d.name LIKE ? OR a.username LIKE ? OR a.Employeenumber LIKE ?)";
                    $search_term = "%" . $search_query . "%";
                    $params[] = $search_term;
                    $params[] = $search_term;
                    $params[] = $search_term;
                    $types .= "sss";
                }

                $sql = $base_sql . " WHERE " . implode(" AND ", $where_conditions) . " ORDER BY d.name ASC, a.username ASC";

                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    echo "<table class='employee-table'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Employee Number</th>";
                    echo "<th>Full Name</th>";
                    echo "<th>Action</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    while ($row = $result->fetch_assoc()) {
                        if (!empty($row['name'])) {
                            $display_name = htmlspecialchars($row['name']);
                        } else {
                            $display_name = '<i class="no-name">' . htmlspecialchars($row['username']) . ' (Username)</i>';
                        }

                        $emp_number = htmlspecialchars($row['Employeenumber']);
                        $view_link = "viewemployee.php?id=" . urlencode($row['EmployeeID']);
                        $employee_id = $row['EmployeeID'];

                        echo "<tr>";
                        echo "<td>" . $emp_number . "</td>";
                        echo "<td>" . $display_name . "</td>";
                        echo '<td><div class="action-buttons">';
                        echo '<a href="' . $view_link . '" class="btn btn-view">View</a>';
                        echo '<form action="deleteemployee.php" method="POST" onsubmit="return confirm(\'Are you sure you want to PERMANENTLY delete this employee? This action will remove all their details and documents and cannot be undone.\');">';
                        echo '<input type="hidden" name="EmployeeID" value="' . $employee_id . '">';
                        echo '<button type="submit" class="btn btn-danger">Delete</button>';
                        echo '</form>';
                        echo '</div></td>';
                        echo "</tr>";
                    }

                    echo "</tbody>";
                    echo "</table>";
                } else {
                    if (!empty($search_query)) {
                        echo "<p class='no-results'>No employees found matching your search.</p>";
                    } else {
                        echo "<p class='no-results'>No staff employees found in the system.</p>";
                    }
                }
                $stmt->close();
                ?>
            </div>
        </main>
    </div>

    <!-- Create Employee Modal -->
    <div id="create-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close" id="closeCreateModal">&times;</span>
            <h3>Create Employee Account</h3>

            <form action="../controllers/employeeController.php" method="post" class="modal-form">
                <div class="form-field">
                    <label for="emp_num">Employee Number</label>
                    <input id="emp_num" type="text" placeholder="Enter employee Number" name="Employeenumber" required>
                </div>
                <div class="form-field">
                    <label for="emp_user">Username</label>
                    <input id="emp_user" type="text" placeholder="Enter username" name="username" required>
                </div>
                <div class="form-field">
                    <label for="emp_pass">Password</label>
                    <input id="emp_pass" type="password" placeholder="Enter password" name="password" required>
                </div>

                <input type="submit" value="Create Account" class="btn btn-success">
            </form>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="edit-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close" id="closeEditModal">&times;</span>
            <h3>Edit My Admin Account</h3>
            <p class="modal-subtitle">Update your username or password.</p>

            <form action="updateadminaccount.php" method="POST" class="modal-form"
                onsubmit="return validatePassword();">
                <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">

                <div class="form-field">
                    <label for="edit_username">Admin Username</label>
                    <input id="edit_username" type="text" name="username"
                        value="<?php echo htmlspecialchars($current_username); ?>" required>
                </div>

                <hr class="divider">

                <p class="helper-text"><strong>Change Password</strong> (Leave blank to keep current password)</p>

                <div class="form-field">
                    <label for="new_password">New Password</label>
                    <input id="new_password" type="password" name="new_password">
                </div>

                <div class="form-field">
                    <label for="confirm_password">Confirm New Password</label>
                    <input id="confirm_password" type="password" name="confirm_password">
                </div>

                <input type="submit" name="update" value="Update Account" class="btn btn-success">
            </form>
        </div>
    </div>

    <script>
        const createModal = document.getElementById('create-account-form');
        const createBtn = document.getElementById('create-account');
        const closeCreateBtn = document.getElementById('closeCreateModal');

        createBtn.addEventListener('click', function () {
            createModal.style.display = 'flex';
        });

        closeCreateBtn.addEventListener('click', function () {
            createModal.style.display = 'none';
        });

        createModal.addEventListener('click', function (event) {
            if (event.target === createModal) {
                createModal.style.display = 'none';
            }
        });

        const editModal = document.getElementById('edit-account-form');
        const editBtn = document.getElementById('openEditModal');
        const closeEditBtn = document.getElementById('closeEditModal');

        editBtn.addEventListener('click', function () {
            editModal.style.display = 'flex';
        });

        closeEditBtn.addEventListener('click', function () {
            editModal.style.display = 'none';
        });

        editModal.addEventListener('click', function (event) {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });

        function validatePassword() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass !== "" && newPass !== confirmPass) {
                alert("Error: New passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>