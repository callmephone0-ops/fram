<?php
session_start();

// ลบ session ทั้งหมด
session_unset();

// ทำลาย session
session_destroy();

// redirect กลับไปหน้า login หรือ index
header("Location: ./login.php");
exit();
?>
