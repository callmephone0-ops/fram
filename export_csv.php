<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. (แก้ไข) ดึง username ที่ล็อกอินอยู่
$current_username = $_SESSION['username'];

// 2. (แก้ไข) "เพิ่ม" AND username = ? เพื่อกรอง "ตู้ของใครของมัน"
$sql_recent = "SELECT * FROM production_cycles 
               WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ? 
               ORDER BY harvest_date DESC";

// 3. (แก้ไข) ใช้ Prepared Statements เพื่อความปลอดภัย
$stmt = $conn->prepare($sql_recent);
$stmt->bind_param("s", $current_username);
$stmt->execute();
$result = $stmt->get_result();

// Set headers to trigger file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=production_report.csv');

// Open file pointer to output stream
$output = fopen('php://output', 'w');

// Add BOM to support UTF-8 in Excel
fputs($output, "\xEF\xBB\xBF");

// Add table headers
fputcsv($output, ['ชนิดพืช', 'พันธุ์/รอบการผลิต', 'วันที่เก็บเกี่ยว', 'รายรับ (บาท)', 'ต้นทุน (บาท)', 'กำไร (บาท)']);

// Write data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        // 4. (แก้ไข) ตรวจสอบวันที่ก่อนแสดงผล
        $harvest_date_str = '-'; // ตั้งค่าเริ่มต้นเป็นขีด
        if (!empty($row['harvest_date'])) {
            $timestamp = strtotime($row['harvest_date']);
            // ตรวจสอบว่าวันที่ไม่เป็น 0000-00-00 หรือ NULL
            if ($timestamp > 0) { 
                $harvest_date_str = date("d/m/Y", $timestamp);
            }
        }
        
        fputcsv($output, [
            $row['crop_type'],
            $row['cycle_code'] ?: $row['variety'],
            $harvest_date_str, // <-- ใช้ตัวแปรที่ตรวจสอบแล้ว
            $row['total_revenue'],
            $row['total_cost'],
            $row['profit']
        ]);
    }
}

$stmt->close(); // ปิด statement
fclose($output);
exit();
?>