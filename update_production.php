<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. ตรวจสอบว่าเป็น POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. รับข้อมูลจากฟอร์ม (ต้องรับ ID มาด้วย)
    $id = $_POST['id'];
    $crop_type = $_POST['crop_type'];
    $variety = $_POST['variety'];
    $cycle_code = $_POST['cycle_code'];
    $planting_date = $_POST['planting_date'];
    $status = $_POST['status'];
    $area_rai = $_POST['area_rai'];
    $cost_fertilizer = $_POST['cost_fertilizer'];
    $cost_chemicals = $_POST['cost_chemicals'];
    $cost_labor = $_POST['cost_labor'];

    // 3. สร้าง SQL UPDATE (ใช้ Prepared Statements)
    $sql = "UPDATE production_cycles SET
                crop_type = ?,
                variety = ?,
                cycle_code = ?,
                planting_date = ?,
                status = ?,
                area_rai = ?,
                cost_fertilizer = ?,
                cost_chemicals = ?,
                cost_labor = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    
    // 4. Bind Parameters (ระวังประเภทข้อมูล s = string, d = double, i = integer)
    // s, s, s, s, s, d, d, d, d, i
    $stmt->bind_param("sssssddddi", 
        $crop_type, 
        $variety, 
        $cycle_code, 
        $planting_date, 
        $status, 
        $area_rai, 
        $cost_fertilizer, 
        $cost_chemicals, 
        $cost_labor, 
        $id
    );

    // 5. Execute และตรวจสอบ
    if ($stmt->execute()) {
        // อัปเดตสำเร็จ กลับไปหน้า list
        header("Location: all_production_list.php?update=success"); 
    } else {
        // เกิดข้อผิดพลาด
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST
    header("Location: all_production_list.php");
}
exit();
?>