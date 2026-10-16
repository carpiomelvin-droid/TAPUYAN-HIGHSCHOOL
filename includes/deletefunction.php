<?php
/* * This file (includes/functions.php) will hold reusable functions.
 */

/**
 * Deletes an employee from all related tables using a transaction.
 *
 * @param mysqli $conn The database connection object.
 * @param int $EmployeeID The ID of the employee to delete.
 * @return bool True on success, false on failure.
 */
function deleteEmployee($conn, $EmployeeID)
{
    $EmployeeID = (int) $EmployeeID;
    if ($EmployeeID <= 0) {
        return false;
    }

    if (!$conn->begin_transaction()) {
        return false;
    }

    try {
        // Check the account first so a missing ID is not reported as a success.
        $checkStmt = $conn->prepare("SELECT EmployeeID FROM admin WHERE EmployeeID = ? LIMIT 1");
        if (!$checkStmt) {
            throw new RuntimeException($conn->error);
        }
        $checkStmt->bind_param("i", $EmployeeID);
        if (!$checkStmt->execute() || !$checkStmt->get_result()->fetch_assoc()) {
            $checkStmt->close();
            throw new RuntimeException('Employee account not found.');
        }
        $checkStmt->close();

        // Remove dependent rows explicitly; the foreign keys also cascade these rows.
        foreach (['employee_details', 'employee_documents'] as $table) {
            $stmt = $conn->prepare("DELETE FROM {$table} WHERE EmployeeID = ?");
            if (!$stmt) {
                throw new RuntimeException($conn->error);
            }
            $stmt->bind_param("i", $EmployeeID);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                throw new RuntimeException($error);
            }
            $stmt->close();
        }

        $accountStmt = $conn->prepare("DELETE FROM admin WHERE EmployeeID = ?");
        if (!$accountStmt) {
            throw new RuntimeException($conn->error);
        }
        $accountStmt->bind_param("i", $EmployeeID);
        if (!$accountStmt->execute() || $accountStmt->affected_rows !== 1) {
            $error = $accountStmt->error ?: 'Employee account could not be deleted.';
            $accountStmt->close();
            throw new RuntimeException($error);
        }
        $accountStmt->close();

        $conn->commit();
        return true;
    } catch (Throwable $exception) {
        $conn->rollback();
        return false;
    }
}

// You can add other functions here later...
