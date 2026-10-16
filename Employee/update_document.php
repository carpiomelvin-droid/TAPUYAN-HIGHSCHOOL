<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header("Location: ../index.php");
    exit();
}

$EmployeeID = $_SESSION['EmployeeID'];

$allowedColumns = [
    'prc_file' => 'PRC',
    'file_leave' => 'FILE LEAVE',
    'dll_file' => 'DLL',
    'saln_file' => 'SALN',
    'ipcr_file' => 'IPCR',
    'diploma_file' => 'DIPLOMA',
    'tor_file' => 'TOR',
    'itr_file' => 'ITR',
    'itr_file_2' => 'ITR (2nd Copy)',
    'service_record_file' => 'SERVICE RECORD',
    'appointment_file' => 'APPOINTMENT',
    'trainings_awards_file' => 'TRAININGS & AWARDS',
    'pds_file' => 'PDS',
    'ICS' => 'ICS'
];

if (!isset($_GET['file']) || !array_key_exists($_GET['file'], $allowedColumns)) {
    die("Invalid document selected.");
}

$fileColumn = $_GET['file'];
$fileLabel = $allowedColumns[$fileColumn];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Document</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 30px;
        }

        .box {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="file"] {
            margin: 15px 0;
            width: 100%;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="box">
        <h2><?php echo htmlspecialchars($fileLabel); ?> - Update File</h2>

        <form action="process_update_document.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="file_column" value="<?php echo htmlspecialchars($fileColumn); ?>">
            <input type="file" name="new_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*" required>
            <br>
            <button type="submit" class="btn btn-save">Save Update</button>
            <a href="documents.php" class="btn btn-back">Back</a>
        </form>
    </div>
</body>

</html>