<?php
session_start();
include "./db.php";

// 1. ตรวจสอบ Login
if (!isset($_SESSION['username'])) {
    header("Location: login.html?error=session_expired");
    exit();
}

// 2. [เพิ่มใหม่] ตรวจสอบ CSRF Token (สำคัญมาก!)
// ต้องเช็คว่าค่าที่ส่งมาจากฟอร์ม ตรงกับค่าที่เก็บใน Session หรือไม่
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // ถ้าไม่ตรง หรือไม่มีการส่งมา ให้หยุดทำงานทันที
    die("Error: Invalid CSRF Token! (คำขอไม่ถูกต้องหรือหมดอายุ)");
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- รับข้อมูลทั่วไป ---
    $crop_type = $_POST['crop_type'];
    $variety = $_POST['variety'];
    
    // --- คำนวณพื้นที่ ---
    $area_value = floatval($_POST['area_value'] ?? 0);
    $area_unit = $_POST['area_unit'] ?? 'rai';
    $area_rai_calculated = ($area_unit == 'sq_wah') ? ($area_value / 400.0) : $area_value;

    // --- รับข้อมูลต้นทุน ---
    $cost_fertilizer = floatval($_POST['cost_fertilizer'] ?? 0);
    $cost_chemicals = floatval($_POST['cost_chemicals'] ?? 0);
    $cost_labor = floatval($_POST['cost_labor'] ?? 0);
    
    // คำนวณต้นทุนรวม
    $total_cost = $cost_fertilizer + $cost_chemicals + $cost_labor;

    // กำหนดสถานะ
    $status = "กำลังเพาะปลูก"; 

    // --- เตรียมตัวแปรสำหรับค่า Null (เพื่อป้องกัน error เวลา bind param) ---
    $cycle_code = null;
    $planting_date = null;
    $planting_method = null;
    $plant_count = null;

    if ($crop_type == 'ข้าว') {
        $cycle_code = $_POST['cycle_code'];
        $planting_date = $_POST['planting_date'];
        $planting_method = $_POST['planting_method'];
    } else if ($crop_type == 'ลำไย' || $crop_type == 'ยางพารา') {
        $plant_count = intval($_POST['plant_count'] ?? 0);
    }

    // --- SQL INSERT ---
    $sql = "INSERT INTO production_cycles 
                (username, crop_type, variety, area_rai, 
                cost_fertilizer, cost_chemicals, cost_labor, total_cost,
                cycle_code, planting_date, planting_method, plant_count, 
                status) 
            VALUES 
                (?, ?, ?, ?, 
                 ?, ?, ?, ?,
                 ?, ?, ?, ?, 
                 ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL Error: " . htmlspecialchars($conn->error));
    }

    // Bind Params
    // s = string, d = double, i = integer
    // ลำดับตัวแปรต้องตรงกับ ? ด้านบนเป๊ะๆ
    $stmt->bind_param("sssdddddsssis", 
                        $username, 
                        $crop_type, 
                        $variety, 
                        $area_rai_calculated,
                        $cost_fertilizer, 
                        $cost_chemicals, 
                        $cost_labor,
                        $total_cost, 
                        $cycle_code, 
                        $planting_date, 
                        $planting_method, 
                        $plant_count,
                        $status);

    if ($stmt->execute()) {
        // บันทึกสำเร็จ
        header("Location: production_list1.php?success=add"); 
        exit();
    } else {
        // บันทึกไม่สำเร็จ
        echo "Error saving data: " . htmlspecialchars($stmt->error);
        echo "<br><br><a href='save-data1.php'>&laquo; กลับไปหน้าฟอร์ม</a>";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index1.php");
    exit();
}
?>