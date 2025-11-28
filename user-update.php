<?php
session_start();
// 1. ตรวจสอบ Session และต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "./db.php";

// 2. ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST (จากฟอร์ม) หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. รับค่าทั้งหมดจากฟอร์ม
    $user_id = $_POST['id'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $status = $_POST['status'];
    $role = $_POST['role'];

    // 4. เตรียมคำสั่ง SQL เพื่อ Update
    $sql = "UPDATE user SET name = ?, surname = ?, username = ?, status = ?, role = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // 5. ผูกตัวแปร (sssisi = string, string, string, integer, string, integer)
    $stmt->bind_param("sssisi", $name, $surname, $username, $status, $role, $user_id);

    // 6. สั่ง Execute (ทำการอัปเดต)
    if ($stmt->execute()) {
        // ถ้าสำเร็จ: ให้เด้งกลับไปหน้า admin-user.php
        header("Location: admin-user.php?status=update_success");
        exit();
    } else {
        // ถ้าไม่สำเร็จ: แสดง error
        echo "Error updating record: " + $stmt->error;
    }

    // 7. ปิด statement
    $stmt->close();

} else {
    // 
    // ถ้ามีคนพยายามเปิดไฟล์นี้ตรงๆ (แบบ GET)
    // มันจะแสดงข้อความนี้ ซึ่งเป็นสิ่งที่คุณเจออยู่
    //
    echo "Invalid request method.";
    echo "<br>กรุณากลับไปที่หน้าฟอร์มแก้ไข และกดปุ่ม 'บันทึก' แทนการเปิดหน้านี้โดยตรง";
}

// 8. ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>