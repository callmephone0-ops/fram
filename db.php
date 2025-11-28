<?php 
$servername = "localhost"; 
$username = "root";   // แก้ตามของคุณ 
$password = "";       // รหัสผ่าน MySQL
$dbname = "test_frong";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

?>