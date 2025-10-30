<?php
// === Enable error reporting during development ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {

    // 1. Helper function for text fields
    function post_or_null($field)
    {
        return isset($_POST[$field]) && $_POST[$field] !== '' ? $_POST[$field] : null;
    }

    // 2a. Collect all TEXT form data
    $EmployeeID = post_or_null('EmployeeID');
    $employee_number = post_or_null('employee_number');
    $name = post_or_null('name');
    $position = post_or_null('position');
    $basic_salary = post_or_null('basic_salary');
    $original_appointment_date = post_or_null('original_appointment_date');
    $latest_appointment_date = post_or_null('latest_appointment_date');
    $salary_grade = post_or_null('salary_grade');
    $step = post_or_null('step');
    $gender = post_or_null('gender');
    $subject_handled = post_or_null('subject_handled');
    $marital_status = post_or_null('marital_status');
    $birthdate = post_or_null('birthdate');
    $residential_address = post_or_null('residential_address');
    $email_address = post_or_null('email_address');
    $contact_number = post_or_null('contact_number');
    $gsis_number = post_or_null('gsis_number');
    $philhealth_number = post_or_null('philhealth_number');
    $pagibig_number = post_or_null('pagibig_number');
    $tin_number = post_or_null('tin_number');
    $prc_license = post_or_null('prc_license');
    $expiration_date = post_or_null('expiration_date');
    $previous_employer = post_or_null('previous_employer');
    $plantilla_number = post_or_null('plantilla_number');

    // 2b. Handle FILE data (Avatar)
    $avatar_img_data = null;
    $avatar_mime_type = null;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        // Check file size (5MB limit)
        if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            echo "<script>
                alert('Error: Avatar file is larger than 5MB.');
                window.history.back();
            </script>";
            exit();
        }

        // Get file contents and mime type
        $avatar_img_data = file_get_contents($_FILES['avatar']['tmp_name']);
        $avatar_mime_type = $_FILES['avatar']['type']; // e.g., "image/png"

    } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors (e.g., partial upload)
        echo "<script>
            alert('Error uploading avatar: Code " . addslashes($_FILES['avatar']['error']) . "');
            window.history.back();
        </script>";
        exit();
    }
    // If no file was uploaded, $avatar_img_data and $avatar_mime_type remain null


    // 3. Prepare SQL with ON DUPLICATE KEY UPDATE (Now 26 columns)
    $sql = "INSERT INTO employee_details (
        EmployeeID, employee_number, name, position, basic_salary, 
        original_appointment_date, latest_appointment_date, salary_grade, step, gender, 
        subject_handled, marital_status, birthdate, residential_address, email_address, 
        contact_number, gsis_number, philhealth_number, pagibig_number, tin_number, 
        prc_license, expiration_date, previous_employer, plantilla_number,
        avatar_image, avatar_mime_type
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?  -- 26 placeholders
    )
    ON DUPLICATE KEY UPDATE
        employee_number = VALUES(employee_number),
        name = VALUES(name),
        position = VALUES(position),
        basic_salary = VALUES(basic_salary),
        original_appointment_date = VALUES(original_appointment_date),
        latest_appointment_date = VALUES(latest_appointment_date),
        salary_grade = VALUES(salary_grade),
        step = VALUES(step),
        gender = VALUES(gender),
        subject_handled = VALUES(subject_handled),
        marital_status = VALUES(marital_status),
        birthdate = VALUES(birthdate),
        residential_address = VALUES(residential_address),
        email_address = VALUES(email_address),
        contact_number = VALUES(contact_number),
        gsis_number = VALUES(gsis_number),
        philhealth_number = VALUES(philhealth_number),
        pagibig_number = VALUES(pagibig_number),
        tin_number = VALUES(tin_number),
        prc_license = VALUES(prc_license),
        expiration_date = VALUES(expiration_date),
        previous_employer = VALUES(previous_employer),
        plantilla_number = VALUES(plantilla_number),
        
        -- This logic updates the avatar ONLY IF a new one is not NULL.
        avatar_image = IF(VALUES(avatar_image) IS NOT NULL, VALUES(avatar_image), avatar_image),
        avatar_mime_type = IF(VALUES(avatar_mime_type) IS NOT NULL, VALUES(avatar_mime_type), avatar_mime_type)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 4. Bind parameters (i = integer, d = double, s = string, b = blob)
    $null_blob = null; // Placeholder for the blob
    $stmt->bind_param(
        "isssdsssssssssssssssssssbs", // 24 types + 'b' (blob) + 's' (string)
        $EmployeeID,
        $employee_number,
        $name,
        $position,
        $basic_salary,
        $original_appointment_date,
        $latest_appointment_date,
        $salary_grade,
        $step,
        $gender,
        $subject_handled,
        $marital_status,
        $birthdate,
        $residential_address,
        $email_address,
        $contact_number,
        $gsis_number,
        $philhealth_number,
        $pagibig_number,
        $tin_number,
        $prc_license,
        $expiration_date,
        $previous_employer,
        $plantilla_number,
        $null_blob,        // Placeholder for avatar_image
        $avatar_mime_type  // The mime type (or null)
    );

    // 5. Stream BLOB data (if it exists)
    //    This must be done AFTER bind_param and BEFORE execute
    if ($avatar_img_data !== null) {
        // 24 is the 0-based index of the avatar_image placeholder
        $stmt->send_long_data(24, $avatar_img_data);
    }

    // 6. Execute and respond
    if ($stmt->execute()) {
        echo "<script>
            alert('Employee data saved successfully!');
            window.location.href = '../Employee/home.php';
        </script>";
    } else {
        echo "<script>
            alert('Error saving data: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<h3>Access denied or invalid request.</h3>";
}
