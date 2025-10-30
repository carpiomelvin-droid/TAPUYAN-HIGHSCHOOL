<?php
session_start();
include_once '../includes/header.php';
include_once '../db/conn.php';


$username = $_SESSION['username'];
$sql = "SELECT username FROM admin WHERE EmployeeID = ? AND role = 'superadmin' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $admin_username = $row['username'];
} else {
    $admin_username = $username;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

</head>

<body>
    <div>
        <div>
            <h2>Employee List</h2>
            <?php
            // Your SQL query is correct, just added Employeenumber from 'a'
            $sql = "SELECT 
            a.EmployeeID, 
            a.Employeenumber, 
            a.username, 
            d.name 
        FROM 
            admin AS a
        LEFT JOIN 
            employee_details AS d ON a.EmployeeID = d.EmployeeID
        WHERE 
            a.role = 'staff'
        ORDER BY 
            d.name ASC, a.username ASC";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                echo "<table class='employee-table'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Employee Number</th>";
                echo "<th>Full Name</th>";
                /*                 echo "<th>Username</th>";
 */
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
                    $username = htmlspecialchars($row['username']);
                    $view_link = "viewemployee.php?id=" . urlencode($row['EmployeeID']);
                    echo "<tr>";
                    echo "<td>" . $emp_number . "</td>";
                    echo "<td>" . $display_name . "</td>";
                    echo '<td><a href="' . $view_link . '" class="btn-view">View Profile</a></td>';
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No employees found in the system.</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>
    </div>
    <div>
        <h4 class="admin-title">Welcome <?php echo $admin_username; ?></h4>
        <form class="search-bar-container" action="/search" method="get">
            <input type="search" id="search" name="searchemployee" placeholder="Search Employee" />
            <div><button type="submit">Search</button></div>
        </form>
    </div>
    <div>
        <h3>Create Employee Account</h3>
        <form action="../controllers/employeeController.php" method="post" class="form-login">
            <input type="text" placeholder="Enter employee ID" name="Employeenumber" required>
            <input type="text" placeholder="Enter username" name="username" required>
            <input type="password" placeholder="Enter password" name="password" required>
            <input type="submit" value="Create Account" class="btn-create-account">
        </form>
        <span class="closebtn" onclick="this.parentElement.style.display='none'">Close</span>
    </div>

    <!--     dashboard left panel -->
    <div class=" menu-admin">
        <ul>
            <li>Dashboard</>
            <li id="create-account">Create Employee Account</li>
            <li>
                <form action="../controllers/logout.php" method="post">
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </li>
        </ul>
    </div>
    <div>
</body>

</html>