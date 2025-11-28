<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}
echo "ยินดีต้อนรับ User: " . $_SESSION['username'];
?>
