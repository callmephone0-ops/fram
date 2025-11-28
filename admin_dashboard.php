<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
echo "ยินดีต้อนรับ Admin: " . $_SESSION['username'];
?>
