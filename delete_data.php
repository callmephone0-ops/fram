<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    
    $record_id = $_GET['delete_id'];
    $username = $_SESSION['username'];

    // ==========================================================
    // ===== 1. (เพิ่ม) ตรวจสอบสิทธิ์แอดมิน (เหมือนเดิม) =====
    // ==========================================================
    
    // *** สมมติฐาน: ตาราง users มีคอลัมน์ role และแอดมินคือ 'admin' ***
    // *** (กรุณาแก้ไข 'role' === 'admin' ให้ตรงกับ DB ของคุณ) ***
    $is_admin = false;
    $stmt_role = $conn->prepare("SELECT role FROM users WHERE username = ?");
    if ($stmt_role) {
        $stmt_role->bind_param("s", $username);
        $stmt_role->execute();
        $res_role = $stmt_role->get_result();
        if ($res_role->num_rows > 0) {
            $user_row = $res_role->fetch_assoc();
            
            // !! (แก้ไขบรรทัดนี้ให้ตรงกับ DB ของคุณ) !!
            $is_admin = ($user_row['role'] === 'admin'); 
        }
        $stmt_role->close();
    }
    // *** จบสมมติฐาน ***
    // ==========================================================


    // ==========================================================
    // ===== 2. (แก้ไข) เตรียม SQL ตามสิทธิ์ =====
    // ==========================================================
    if ($is_admin) {
        // แอดมิน: ลบได้เลยโดยไม่ต้องเช็ก username
        $sql = "DELETE FROM production_cycles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $record_id);

    } else {
        // ผู้ใช้ทั่วไป: ต้องเช็ก username (เหมือนโค้ดเดิมของคุณ)
        $sql = "DELETE FROM production_cycles WHERE id = ? AND username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $record_id, $username);
    }
    // ==========================================================
    
    // รันคำสั่ง
    if ($stmt->execute()) {
        // สำเร็จ: กลับไปหน้า list
        if ($stmt->affected_rows > 0) {
            header("Location: data_list.php?status=deleted");
        } else {
            // (แก้ไข) แจ้งเตือนให้ชัดเจนขึ้น
            // ไม่เจอแถว (อาจจะ ID ผิด หรือพยายามลบของคนอื่น และคุณไม่ใช่แอดมิน)
            echo "ไม่พบข้อมูล หรือคุณไม่มีสิทธิ์ลบ (affected_rows = 0)";
        }
    } else {
        // ล้มเหลว: แสดง error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // ถ้าไม่มี ID ส่งมา
    header("Location: data_list.php");
}
exit();
?>