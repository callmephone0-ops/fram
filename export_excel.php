<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// Autoload PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch data
$sql_recent = "SELECT * FROM production_cycles 
               WHERE status = 'เก็บเกี่ยวแล้ว' 
               ORDER BY harvest_date DESC";
$result = $conn->query($sql_recent);

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Headers
$sheet->setCellValue('A1', 'ชนิดพืช');
$sheet->setCellValue('B1', 'พันธุ์/รอบการผลิต');
$sheet->setCellValue('C1', 'วันที่เก็บเกี่ยว');
$sheet->setCellValue('D1', 'รายรับ (บาท)');
$sheet->setCellValue('E1', 'ต้นทุน (บาท)');
$sheet->setCellValue('F1', 'กำไร (บาท)');

// Write data rows
$row_num = 2;
if ($result->num_rows > 0) {
    while ($row_data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row_num, $row_data['crop_type']);
        $sheet->setCellValue('B' . $row_num, $row_data['cycle_code'] ?: $row_data['variety']);
        $sheet->setCellValue('C' . $row_num, date("d/m/Y", strtotime($row_data['harvest_date'])));
        $sheet->setCellValue('D' . $row_num, $row_data['total_revenue']);
        $sheet->setCellValue('E' . $row_num, $row_data['total_cost']);
        $sheet->setCellValue('F' . $row_num, $row_data['profit']);
        $row_num++;
    }
}

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="production_report.xlsx"');
header('Cache-Control: max-age=0');

// Create the writer and save to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>