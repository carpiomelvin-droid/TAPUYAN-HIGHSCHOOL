<?php
session_start();
include_once '../db/conn.php';


$EmployeeID = $_SESSION['EmployeeID'] ?? NULL;
$display_name = $_SESSION['name'] ?? NULL;

if ($EmployeeID) {
    // 3. Get the proper 'name' from the employee_details table
    $sql = "SELECT name FROM employee_details WHERE EmployeeID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // 4. Bind the EmployeeID as an integer ('i')
        $stmt->bind_param("i", $EmployeeID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // 5. If a name is found, use it
            $display_name = $row['name'];
        }
    }
}
// Initialize all variables to avoid errors
$employeenumber = "";
$details = [
    'employee_number' => '',
    'name' => '',
    'position' => '',
    'basic_salary' => '',
    'original_appointment_date' => '',
    'latest_appointment_date' => '',
    'salary_grade' => '',
    'step' => '',
    'gender' => '',
    'subject_handled' => '',
    'marital_status' => '',
    'birthdate' => '',
    'residential_address' => '',
    'email_address' => '',
    'contact_number' => '',
    'gsis_number' => '',
    'philhealth_number' => '',
    'pagibig_number' => '',
    'tin_number' => '',
    'prc_license' => '',
    'expiration_date' => '',
    'previous_employer' => '',
    'plantilla_number' => '',
    'avatar_image' => null,      // <-- ADDED
    'avatar_mime_type' => ''     // <-- ADDED
];
// <-- ADDED: Path to a default placeholder image
$avatar_src = "../path/to/default/avatar.png"; // Make sure this default path is correct

if ($EmployeeID) {
    // 1. Get Employee Number from 'admin' table
    $sql_admin = "SELECT Employeenumber FROM admin WHERE EmployeeID = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    if ($stmt_admin) {
        $stmt_admin->bind_param("i", $EmployeeID);
        $stmt_admin->execute();
        $stmt_admin->bind_result($employeenumber);
        $stmt_admin->fetch();
        $stmt_admin->close();
    }
    $details['employee_number'] = $employeenumber;

    // 2. Get all other details from 'employee_details' table
    $sql_details = "SELECT * FROM employee_details WHERE EmployeeID = ?"; // Fetches all columns
    $stmt_details = $conn->prepare($sql_details);
    if ($stmt_details) {
        $stmt_details->bind_param("i", $EmployeeID);
        $stmt_details->execute();
        $result = $stmt_details->get_result();
        if ($result->num_rows > 0) {
            $db_data = $result->fetch_assoc();
            $details = array_merge($details, $db_data);
            $details['employee_number'] = $employeenumber;

            // 3. <-- ADDED: Create the avatar image source from BLOB data
            if (!empty($details['avatar_image']) && !empty($details['avatar_mime_type'])) {
                $avatar_src = "data:" . $details['avatar_mime_type'] . ";base64," . base64_encode($details['avatar_image']);
            }
        }
        $stmt_details->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Employee Details | Tapuyan National High School</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --success-color: #28a745;
            --success-hover: #218838;
            --danger-color: #dc3545;
            --danger-hover: #c82333;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --border-radius: 8px;
            --header-height: 60px;
            /* --- NEW: Fixed header height --- */
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
            /* --- NEW: Prevent body from scrolling --- */
            overflow: hidden;
            height: 100vh;
        }

        /* --- Main Navigation Header --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            /* Adjusted padding */
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            /* --- NEW: Fixed height --- */
            height: var(--header-height);
        }

        .header-nav a,
        .header-nav button {
            text-decoration: none;
            color: var(--primary-color);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            transition: background-color 0.2s;
        }

        .header-nav a:hover,
        .header-nav button:hover {
            background-color: #f1f1f1;
        }

        .btn-logout {
            color: var(--danger-color);
            border: 1px solid var(--danger-color) !important;
            background: none;
        }

        .btn-logout:hover {
            background-color: var(--danger-color) !important;
            color: #fff;
        }

        /* --- Button Base Styles --- */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        #btn-save {
            display: none;
            /* Hidden by default */
        }


        /* --- NEW: Full-screen App Grid Layout --- */
        /* The <form> is now the main grid container for the content */
        #employee-form {
            display: grid;
            /* Fixed sidebar, flexible content */
            grid-template-columns: 320px 1fr;
            /* Full height minus the header */
            height: calc(100vh - var(--header-height));
        }

        /* --- Sidebar Content --- */
        .profile-sidebar {
            background-color: var(--card-bg);
            padding: 2.5rem 2rem;
            text-align: center;
            /* --- NEW: Fills grid cell and scrolls if needed --- */
            height: 100%;
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border: 4px solid var(--border-color);
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .avatar-upload-label {
            display: block;
            margin: 1rem 0 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        input[type="file"] {
            font-size: 0.9rem;
            width: 100%;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            word-wrap: break-word;
        }

        .profile-position {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .profile-id {
            font-size: 0.9rem;
            color: var(--text-muted);
            background-color: var(--light-bg);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
        }


        /* --- Details Content --- */
        .profile-details {
            /* --- NEW: Fills grid cell and scrolls --- */
            height: 100%;
            overflow-y: auto;
            padding: 2.5rem;
        }

        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .details-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .detail-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            /* To contain border-radius */
        }

        .detail-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-content {
            padding: 1.5rem;
            display: grid;
            /* Responsive grid: 1 col on small, 2 on medium+ */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;

        }

        .field.full-width {
            grid-column: 1 / -1;
            /* Makes field span full width */
            text-transform: uppercase;
        }

        .field {
            display: flex;
            flex-direction: column;

        }

        .field label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }

        .field textarea {
            resize: vertical;
            min-height: 80px;
        }

        input {
            text-transform: uppercase;
        }

        /* Style for read-only fields */
        input:read-only,
        textarea:read-only,
        select:disabled,
        input[type="file"]:disabled {
            background-color: #f1f1f1;
            color: #555;
            cursor: not-allowed;
            border-color: #e0e0e0;
        }

        /* --- Responsive Design --- */
        @media (max-width: 900px) {
            body {
                overflow: auto;
                /* Allow body to scroll on mobile */
                height: auto;
            }

            #employee-form {
                /* Stack columns */
                grid-template-columns: 1fr;
                height: auto;
                /* Let content flow */
            }

            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                height: auto;
                /* Fit content */
                padding: 2rem 1.5rem;
            }

            .profile-details {
                height: auto;
                /* Fit content */
                padding: 1.5rem;
            }
        }

        @media (max-width: 600px) {
            .main-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
                height: auto;
                /* Let header expand */
            }

            /* Recalculate form height if header height changes */
            #employee-form {
                /* This media query will be overridden by the 900px one, */
                /* so we only need to adjust non-grid styles */
            }

            .details-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .card-content {
                /* Force 1 column */
                grid-template-columns: 1fr;
            }

            .profile-details {
                padding: 1rem;
            }


        }
    </style>
</head>

<body>

    <header class="main-header">
        <div class="header-logo">
            <strong>Tapuyan NHS</strong> Employee Portal
        </div>
        <nav class="header-nav">
            <a href="../Employee/home.php">Fill up Form</a>
            <a href="../Employee/documents.php">Upload documents</a>
            <form action="../controllers/logout.php" method="post" style="display: inline;">
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </nav>
    </header>

    <form id="employee-form" action="../controllers/employeedatacontroller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="EmployeeID" value="<?php echo $_SESSION['EmployeeID']; ?>">

        <aside class="profile-sidebar">
            <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview">

            <label for="avatar" class="avatar-upload-label">Update Profile Avatar</label>
            <input id="avatar" name="avatar" type="file" accept="image/*" onchange="validateAvatar(this)" disabled>

            <h1 class="profile-name" style="text-transform: uppercase;"><?php echo htmlspecialchars($details['name']); ?></h1>
            <p class="profile-position" style="text-transform: capitalize;"><?php echo htmlspecialchars($details['position']); ?></p>
            <span class="profile-id">Employee No: <?php echo htmlspecialchars($details['employee_number']); ?></span>

            <input id="employee_number" name="employee_number" type="hidden" value="<?php echo htmlspecialchars($details['employee_number']); ?>">

        </aside>

        <section class="profile-details">

            <div class="details-header">
                <h1 style="text-transform: uppercase;">Welcome, <?php echo htmlspecialchars($display_name); ?>!</h1>
                <div class="header-buttons">
                    <button id="btn-edit" type="button" class="btn btn-primary">Edit Details</button>
                    <button id="btn-save" type="submit" name="save" class="btn btn-success">Save Changes</button>
                </div>
            </div>

            <div class="detail-card">
                <h2>Personal Information</h2>
                <div class="card-content">
                    <div class="field">
                        <label for="name">Name *</label>
                        <input id="name" name="name" type="text" required maxlength="150" placeholder="Surname, Firstname, MI" value="<?php echo htmlspecialchars($details['name']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="birthdate">Birthdate</label>
                        <input id="birthdate" name="birthdate" type="date" value="<?php echo htmlspecialchars($details['birthdate']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" disabled>
                            <option value="">-- Select --</option>
                            <option value="Male" <?php if ($details['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if ($details['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                            <option value="Other" <?php if ($details['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="marital_status">Marital Status</label>
                        <select id="marital_status" name="marital_status" disabled>
                            <option value="">-- Select --</option>
                            <option value="Single" <?php if ($details['marital_status'] == 'Single') echo 'selected'; ?>>Single</option>
                            <option value="Married" <?php if ($details['marital_status'] == 'Married') echo 'selected'; ?>>Married</option>
                            <option value="Widowed" <?php if ($details['marital_status'] == 'Widowed') echo 'selected'; ?>>Widowed</option>
                            <option value="Separated" <?php if ($details['marital_status'] == 'Separated') echo 'selected'; ?>>Separated</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="email_address">Email Address</label>
                        <input id="email_address" name="email_address" type="email" maxlength="150" placeholder="name@example.com" value="<?php echo htmlspecialchars($details['email_address']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="contact_number">Contact Number</label>
                        <input id="contact_number" name="contact_number" type="text" maxlength="20" placeholder="09171234567" value="<?php echo htmlspecialchars($details['contact_number']); ?>" readonly>
                    </div>
                    <div class="field full-width">
                        <label for="residential_address">Residential Address</label>
                        <textarea id="residential_address" name="residential_address" rows="2" placeholder="Street, Barangay, City" readonly><?php echo htmlspecialchars($details['residential_address']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2>Employment Details</h2>
                <div class="card-content">
                    <div class="field">
                        <label for="position">Position</label>
                        <input id="position" name="position" type="text" maxlength="100" placeholder="Teacher I" value="<?php echo htmlspecialchars($details['position']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="subject_handled">Subject Handled</label>
                        <input id="subject_handled" name="subject_handled" type="text" maxlength="150" placeholder="Mathematics" value="<?php echo htmlspecialchars($details['subject_handled']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="basic_salary">Basic Salary</label>
                        <input id="basic_salary" name="basic_salary" type="number" step="0.01" min="0" placeholder="25000.00" value="<?php echo htmlspecialchars($details['basic_salary']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="salary_grade">Salary Grade</label>
                        <input id="salary_grade" name="salary_grade" type="text" maxlength="10" placeholder="11" value="<?php echo htmlspecialchars($details['salary_grade']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="step">Step</label>
                        <input id="step" name="step" type="text" maxlength="10" placeholder="1" value="<?php echo htmlspecialchars($details['step']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="plantilla_number">Plantilla Number</label>
                        <input id="plantilla_number" name="plantilla_number" type="text" maxlength="100" value="<?php echo htmlspecialchars($details['plantilla_number']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="original_appointment_date">Original Date of Appointment</label>
                        <input id="original_appointment_date" name="original_appointment_date" type="date" value="<?php echo htmlspecialchars($details['original_appointment_date']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="latest_appointment_date">Latest Appointment Date</label>
                        <input id="latest_appointment_date" name="latest_appointment_date" type="date" value="<?php echo htmlspecialchars($details['latest_appointment_date']); ?>" readonly>
                    </div>
                    <div class="field full-width">
                        <label for="previous_employer">Previous Employer</label>
                        <input id="previous_employer" name="previous_employer" type="text" maxlength="150" value="<?php echo htmlspecialchars($details['previous_employer']); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2>IDs & Licenses</h2>
                <div class="card-content">
                    <div class="field">
                        <label for="gsis_number">GSIS Number</label>
                        <input id="gsis_number" name="gsis_number" type="text" maxlength="50" value="<?php echo htmlspecialchars($details['gsis_number']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="philhealth_number">PhilHealth Number</label>
                        <input id="philhealth_number" name="philhealth_number" type="text" maxlength="50" value="<?php echo htmlspecialchars($details['philhealth_number']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="pagibig_number">Pag-IBIG Number</label>
                        <input id="pagibig_number" name="pagibig_number" type="text" maxlength="50" value="<?php echo htmlspecialchars($details['pagibig_number']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="tin_number">TIN Number</label>
                        <input id="tin_number" name="tin_number" type="text" maxlength="50" value="<?php echo htmlspecialchars($details['tin_number']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="prc_license">PRC License</label>
                        <input id="prc_license" name="prc_license" type="text" maxlength="100" placeholder="1234567" value="<?php echo htmlspecialchars($details['prc_license']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label for="expiration_date">Expiration Date (PRC)</label>
                        <input id="expiration_date" name="expiration_date" type="date" value="<?php echo htmlspecialchars($details['expiration_date']); ?>" readonly>
                    </div>
                </div>
            </div>

        </section>
    </form>

    <script>
        document.getElementById('btn-edit').addEventListener('click', function() {
            const form = document.getElementById('employee-form');

            // 1. Get all 'readonly' fields and make them editable
            const readOnlyFields = form.querySelectorAll('input[readonly], textarea[readonly]');
            readOnlyFields.forEach(field => {
                if (field.id !== 'employee_number') {
                    field.readOnly = false;
                }
            });

            // 2. Get all 'disabled' fields and enable them
            const disabledFields = form.querySelectorAll('select[disabled], input[type="file"][disabled]');
            disabledFields.forEach(field => {
                field.disabled = false;
            });

            // 3. Show the "Save" button
            document.getElementById('btn-save').style.display = 'inline-block';

            // 4. Hide the "Edit" button
            this.style.display = 'none';

            // 5. Optional: Focus the first editable field
            document.getElementById('name').focus();
        });

        // Simple validation script for the avatar
        function validateAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (file.size > maxSize) {
                    alert('File is too large! Max allowed size is 5MB.');
                    input.value = ''; // Clear the invalid file
                }
            }
        }
    </script>
</body>

</html>