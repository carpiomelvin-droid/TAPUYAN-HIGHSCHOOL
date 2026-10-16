<?php
session_start();
include_once '../db/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

if (!isset($_POST['message_ids']) || !is_array($_POST['message_ids']) || count($_POST['message_ids']) === 0) {
    $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: " . $back . "?status=error&message=" . urlencode("Please select at least one message."));
    exit();
}

$box_type = $_POST['box_type'] ?? '';
$message_ids = array_map('intval', $_POST['message_ids']);
$message_ids = array_filter($message_ids, function ($id) {
    return $id > 0;
});

if (count($message_ids) === 0) {
    $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: " . $back . "?status=error&message=" . urlencode("Invalid message selection."));
    exit();
}

$placeholders = implode(',', array_fill(0, count($message_ids), '?'));

if ($box_type === 'employee_inbox') {
    if (!isset($_SESSION['EmployeeID'])) {
        die("Access denied.");
    }

    $EmployeeID = (int) $_SESSION['EmployeeID'];

    $sql = "UPDATE messages
            SET receiver_deleted = 1
            WHERE receiver_id = ?
              AND receiver_role = 'employee'
              AND message_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $types = 'i' . str_repeat('i', count($message_ids));
    $params = array_merge([$EmployeeID], $message_ids);

} elseif ($box_type === 'employee_sent') {
    if (!isset($_SESSION['EmployeeID'])) {
        die("Access denied.");
    }

    $EmployeeID = (int) $_SESSION['EmployeeID'];

    $sql = "UPDATE messages
            SET sender_deleted = 1
            WHERE sender_id = ?
              AND sender_role = 'employee'
              AND message_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $types = 'i' . str_repeat('i', count($message_ids));
    $params = array_merge([$EmployeeID], $message_ids);

} elseif ($box_type === 'admin_inbox') {
    if (!isset($_SESSION['EmployeeID'])) {
        die("Access denied.");
    }

    $AdminEmployeeID = (int) $_SESSION['EmployeeID'];

    $sql = "UPDATE messages
            SET receiver_deleted = 1
            WHERE receiver_id = ?
              AND receiver_role = 'admin'
              AND message_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $types = 'i' . str_repeat('i', count($message_ids));
    $params = array_merge([$AdminEmployeeID], $message_ids);

} elseif ($box_type === 'admin_sent') {
    if (!isset($_SESSION['EmployeeID'])) {
        die("Access denied.");
    }

    $AdminEmployeeID = (int) $_SESSION['EmployeeID'];

    $sql = "UPDATE messages
            SET sender_deleted = 1
            WHERE sender_id = ?
              AND sender_role = 'admin'
              AND message_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $types = 'i' . str_repeat('i', count($message_ids));
    $params = array_merge([$AdminEmployeeID], $message_ids);

} else {
    die("Invalid message box type.");
}

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();

    $conn->query("DELETE FROM messages WHERE sender_deleted = 1 AND receiver_deleted = 1");

    $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: " . $back . "?status=success&message=" . urlencode("Selected messages deleted."));
    exit();
} else {
    $error = $stmt->error;
    $stmt->close();

    $back = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: " . $back . "?status=error&message=" . urlencode("Delete failed: " . $error));
    exit();
}
?>