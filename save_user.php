<?php
header('Content-Type: application/json');

// เชื่อมต่อ DB
$conn = new mysqli("localhost", "root", "", "test_frong"); // ปรับ host/user/pass/dbname
if($conn->connect_error){
    echo json_encode(['success'=>false,'message'=>'Database connection failed']);
    exit;
}

// รับข้อมูล
$username = $conn->real_escape_string($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$firstname = $conn->real_escape_string($_POST['firstname'] ?? '');
$lastname = $conn->real_escape_string($_POST['lastname'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : null;


if(!$username || !$password || !$firstname || !$lastname || !$status){
    echo json_encode(['success'=>false,'message'=>'กรุณากรอกข้อมูลให้ครบ']);
    exit;
}

// ตรวจสอบ username ซ้ำ
$sql_check = "SELECT * FROM user WHERE username='$username'";
$result = $conn->query($sql_check);
if($result->num_rows > 0){
    echo json_encode(['success'=>false,'message'=>'Username ซ้ำ! กรุณาใช้ Username อื่น']);
    exit;
}

// เข้ารหัส password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// บันทึกข้อมูล
$sql_insert = "INSERT INTO user (username,password,name,surname,status)
               VALUES ('$username','$hashed_password','$firstname','$lastname','$status')";

if($conn->query($sql_insert)){
    echo json_encode(['success'=>true,'message'=>"บันทึกสำเร็จ!<br>Username: $username<br>ชื่อ: $firstname $lastname<br>สถานะ: $status"]);
}else{
    echo json_encode(['success'=>false,'message'=>'บันทึกไม่สำเร็จ: '.$conn->error]);
}

$conn->close();
?>
