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
$avatar_src = "../path/to/default/avatar.png";

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
    <title>Tapuyan National High School</title>
</head>
<style>
    /* (Your existing styles) */
    button {
        margin-top: 20px;
        padding: 10px;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background-color: #0056b3;
    }

    #btn-form,
    #btn-uploaDoc {
        border: none;
        background-color: transparent;
        color: #007bff;
        font-size: 0.9em;
    }

    .header-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #btn-save,
    #btn-edit {
        width: 100px;
        font-size: 0.8em;
    }

    #btn-edit {
        background-color: #28a745;
    }

    #btn-edit:hover {
        background-color: #218838;
    }

    /* Visual cue for read-only fields (including disabled file inputs) */
    input:read-only,
    textarea:read-only,
    select:disabled,
    input[type="file"]:disabled {
        /* <-- ADDED */
        background-color: #f4f4f4;
        color: #555;
        cursor: not-allowed;
    }

    /* <-- ADDED: Style for the avatar preview --> */
    .avatar-preview {
        width: 150px;
        height: 150px;
        border: 3px solid #ddd;
        border-radius: 50%;
        /* Makes it round */
        object-fit: cover;
        /* Prevents stretching */
        margin: 10px auto;
        /* Center it */
    }
</style>

<body>
    <form action="../controllers/logout.php" method="post">
        <button type="submit" class="btn-logout">Logout</button>
    </form>
    <div class="header-btn">
        <div>
            <button id="btn-form"><a href="../Employee/home.php">Fill up Form</a></button>
            <button id="btn-uploaDoc"><a href="../Employee/documents.php">Upload documents</a></button>
        </div>
    </div>

    <form id="employee-form" action="../controllers/employeedatacontroller.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="EmployeeID" value="<?php echo $_SESSION['EmployeeID']; ?>">

        <div class="field" style="text-align: center; flex: 1 1 100%;">
            <img src="<?php echo $avatar_src; ?>" alt="Profile Avatar" class="avatar-preview">
            <br>
            <label for="avatar">Update Profile Avatar (Image, Max 5MB)</label>
            <input id="avatar" name="avatar" type="file" accept="image/*" onchange="validateAvatar(this)" disabled>
            <p>Welcome! <?php echo htmlspecialchars($display_name); ?></>
        </div>

        <div class="row" id="form-fillup">
            <div class="field">
                <label for="employee_number">Employee Number *</label>
                <input id="employee_number" name="employee_number" type="text" required maxlength="50" value="<?php echo htmlspecialchars($details['employee_number']); ?>" readonly>
            </div>
            <div class="field">
                <label for="name">Name *</label>
                <input id="name" name="name" type="text" required maxlength="150" placeholder="Cruz, John Michael, M" value="<?php echo htmlspecialchars($details['name']); ?>" readonly>
            </div>
            <div class="field">
                <label for="position">Position</label>
                <input id="position" name="position" type="text" maxlength="100" placeholder="Teacher I" value="<?php echo htmlspecialchars($details['position']); ?>" readonly>
            </div>
            <div class="field">
                <label for="basic_salary">Basic Salary</label>
                <input id="basic_salary" name="basic_salary" type="number" step="0.01" min="0" placeholder="25000.00" value="<?php echo htmlspecialchars($details['basic_salary']); ?>" readonly>
            </div>
            <div class="field">
                <label for="original_appointment_date">Original Date of Appointment</label>
                <input id="original_appointment_date" name="original_appointment_date" type="date" value="<?php echo htmlspecialchars($details['original_appointment_date']); ?>" readonly>
            </div>
            <div class="field">
                <label for="latest_appointment_date">Latest Appointment Date</label>
                <input id="latest_appointment_date" name="latest_appointment_date" type="date" value="<?php echo htmlspecialchars($details['latest_appointment_date']); ?>" readonly>
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
                <label for="gender">Gender</label>
                <select id="gender" name="gender" disabled>
                    <option value="">-- Select --</option>
                    <option value="Male" <?php if ($details['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($details['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if ($details['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <div class="field">
                <label for="subject_handled">Subject Handled</label>
                <input id="subject_handled" name="subject_handled" type="text" maxlength="150" placeholder="Mathematics" value="<?php echo htmlspecialchars($details['subject_handled']); ?>" readonly>
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
                <label for="birthdate">Birthdate</label>
                <input id="birthdate" name="birthdate" type="date" value="<?php echo htmlspecialchars($details['birthdate']); ?>" readonly>
            </div>
            <div class="field" style="flex:1 1 100%;">
                <label for="residential_address">Residential Address</label>
                <textarea id="residential_address" name="residential_address" rows="2" placeholder="Street, Barangay, City" readonly><?php echo htmlspecialchars($details['residential_address']); ?></textarea>
            </div>
            <div class="field">
                <label for="email_address">Email Address</label>
                <input id="email_address" name="email_address" type="email" maxlength="150" placeholder="name@example.com" value="<?php echo htmlspecialchars($details['email_address']); ?>" readonly>
            </div>
            <div class="field">
                <label for="contact_number">Contact Number</label>
                <input id="contact_number" name="contact_number" type="text" maxlength="20" placeholder="09171234567" value="<?php echo htmlspecialchars($details['contact_number']); ?>" readonly>
            </div>
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
            <div class="field">
                <label for="previous_employer">Previous Employer</label>
                <input id="previous_employer" name="previous_employer" type="text" maxlength="150" value="<?php echo htmlspecialchars($details['previous_employer']); ?>" readonly>
            </div>
            <div class="field">
                <label for="plantilla_number">Plantilla Number</label>
                <input id="plantilla_number" name="plantilla_number" type="text" maxlength="100" value="<?php echo htmlspecialchars($details['plantilla_number']); ?>" readonly>
            </div>
        </div>
        <div>
            <button id="btn-edit" type="button">Edit</button>
            <button id="btn-save" type="submit" name="save" style="display: none;">Save</button>
        </div>
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
            //    (UPDATED to include the avatar input)
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

        // ADDED: Simple validation script for the avatar
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