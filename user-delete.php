<?php
session_start();
include "./db.php";

// 1. ตรวจสอบ Session และต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ดำเนินการ']);
    exit();
}

// 2. ตรวจสอบว่าส่งมาแบบ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID ไม่ถูกต้อง']);
        exit();
    }

    // (ป้องกัน) อย่าให้ Admin ลบตัวเอง
    // (สมมติว่า id ของ admin ที่ login อยู่ถูกเก็บใน session)
    // if ($id == $_SESSION['user_id']) { 
    //     echo json_encode(['success' => false, 'message' => 'คุณไม่สามารถลบตัวเองได้']);
    //     exit();
    // }

    // 3. ใช้ Prepared Statements เพื่อลบ
    $sql = "DELETE FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'ลบผู้ใช้สำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้ที่ต้องการลบ']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>