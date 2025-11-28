<?php
header('Content-Type: application/json'); // ให้ PHP ส่ง JSON

$conn = new mysqli("localhost", "root", "", "test_frong");

// รับ id และแปลงเป็น number ป้องกัน SQL Injectioten
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if($id > 0){
    $sql = "DELETE FROM user WHERE id=$id";
    if($conn->query($sql)){
        echo json_encode(['success'=>true, 'message'=>'ลบสำเร็จ']);
    } else {
        echo json_encode(['success'=>false, 'message'=>'ลบไม่สำเร็จ: '.$conn->error]);
    }
} else {
    echo json_encode(['success'=>false, 'message'=>'id ไม่ถูกต้อง']);
}
?>
