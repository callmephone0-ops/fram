<?php
session_start();
include "./db.php";

// 1. ตรวจสอบ Login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. รับค่าจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id = $_POST['id'];
    $username = $_SESSION['username'];
    $crop_type = $_POST['crop_type'];
    $variety = $_POST['variety'];
    $planting_date = $_POST['planting_date'];
    
    // รับค่าพื้นที่ (ถ้ามีการแก้ไข)
    $area_rai = floatval($_POST['area_rai']); 

    // รับค่าต้นทุน
    $cost_fertilizer = floatval($_POST['cost_fertilizer']);
    $cost_chemicals = floatval($_POST['cost_chemicals']);
    $cost_labor = floatval($_POST['cost_labor']);
    
    // คำนวณต้นทุนรวมใหม่
    $total_cost = $cost_fertilizer + $cost_chemicals + $cost_labor;

    // ตัวแปรเฉพาะพืช
    $cycle_code = isset($_POST['cycle_code']) ? $_POST['cycle_code'] : null;
    $planting_method = isset($_POST['planting_method']) ? $_POST['planting_method'] : null;
    $plant_count = isset($_POST['plant_count']) ? intval($_POST['plant_count']) : null;

    // 3. อัปเดตข้อมูลลงฐานข้อมูล
    // อัปเดตเฉพาะข้อมูลของ User คนนี้เท่านั้น (AND username = ?) เพื่อความปลอดภัย
    $sql = "UPDATE production_cycles SET 
            variety = ?, 
            planting_date = ?, 
            area_rai = ?, 
            cost_fertilizer = ?, 
            cost_chemicals = ?, 
            cost_labor = ?, 
            total_cost = ?, 
            cycle_code = ?, 
            planting_method = ?, 
            plant_count = ?
            WHERE id = ? AND username = ?";

    $stmt = $conn->prepare($sql);
    
    // s = string, d = double, i = integer
    // เรียงลำดับ: variety(s), date(s), area(d), cost1(d), cost2(d), cost3(d), total(d), cycle(s), method(s), count(i), id(i), user(s)
    $stmt->bind_param("ssdddddssiis", 
        $variety, 
        $planting_date, 
        $area_rai, 
        $cost_fertilizer, 
        $cost_chemicals, 
        $cost_labor, 
        $total_cost,
        $cycle_code, 
        $planting_method, 
        $plant_count, 
        $id, 
        $username
    );

    if ($stmt->execute()) {
        // อัปเดตสำเร็จ กลับไปหน้ารายการพร้อมแจ้งเตือน
        header("Location: production_list1.php?status=updated");
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: production_list1.php");
    exit();
}
?>