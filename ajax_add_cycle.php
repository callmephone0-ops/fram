<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาล็อกอินก่อน']);
    exit();
}

include "./db.php";

$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาป้อนชื่อรอบการผลิต']);
    exit();
}

// ป้องกันการเพิ่มข้อมูลซ้ำ
$stmt_check = $conn->prepare("SELECT id FROM rice_production_cycles WHERE cycle_name = ?");
$stmt_check->bind_param("s", $name);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'มีชื่อรอบการผลิตนี้แล้ว']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

// บันทึกลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO rice_production_cycles (cycle_name) VALUES (?)");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'id' => $new_id,
        'name' => $name
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>