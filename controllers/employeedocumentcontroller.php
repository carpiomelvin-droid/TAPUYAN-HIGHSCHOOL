<?php
// === Enable error reporting during development ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Collect EmployeeID
    $EmployeeID = $_POST['EmployeeID'];

    // 2️⃣ Check if EmployeeID exists in admin table
    $check = $conn->prepare("SELECT EmployeeID FROM admin WHERE EmployeeID = ?");
    $check->bind_param("i", $EmployeeID);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows === 0) {
        echo "<script>
            alert('Error: EmployeeID {$EmployeeID} does not exist in the admin table. Please create the account first.');
            window.history.back();
        </script>";
        exit();
    }
    $check->close();

    // 3️⃣ Safely read file contents (binary)
    function getFileContent($fieldName)
    {
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
            // Check file size (5MB limit)
            if ($_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
                echo "<script>
                    alert('Error: File " . htmlspecialchars($_FILES[$fieldName]['name']) . " is larger than 5MB.');
                    window.history.back();
                </script>";
                exit();
            }
            return file_get_contents($_FILES[$fieldName]['tmp_name']);
        }
        return null;
    }

    // 4️⃣ Retrieve all file data
    $files = [
        'prc_file' => getFileContent('prc_file'),
        'dll_file' => getFileContent('dll_file'),
        'saln_file' => getFileContent('saln_file'),
        'ipcr_file' => getFileContent('ipcr_file'),
        'diploma_file' => getFileContent('diploma_file'),
        'tor_file' => getFileContent('tor_file'),
        'itr_file' => getFileContent('itr_file'),
        'itr_file_2' => getFileContent('itr_file_2'),
        'service_record_file' => getFileContent('service_record_file'),
        'appointment_file' => getFileContent('appointment_file'),
        'trainings_awards_file' => getFileContent('trainings_awards_file'),
        'pds_file' => getFileContent('pds_file')
    ];

    // 5️⃣ Prepare SQL with ON DUPLICATE KEY UPDATE (Corrected for 13 columns)
    $sql = "INSERT INTO employee_documents (
        EmployeeID,
        prc_file, dll_file, saln_file, ipcr_file, diploma_file, tor_file, itr_file,
        itr_file_2, service_record_file, appointment_file, trainings_awards_file, pds_file
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) -- 13 total placeholders
    ON DUPLICATE KEY UPDATE
        prc_file = IF(VALUES(prc_file) IS NOT NULL, VALUES(prc_file), prc_file),
        dll_file = IF(VALUES(dll_file) IS NOT NULL, VALUES(dll_file), dll_file),
        saln_file = IF(VALUES(saln_file) IS NOT NULL, VALUES(saln_file), saln_file),
        ipcr_file = IF(VALUES(ipcr_file) IS NOT NULL, VALUES(ipcr_file), ipcr_file),
        diploma_file = IF(VALUES(diploma_file) IS NOT NULL, VALUES(diploma_file), diploma_file),
        tor_file = IF(VALUES(tor_file) IS NOT NULL, VALUES(tor_file), tor_file),
        itr_file = IF(VALUES(itr_file) IS NOT NULL, VALUES(itr_file), itr_file),
        itr_file_2 = IF(VALUES(itr_file_2) IS NOT NULL, VALUES(itr_file_2), itr_file_2),
        service_record_file = IF(VALUES(service_record_file) IS NOT NULL, VALUES(service_record_file), service_record_file),
        appointment_file = IF(VALUES(appointment_file) IS NOT NULL, VALUES(appointment_file), appointment_file),
        trainings_awards_file = IF(VALUES(trainings_awards_file) IS NOT NULL, VALUES(trainings_awards_file), trainings_awards_file),
        pds_file = IF(VALUES(pds_file) IS NOT NULL, VALUES(pds_file), pds_file)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 6️⃣ Bind parameters (Corrected for 13 columns)
    $null = null; // Placeholder for blob data
    $stmt->bind_param(
        "ibbbbbbbbbbbb",  // 1 integer (i) + 12 blobs (b)
        $EmployeeID,
        $null, // prc_file
        $null, // dll_file
        $null, // saln_file
        $null, // ipcr_file
        $null, // diploma_file
        $null, // tor_file
        $null, // itr_file
        $null, // itr_file_2
        $null, // service_record_file
        $null, // appointment_file
        $null, // trainings_awards_file
        $null  // pds_file
    );

    // 7️⃣ Stream binary data using send_long_data() (Corrected index)
    $fileIndex = 1; // Start at the 2nd parameter (index 1), since index 0 is EmployeeID
    foreach ($files as $data) {
        if (!is_null($data)) {
            $stmt->send_long_data($fileIndex, $data);
        }
        $fileIndex++;
    }

    // 8️⃣ Execute and respond
    if ($stmt->execute()) {
        echo "<script>
            alert('Employee documents saved successfully!');
            window.location.href = '../Employee/documents.php';
        </script>";
    } else {
        // Check for duplicate key error vs. other errors
        if ($stmt->errno == 1062) { // 1062 is the error code for 'Duplicate entry'
            echo "<script>
                alert('Error: This EmployeeID already has an entry. The system tried to update.');
                window.history.back();
            </script>";
        } else {
            echo "<script>
                alert('Error saving documents: " . addslashes($stmt->error) . "');
                window.history.back();
            </script>";
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<h3>Access denied. Please upload through the proper form.</h3>";
}
