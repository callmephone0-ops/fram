<?php
session_start();
include "./db.php"; // ใช้ไฟล์เชื่อมต่อฐานข้อมูลเดิม

// ตรวจสอบว่า login หรือไม่ (เพื่อความปลอดภัย)
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$variety_id = $_POST['id'] ?? 0;

if (empty($variety_id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้ระบุ ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM crop_varieties WHERE id = ?");
$stmt->bind_param("i", $variety_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>