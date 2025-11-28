<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require './db.php';

// 1. ตรวจสอบ Session
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// 2. ดึงข้อมูลจาก Form
$title = $_POST['title'] ?? null;
$start = $_POST['start'] ?? null;
$end = $_POST['end'] ?? null;
$all_day = isset($_POST['all_day']) ? 1 : 0;
// $color = $_POST['color'] ?? '#3788d8'; // (แก้ไข) ลบบรรทัดนี้ทิ้ง

// 3. (สำคัญ!) ตรวจสอบ Checkbox กิจกรรมส่วนกลาง
$is_global = isset($_POST['is_global']) && $_POST['is_global'] === '1';

// (แก้ไข) บังคับให้กิจกรรมที่แอดมินสร้าง (ทั้งส่วนตัวและส่วนกลาง) เป็นสีเทา
$color = '#6c757d'; 

$username_to_insert = null;

if ($is_global) {
    // ถ้าติ๊ก "ประกาศ" (is_global) ให้ตั้ง username เป็น NULL
    $username_to_insert = null;
} else {
    // ถ้าไม่ติ๊ก ให้บันทึกเป็นกิจกรรมส่วนตัวของแอดมิน
    $username_to_insert = $_SESSION['username'];
}

// 4. ตรวจสอบข้อมูล
if (empty($title) || empty($start)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อกิจกรรมและวันเริ่มต้น']);
    exit();
}

// 5. แปลง Format วันที่ (จาก datetime-local)
try {
    $start_sql = date('Y-m-d H:i:s', strtotime($start));
    $end_sql = !empty($end) ? date('Y-m-d H:i:s', strtotime($end)) : null;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง']);
    exit();
}


// 6. บันทึกลงฐานข้อมูล (สมมติว่าตารางชื่อ 'calendar_events')
$sql = "INSERT INTO calendar_events (username, title, start_date, end_date, all_day, color) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
// ประเภทข้อมูล: s (username), s (title), s (start_date), s (end_date), i (all_day), s (color)
$stmt->bind_param("ssssis", $username_to_insert, $title, $start_sql, $end_sql, $all_day, $color);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'inserted_id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>