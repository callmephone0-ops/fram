<?php
session_start();
include "./db.php";

// 1. ตรวจสอบว่า Login หรือไม่
if (!isset($_SESSION['username'])) {
    die("Access Denied. Please login.");
}

// 2. ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. ดึงข้อมูลจากฟอร์ม
    $crop_type = mysqli_real_escape_string($conn, $_POST['crop_type']);
    $variety = mysqli_real_escape_string($conn, $_POST['variety']);
    $cycle_code = mysqli_real_escape_string($conn, $_POST['cycle_code']);
    $planting_date = mysqli_real_escape_string($conn, $_POST['planting_date']);
    $plant_count = (int)$_POST['plant_count'];
    $planting_method = mysqli_real_escape_string($conn, $_POST['planting_method']);
    $area_rai = (float)$_POST['area_rai'];

    // === VVVV นี่คือส่วนที่แก้ไข VVVV ===

    // 4. รับค่าต้นทุน (แปลงเป็นตัวเลขทศนิยม)
    $cost_fertilizer = (float)$_POST['cost_fertilizer'];
    $cost_chemicals = (float)$_POST['cost_chemicals'];
    $cost_labor = (float)$_POST['cost_labor'];

    // 5. คำนวณต้นทุนรวม (Total Cost)
    $total_cost = $cost_fertilizer + $cost_chemicals + $cost_labor;

    // === AAAA นี่คือส่วนที่แก้ไข AAAA ===

    // 6. ดึง username จาก session เพื่อบันทึกว่าใครเป็นคนเพิ่ม
    $username = $_SESSION['username'];
    
    // 7. ตั้งค่าสถานะเริ่มต้น
    $status = 'กำลังเพาะปลูก'; // (อ้างอิงจาก Comment ในฐานข้อมูลของคุณ)

    // 8. สร้างคำสั่ง SQL (ใช้ Prepared Statements เพื่อความปลอดภัย)
    $sql = "INSERT INTO production_cycles 
                (username, crop_type, variety, area_rai, 
                 cost_fertilizer, cost_chemicals, cost_labor, total_cost, 
                 cycle_code, planting_date, planting_method, plant_count, status) 
            VALUES 
                (?, ?, ?, ?, 
                 ?, ?, ?, ?, 
                 ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    
    // 9. ผูกค่าตัวแปรกับ SQL (Type: s=string, d=double, i=integer)
    // sss ddd d ssi s
    mysqli_stmt_bind_param($stmt, "sssddddsssiss", 
        $username, $crop_type, $variety, $area_rai,
        $cost_fertilizer, $cost_chemicals, $cost_labor, $total_cost,
        $cycle_code, $planting_date, $planting_method, $plant_count, $status
    );

    // 10. สั่ง execute และตรวจสอบผล
    if (mysqli_stmt_execute($stmt)) {
        // ถ้าสำเร็จ ให้เด้งกลับไปหน้ารายการ
        header("Location: production_list.php?save=success");
        exit();
    } else {
        // ถ้าไม่สำเร็จ ให้แสดง Error
        die("Error: " . mysqli_stmt_error($stmt));
    }

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST ให้เด้งกลับ
    header("Location: production_form.php");
    exit();
}
?>