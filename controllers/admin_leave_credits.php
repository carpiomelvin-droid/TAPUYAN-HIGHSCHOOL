<?php
session_start();
include_once '../db/conn.php';
/** @var mysqli $conn */

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$back = '../admin/leave_credits.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $back);
    exit();
}

function fail_credits(string $message): void
{
    header('Location: ../admin/leave_credits.php?status=error&message=' . urlencode($message));
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS employee_leave_credits (
    credit_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EmployeeID INT NOT NULL,
    leave_year YEAR NOT NULL,
    vacation_entitlement DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    sick_entitlement DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    other_entitlement DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (credit_id),
    UNIQUE KEY uq_employee_leave_year (EmployeeID, leave_year),
    KEY idx_leave_credits_employee (EmployeeID),
    CONSTRAINT fk_leave_credits_employee FOREIGN KEY (EmployeeID) REFERENCES admin(EmployeeID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$year = (int) ($_POST['leave_year'] ?? date('Y'));
if ($year < 2000 || $year > 2100) {
    fail_credits('Invalid leave year.');
}

$credits = $_POST['credits'] ?? null;
if (!is_array($credits) || empty($credits)) {
    fail_credits('No leave balances to save.');
}

$upsert = $conn->prepare("INSERT INTO employee_leave_credits (EmployeeID, leave_year, vacation_entitlement, sick_entitlement, other_entitlement) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE vacation_entitlement = VALUES(vacation_entitlement), sick_entitlement = VALUES(sick_entitlement), other_entitlement = VALUES(other_entitlement)");
if (!$upsert) {
    fail_credits('Unable to prepare the leave balance update.');
}

$checkStaff = $conn->prepare("SELECT EmployeeID FROM admin WHERE EmployeeID = ? AND role = 'staff' LIMIT 1");
if (!$checkStaff) {
    fail_credits('Unable to validate employees.');
}

$saved = 0;
$conn->begin_transaction();
try {
    foreach ($credits as $employeeId => $values) {
        $employeeId = (int) $employeeId;
        if ($employeeId <= 0 || !is_array($values)) {
            continue;
        }
        $checkStaff->bind_param('i', $employeeId);
        $checkStaff->execute();
        if (!$checkStaff->get_result()->fetch_assoc()) {
            continue;
        }
        $vacation = $sick = $other = null;
        foreach (['vacation' => &$vacation, 'sick' => &$sick, 'other' => &$other] as $key => &$target) {
            $raw = trim((string) ($values[$key] ?? ''));
            if ($raw === '' || !is_numeric($raw)) {
                throw new Exception('Enter a valid number for every leave balance.');
            }
            $target = round((float) $raw, 2);
            if ($target < 0 || $target > 365) {
                throw new Exception('Leave balances must be between 0 and 365 days.');
            }
        }
        unset($target);
        $upsert->bind_param('iiddd', $employeeId, $year, $vacation, $sick, $other);
        if (!$upsert->execute()) {
            throw new Exception('Unable to save leave balances.');
        }
        $saved++;
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    $upsert->close();
    $checkStaff->close();
    fail_credits($e->getMessage());
}
$upsert->close();
$checkStaff->close();

if ($saved === 0) {
    fail_credits('No valid staff leave balances were found to save.');
}
header('Location: ' . $back . '?status=success&message=' . urlencode("Leave balances saved for {$saved} employee(s) covering {$year}."));
exit();
