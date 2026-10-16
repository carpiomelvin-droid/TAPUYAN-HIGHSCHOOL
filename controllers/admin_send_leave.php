<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$back = '../admin/leave_admin.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $back);
    exit();
}

$leaveId = (int) ($_POST['leave_id'] ?? 0);
if ($leaveId <= 0) {
    header('Location: ' . $back . '?status=error&message=' . urlencode('Invalid leave document.'));
    exit();
}

$stmt = $conn->prepare("UPDATE leave_records SET status = 'approved' WHERE leave_id = ? AND status = 'pending'");
$stmt->bind_param('i', $leaveId);
$stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();

if ($updated > 0) {
    header('Location: ' . $back . '?status=success&message=' . urlencode('Leave document sent to the employee.'));
} else {
    header('Location: ' . $back . '?status=error&message=' . urlencode('Leave document was not found or has already been sent.'));
}
exit();
