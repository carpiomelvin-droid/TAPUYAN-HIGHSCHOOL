<?php
session_start();
include_once '../includes/header.php'; // Make sure this is the correct admin header
include_once '../db/conn.php';

// --- 1. CORRECTED "WELCOME" LOGIC ---
$EmployeeID = $_SESSION['EmployeeID'] ?? 0;
// Fallback to username
$admin_display_name = $_SESSION['username'] ?? 'Admin';

// Fetch the admin's proper name from their details
$sql_admin = "SELECT name FROM employee_details WHERE EmployeeID = ? LIMIT 1";
$stmt_admin = $conn->prepare($sql_admin);

if ($stmt_admin) {
    $stmt_admin->bind_param("i", $EmployeeID);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($row_admin = $result_admin->fetch_assoc()) {
        $admin_display_name = $row_admin['name']; // Use the full name
    }
    $stmt_admin->close();
}

// --- 2. GET SEARCH TERM ---
$search_query = $_GET['search_query'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>

<body>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f0f0f0;">
        <h4 class="admin-title">Welcome <?php echo htmlspecialchars($admin_display_name); ?></h4>

        <form class="search-bar-container" action="" method="get"> <input type="search" id="search" name="search_query" placeholder="Search Employee Name" value="<?php echo htmlspecialchars($search_query); ?>" />
            <button type="submit">Search</button>
            <?php if (!empty($search_query)): ?>
                <a href="?">Clear</a> <?php endif; ?>
        </form>
    </div>

    <div style="display: flex;">

        <div class="menu-admin" style="width: 200px; padding: 15px; background: #fafafa;">
            <ul>
                <li>Dashboard</li>
                <li id="create-account" style="cursor: pointer; color: blue;">Create Employee Account</li>
                <li>
                    <form action="../controllers/logout.php" method="post">
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>

        <div style="flex: 1; padding: 15px;">
            <h2>Employee List</h2>
            <?php
            // --- 4. SQL QUERY WITH SEARCH LOGIC ---
            $base_sql = "SELECT 
                    a.EmployeeID, 
                    a.Employeenumber, 
                    a.username, 
                    d.name 
                FROM 
                    admin AS a
                LEFT JOIN 
                    employee_details AS d ON a.EmployeeID = d.EmployeeID";

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

            // --- 5. DISPLAY TABLE ---
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
                    $employee_id = $row['EmployeeID']; // Get the ID for the form

                    echo "<tr>";
                    echo "<td>" . $emp_number . "</td>";
                    echo "<td>" . $display_name . "</td>";

                    // --- UPDATED ACTION CELL ---
                    echo '<td>';
                    // 1. View Button
                    echo '<a href="' . $view_link . '"<button type="submit">View Profile</button></a>';
                    // 2. Delete Form & Button
                    echo '<form action="deleteemployee.php" method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to PERMANENTLY delete this employee? This action will remove all their details and documents and cannot be undone.\');">';
                    echo '<input type="hidden" name="EmployeeID" value="' . $employee_id . '">';
                    echo '<button type="submit">Delete</button>';
                    echo '</form>';

                    echo '</td>';
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                if (!empty($search_query)) {
                    echo "<p>No employees found matching your search.</p>";
                } else {
                    echo "<p>No staff employees found in the system.</p>";
                }
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>

    <div id="create-account-form" class="popup-form">
        <span class="closebtn" onclick="this.parentElement.style.display='none'">&times;</span>
        <h3>Create Employee Account</h3>
        <form action="../controllers/employeeController.php" method="post" class="form-login">
            <input type="text" placeholder="Enter employee Number" name="Employeenumber" required> <br><br>
            <input type="text" placeholder="Enter username" name="username" required> <br><br>
            <input type="password" placeholder="Enter password" name="password" required> <br><br>
            <input type="submit" value="Create Account" class="btn-create-account">
        </form>
    </div>

    <script>
        // --- 6. JAVASCRIPT TO SHOW THE POPUP ---
        document.getElementById('create-account').addEventListener('click', function() {
            document.getElementById('create-account-form').style.display = 'block';
        });
    </script>
</body>

</html>