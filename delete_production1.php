<?php
session_start();
include "./db.php";

// 1. ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. ตรวจสอบว่ามีค่า ID ส่งมาหรือไม่
if (isset($_GET['id'])) {
    
    $id = $_GET['id'];
    $username = $_SESSION['username'];

    // 3. เตรียมคำสั่ง SQL (Secure Delete)
    // ใช้ AND username = ? เพื่อมั่นใจว่าลบได้เฉพาะของตัวเองเท่านั้น
    $sql = "DELETE FROM production_cycles WHERE id = ? AND username = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // i = integer (id), s = string (username)
        $stmt->bind_param("is", $id, $username);
        
        if ($stmt->execute()) {
            // ตรวจสอบว่ามีการลบแถวข้อมูลจริงหรือไม่ (affected_rows)
            if ($stmt->affected_rows > 0) {
                // ลบสำเร็จ
                header("Location: production_list1.php?status=deleted");
            } else {
                // คำสั่งทำงาน แต่ไม่มีอะไรถูกลบ (อาจเพราะ ID ผิด หรือ พยายามลบของคนอื่น)
                header("Location: production_list1.php?error=delete_failed");
            }
        } else {
            echo "Error deleting record: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
    
    $conn->close();
    
} else {
    // ถ้าไม่มี ID ส่งมา ให้เด้งกลับไปหน้ารายการ
    header("Location: production_list1.php");
    exit();
}
?>