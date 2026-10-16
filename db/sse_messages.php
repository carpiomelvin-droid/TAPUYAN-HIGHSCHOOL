<?php
session_start();
include_once 'conn.php';

$EmployeeID = $_SESSION['EmployeeID'] ?? 0;

$role = 'admin';
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'staff' || $_SESSION['role'] === 'employee')) {
    $role = 'employee';
}

session_write_close();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) {
    @ob_end_clean();
}

$last_count = -1;

while (true) {
    if ($EmployeeID > 0) {
        $conn->query("SELECT 1");

        // 🟢 FIX: Added AND receiver_deleted = 0
        $sql = "SELECT COUNT(*) AS unread_count
                FROM messages
                WHERE receiver_id = ?
                  AND receiver_role = ?
                  AND is_read = 0
                  AND receiver_deleted = 0";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $EmployeeID, $role);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $current_count = (int) ($row['unread_count'] ?? 0);
            $stmt->close();

            if ($current_count !== $last_count) {
                echo "data: " . $current_count . "\n\n";
                @ob_flush();
                @flush();
                $last_count = $current_count;
            }
        }
    }
    sleep(2);
}