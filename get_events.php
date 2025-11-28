<?php
header('Content-Type: application/json');
include "./db.php"; 

// 1. (แนะนำ) ตั้งค่า charset
if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $conn->error);
}

$events = []; 

// --- ส่วนที่ 1: ดึง "กิจกรรมที่บันทึกเอง" ---
$sql_manual = "SELECT id, title, start_date AS start, end_date AS end FROM calendar_events";
$result_manual = mysqli_query($conn, $sql_manual);

// 2. (สำคัญ) ตรวจสอบว่า Query แรกสำเร็จหรือไม่
if ($result_manual === false) {
    // ถ้าพัง, ให้เพิ่มกิจกรรม Error ไปแทน
    $events[] = [
        'title' => 'Error: calendar_events',
        'start' => date('Y-m-d'), // แสดง Error ในวันปัจจุบัน
        'color' => 'red',
        'description' => mysqli_error($conn) // บอกสาเหตุ
    ];
} else {
    while ($row = mysqli_fetch_assoc($result_manual)) {
        $events[] = [
            'title' => $row['title'],
            'start' => $row['start'],
            'end'   => $row['end'],
            'color' => '#007bff', // สีน้ำเงิน
            'url'   => null
        ];
    }
}

// --- ส่วนที่ 2: ดึง "รอบการผลิต" ---
$query_prod = "SELECT id, crop_type, cycle_code, planting_date, status FROM production_cycles";
$result_prod = mysqli_query($conn, $query_prod);

// 3. (สำคัญ) ตรวจสอบว่า Query ที่สองสำเร็จหรือไม่
if ($result_prod === false) {
    // ถ้าพัง, ให้เพิ่มกิจกรรม Error ไปแทน
    $events[] = [
        'title' => 'Error: production_cycles',
        'start' => date('Y-m-d'), // แสดง Error ในวันปัจจุบัน
        'color' => 'red',
        'description' => mysqli_error($conn) // บอกสาเหตุ
    ];
} else {
    while ($row = mysqli_fetch_assoc($result_prod)) {
        $color = ($row['status'] == 'เก็บเกี่ยวแล้ว') ? '#28a745' : '#ffc107'; 
        $events[] = [
            'title' => $row['crop_type'] . ' (' . ($row['cycle_code'] ?: 'N/A') . ')', 
            'start' => $row['planting_date'], 
            'end'   => null,                  
            'color' => $color,                
            'url'   => 'production_detail.php?id=' . $row['id'], 
        ];
    }
}

// --- ส่วนที่ 3: ส่งข้อมูลทั้งหมดกลับไป ---
echo json_encode($events);
mysqli_close($conn);
?>