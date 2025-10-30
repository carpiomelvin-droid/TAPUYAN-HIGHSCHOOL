<?php

session_start();
include_once '../db/conn.php';

if (isset($_SESSION['username'])) {
    session_unset();
    session_destroy();
    echo "<script>alert('Do you really want to log out?Press ESC to cancel');window.location.href='../index.php';</script>";
} else {
    exit();
}
