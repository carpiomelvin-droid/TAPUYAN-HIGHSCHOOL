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

    // Start a transaction
    $conn->autocommit(FALSE);
    $success = true; // Flag to track success

    // 1. Delete from 'admin' table
    $stmt1 = $conn->prepare("DELETE FROM admin WHERE EmployeeID = ?");
    $stmt1->bind_param("i", $EmployeeID);
    if (!$stmt1->execute()) $success = false;
    $stmt1->close();

    // 2. Delete from 'employee_details' table
    $stmt2 = $conn->prepare("DELETE FROM employee_details WHERE EmployeeID = ?");
    $stmt2->bind_param("i", $EmployeeID);
    if (!$stmt2->execute()) $success = false;
    $stmt2->close();

    // 3. Delete from 'employee_documents' table
    $stmt3 = $conn->prepare("DELETE FROM employee_documents WHERE EmployeeID = ?");
    $stmt3->bind_param("i", $EmployeeID);
    if (!$stmt3->execute()) $success = false;
    $stmt3->close();

    // --- 4. Commit or Rollback ---
    if ($success) {
        $conn->commit(); // All deletes were successful
        return true;
    } else {
        $conn->rollback(); // Something failed, undo all changes
        return false;
    }
}

// You can add other functions here later...
