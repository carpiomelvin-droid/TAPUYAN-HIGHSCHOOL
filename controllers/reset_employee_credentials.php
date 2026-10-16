<?php
session_start();
include_once '../db/conn.php';

$targetEmployeeId = (int) ($_POST['EmployeeID'] ?? 0);
$adminId = (int) ($_SESSION['EmployeeID'] ?? 0);
$redirect = '../admin/viewemployee.php?id=' . $targetEmployeeId;

function resetRedirect(string $redirect): never
{
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $adminId <= 0 || $targetEmployeeId <= 0) {
    resetRedirect($redirect);
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''))) {
    resetRedirect($redirect);
}

$roleStmt = $conn->prepare('SELECT role FROM admin WHERE EmployeeID = ? LIMIT 1');
if (!$roleStmt) {
    resetRedirect($redirect);
}
$roleStmt->bind_param('i', $adminId);
$roleStmt->execute();
$roleRow = $roleStmt->get_result()->fetch_assoc();
$roleStmt->close();

if (!$roleRow || $roleRow['role'] !== 'superadmin') {
    resetRedirect($redirect);
}

if ($targetEmployeeId === $adminId) {
    resetRedirect($redirect);
}

$username = trim((string) ($_POST['username'] ?? ''));
$newPassword = (string) ($_POST['new_password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    resetRedirect($redirect);
}

if (strlen($newPassword) < 6) {
    resetRedirect($redirect);
}

if ($newPassword !== $confirmPassword) {
    resetRedirect($redirect);
}

$targetStmt = $conn->prepare("SELECT EmployeeID FROM admin WHERE EmployeeID = ? AND role = 'staff' LIMIT 1");
if (!$targetStmt) {
    resetRedirect($redirect);
}
$targetStmt->bind_param('i', $targetEmployeeId);
$targetStmt->execute();
$targetExists = $targetStmt->get_result()->num_rows === 1;
$targetStmt->close();

if (!$targetExists) {
    resetRedirect($redirect);
}

$usernameStmt = $conn->prepare('SELECT EmployeeID FROM admin WHERE username = ? AND EmployeeID <> ? LIMIT 1');
if (!$usernameStmt) {
    resetRedirect($redirect);
}
$usernameStmt->bind_param('si', $username, $targetEmployeeId);
$usernameStmt->execute();
$usernameTaken = $usernameStmt->get_result()->num_rows > 0;
$usernameStmt->close();

if ($usernameTaken) {
    resetRedirect($redirect);
}

$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE EmployeeID = ? AND role = 'staff'");
if (!$updateStmt) {
    resetRedirect($redirect);
}
$updateStmt->bind_param('ssi', $username, $passwordHash, $targetEmployeeId);

if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
    $updateStmt->close();
    resetRedirect($redirect);
}

$updateStmt->close();
$conn->close();
resetRedirect($redirect);
?>