<?php
session_start();
// include_once '../includes/header.php'; // This is likely not needed if we build the header here
include_once '../db/conn.php';

// --- 1. WELCOME LOGIC ---
$EmployeeID = $_SESSION['EmployeeID'] ?? 0;
$admin_display_name = $_SESSION['username'] ?? 'Admin'; // Fallback

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

$search_query = $_GET['search_query'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --danger-color: #fb1930ff;
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
            /* --- NEW: Sidebar Colors --- */
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
            /* Main body does not scroll */
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

        .header-user {
            display: flex;
            align-items: center;
            gap: 1rem;

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

        .btn-view {
            background-color: var(--secondary-color);
        }

        .btn-view:hover {
            background-color: var(--secondary-hover);
        }

        .btn-danger {
            background-color: var(--danger-color);
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


        /* --- NEW: Admin Dashboard Layout (Grid) --- */
        .admin-container {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            height: calc(100vh - var(--header-height));
        }

        /* --- NEW: Sidebar --- */
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

        /* --- NEW: Main Content Area --- */
        .admin-content {
            height: 100%;
            overflow-y: auto;
            padding: 2.5rem;
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

        /* --- NEW: Search Bar --- */
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

        /* --- NEW: Content Card & Table --- */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            /* To keep table borders neat */
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

        /* --- NEW: Modal (Popup Form) --- */
        .modal-backdrop {
            display: none;
            /* Hidden by default */
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
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        .modal-content h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
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

        .action-buttons .btn {
            min-width: 80px;
            font-size: 0.8em;
            height: 100%;
        }


        /* --- Responsive --- */
        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .admin-container {
                grid-template-columns: 1fr;
                /* Stack sidebar on top */
                height: auto;
            }

            .admin-sidebar {
                height: auto;
                width: 100%;
                display: flex;
                overflow-x: auto;
                /* Make nav horizontally scrollable if needed */
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
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li id="create-account"><button type="button">Create Employee</button></li>
                    <li><a href="editadminaccount.php">Edit My Account</a></li>
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
                    <input type="search" id="search" name="search_query" placeholder="Search Employee Name" value="<?php echo htmlspecialchars($search_query); ?>" />
                    <button type="submit">🔍</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="?">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="content-card">
                <?php
                // --- SQL QUERY WITH SEARCH LOGIC ---
                $base_sql = "SELECT a.EmployeeID, a.Employeenumber, a.username, d.name 
                             FROM admin AS a
                             LEFT JOIN employee_details AS d ON a.EmployeeID = d.EmployeeID";

                $where_conditions = ["a.role = 'staff'"];
                $params = [];
                $types = "";

                if (!empty($search_query)) {
                    $where_conditions[] = "d.name LIKE ?";
                    $params[] = "%" . $search_query . "%";
                    $types .= "s";
                }

                $sql = $base_sql . " WHERE " . implode(" AND ", $where_conditions) . " ORDER BY d.name ASC, a.username ASC";

                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                // --- DISPLAY TABLE ---
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

                        // 1. View Button
                        echo '<a href="' . $view_link . '" class="btn btn-view">View</a>';

                        // 2. Delete Form & Button
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
                $conn->close();
                ?>
            </div>
        </main>
    </div>
    <div id="create-account-form" class="modal-backdrop">
        <div class="modal-content">
            <span class="modal-close" onclick="this.closest('.modal-backdrop').style.display='none'">&times;</span>
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

    <script>
        // Get the modal
        const modal = document.getElementById('create-account-form');

        // Get the button that opens the modal
        const btn = document.getElementById('create-account');

        // Get the <span> element that closes the modal
        const span = document.querySelector('.modal-close');

        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = 'flex'; // Use flex for centering
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = 'none';
        }

        // When the user clicks anywhere outside of the modal content, close it
        modal.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>