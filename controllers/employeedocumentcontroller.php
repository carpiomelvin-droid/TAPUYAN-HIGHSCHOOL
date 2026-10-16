<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect EmployeeID
    $EmployeeID = $_POST['EmployeeID'];

    // 2. Check if EmployeeID exists in admin table
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

    // 3. Allowed file extensions and MIME types
    function getAllowedFileRules()
    {
        return [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp']
        ];
    }

    // 4. Safely read file contents with validation
    function getFileContent($fieldName)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            echo "<script>
                alert('Error uploading file: " . addslashes($_FILES[$fieldName]['name']) . "');
                window.history.back();
            </script>";
            exit();
        }

        // Check file size (5MB limit)
        if ($_FILES[$fieldName]['size'] > 5 * 1024 * 1024) {
            echo "<script>
                alert('Error: File " . addslashes($_FILES[$fieldName]['name']) . " is larger than 5MB.');
                window.history.back();
            </script>";
            exit();
        }

        $fileName = $_FILES[$fieldName]['name'];
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedRules = getAllowedFileRules();

        // Check extension
        if (!array_key_exists($fileExt, $allowedRules)) {
            echo "<script>
                alert('Invalid file type for " . addslashes($fileName) . ". Allowed types: PDF, Word, Excel, CSV, and images.');
                window.history.back();
            </script>";
            exit();
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedRules[$fileExt])) {
            echo "<script>
                alert('Invalid MIME type for " . addslashes($fileName) . ". Detected: " . addslashes($mimeType) . "');
                window.history.back();
            </script>";
            exit();
        }

        return file_get_contents($tmpName);
    }

    // 5. Retrieve all file data
    $files = [
        'prc_file' => getFileContent('prc_file'),
        'file_leave' => getFileContent('file_leave'),
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
        'pds_file' => getFileContent('pds_file'),
        'ICS' => getFileContent('ICS')
    ];

    // 6. SQL insert/update
    $sql = "INSERT INTO employee_documents (
        EmployeeID,
        prc_file,file_leave, dll_file, saln_file, ipcr_file, diploma_file, tor_file, itr_file,
        itr_file_2, service_record_file, appointment_file, trainings_awards_file, pds_file, ICS
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        prc_file = IF(VALUES(prc_file) IS NOT NULL, VALUES(prc_file), prc_file),
        file_leave = IF(VALUES(file_leave) IS NOT NULL, VALUES(file_leave), file_leave),
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
        pds_file = IF(VALUES(pds_file) IS NOT NULL, VALUES(pds_file), pds_file),
        ICS = IF(VALUES(ICS) IS NOT NULL, VALUES(ICS), ICS)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // 7. Bind parameters
    $null = null;
    $stmt->bind_param(
        "ibbbbbbbbbbbbb",
        $EmployeeID,
        $null, // prc_file
        $null,
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
        $null, // pds_file
        $null  // ICS
    );

    // 8. Send blob data
    $fileIndex = 1;
    foreach ($files as $data) {
        if (!is_null($data)) {
            $stmt->send_long_data($fileIndex, $data);
        }
        $fileIndex++;
    }

    // 9. Execute
    if ($stmt->execute()) {
        echo "<script>
            alert('Employee documents saved successfully!');
            window.location.href = '../Employee/documents.php';
        </script>";
    } else {
        echo "<script>
            alert('Error saving documents: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();

} else {
    echo "<h3>Access denied. Please upload through the proper form.</h3>";
}
?>