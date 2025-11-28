<?php
session_start();
// ตรวจสอบการล็อกอิน (ควรทำ)
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาล็อกอินก่อน']);
    exit();
}

include "./db.php";

// 1. รับค่าที่ส่งมาจาก AJAX
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$crop_type = isset($_POST['crop_type']) ? trim($_POST['crop_type']) : '';

// 2. ตรวจสอบข้อมูลเบื้องต้น
if (empty($name) || empty($crop_type)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

// 3. ป้องกันการเพิ่มข้อมูลซ้ำ
$stmt_check = $conn->prepare("SELECT id FROM crop_varieties WHERE variety_name = ? AND crop_type = ?");
$stmt_check->bind_param("ss", $name, $crop_type);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'มีชื่อสายพันธุ์นี้ในระบบแล้ว']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();


// 4. บันทึกลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO crop_varieties (variety_name, crop_type) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $crop_type);

if ($stmt->execute()) {
    // 5. ส่งข้อมูลกลับไปให้ JavaScript (สำคัญมาก)
    $new_id = $conn->insert_id; // เอา ID ที่เพิ่งสร้าง
    echo json_encode([
        'success' => true,
        'id' => $new_id,
        'name' => $name // ส่งชื่อที่บันทึกสำเร็จกลับไป
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>