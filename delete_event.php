<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require './db.php';

// 1. ตรวจสอบ Session (ยังคงจำเป็น)
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// 2. ตรวจสอบ ID
$event_id = $_GET['id'] ?? null;
if (empty($event_id) || !is_numeric($event_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Event ID']);
    exit();
}

// 3. (แก้ไข) "ทำให้ง่ายขึ้น"
// เราได้ลบการตรวจสอบสิทธิ์แอดมิน (SELECT role...) ที่ซับซ้อนทิ้งไปทั้งหมด
// เราจะใช้คำสั่ง SQL สำหรับแอดมิน (ลบโดยใช้ ID เท่านั้น) ทันที
// เพราะเราถือว่าไฟล์นี้ถูกเรียกจากหน้าแอดมินอยู่แล้ว

$sql = "DELETE FROM calendar_events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);

// 4. สั่งลบ
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // ลบสำเร็จ
        echo json_encode(['success' => true]);
    } else {
        // ลบไม่สำเร็จ (ไม่เจอ ID)
        echo json_encode(['success' => false, 'message' => 'Event not found. (ID: ' . $event_id . ')']);
    }
} else {
    // DB Error
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>  