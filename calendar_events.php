<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

require './db.php';

$username = $_SESSION['username'];
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'user';
$isAll = ($scope === 'all');

// --------------- 1) อีเวนต์ที่ผู้ใช้สร้างเอง (calendar_events) ---------------
if ($isAll) {
  // แอดมินเห็นทุกคน (รวมถึงของแอดมินเองที่เป็น NULL)
  $sql = "SELECT id, username, title, start_date, end_date, all_day, color
          FROM calendar_events
          ORDER BY start_date ASC";
  $stmt = $conn->prepare($sql);
} else {
  // ผู้ใช้เห็นของตัวเอง + ของแอดมิน (username IS NULL)
  $sql = "SELECT id, username, title, start_date, end_date, all_day, color
          FROM calendar_events
          WHERE username = ? OR username IS NULL
          ORDER BY start_date ASC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $username);
}

$stmt->execute();
$res = $stmt->get_result();

$events = [];
while ($row = $res->fetch_assoc()) {
  $title = $row['title'];
  $is_admin_event = is_null($row['username']);

  if ($isAll && !$is_admin_event) {
    $title = '['.$row['username'].'] '.$row['title'];
  }

  $color = $is_admin_event
      ? '#6c757d' // สีเทา (กิจกรรมแอดมิน)
      : ($row['color'] ?: '#3788d8'); // สีของผู้ใช้

  $events[] = [
    'id' => $row['id'],
    'title' => $title,
    'start' => date('c', strtotime($row['start_date'])),
    'end'   => $row['end_date'] ? date('c', strtotime($row['end_date'])) : null,
    'allDay' => (bool)$row['all_day'],
    'backgroundColor' => $color,
    'borderColor' => $color,
    
    // (สำคัญ!) ล็อก! ถ้าเป็นกิจกรรมแอดมิน "และ" คนที่ดูไม่ใช่แอดมิน
    'locked' => ($is_admin_event && !$isAll), 
    
    'source' => 'manual'
  ];
}
$stmt->close();

// --------------- 2) อีเวนต์ระบบจาก production_cycles ---------------
if ($isAll) {
  // แอดมินเห็นทุกคน
  $sql2 = "SELECT username, crop_type, cycle_code, planting_date, harvest_date
           FROM production_cycles";
  $stmt2 = $conn->prepare($sql2);
} else {
  // ผู้ใช้เห็นเฉพาะของตนเอง
  $sql2 = "SELECT username, crop_type, cycle_code, planting_date, harvest_date
           FROM production_cycles
           WHERE username = ?";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param('s', $username);
}

$stmt2->execute();
$res2 = $stmt2->get_result();

while ($r = $res2->fetch_assoc()) {
  $owner = $r['username'];
  $code  = $r['cycle_code'] ?: '-';
  $crop  = $r['crop_type'] ?: 'พืช';
  $prefix = $isAll ? '['.$owner.'] ' : '';

  if (!empty($r['planting_date'])) {
    $events[] = [
      'id' => 'pc-plant-'.$code,
      'title' => $prefix.'เริ่มเพาะปลูก: '.$crop.' ('.$code.')',
      'start' => date('c', strtotime($r['planting_date'])),
      'allDay' => true,
      'backgroundColor' => '#28a745',
      'borderColor' => '#28a745',
      'locked' => true, // ล็อกเสมอ (ข้อมูลระบบ)
      'source' => 'production_cycles'
    ];
  }

  if (!empty($r['harvest_date'])) {
    $events[] = [
      'id' => 'pc-harv-'.$code,
      'title' => $prefix.'วันเก็บเกี่ยว: '.$crop.' ('.$code.')',
      'start' => date('c', strtotime($r['harvest_date'])),
      'allDay' => true,
      'backgroundColor' => '#ffc107',
      'borderColor' => '#ffc107',
      'locked' => true, // ล็อกเสมอ (ข้อมูลระบบ)
      'source' => 'production_cycles'
    ];
  }
}
$stmt2->close();

$conn->close();
echo json_encode($events);
?>