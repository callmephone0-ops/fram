<?php
session_start();
include "./db.php";

// 1. ตรวจสอบว่าล็อกอิน และเป็นการส่งแบบ POST
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. รับค่าจากฟอร์ม
    $id = $_POST['id'];
    $crop_type = $_POST['crop_type'];
    $variety = $_POST['variety'];
    $area_rai = $_POST['area_rai'];
    $cost_fertilizer = $_POST['cost_fertilizer'];
    $cost_chemicals = $_POST['cost_chemicals'];
    $cost_labor = $_POST['cost_labor'];
    
    $current_username = $_SESSION['username'];

    // 3. เตรียมคำสั่ง UPDATE
    // (สำคัญมาก) เราต้องเพิ่ม AND username = ? ใน WHERE
    // เพื่อป้องกันไม่ให้ user แก้ไขข้อมูลของคนอื่นได้
    $sql = "UPDATE production_cycles SET 
                crop_type = ?, 
                variety = ?, 
                area_rai = ?, 
                cost_fertilizer = ?, 
                cost_chemicals = ?, 
                cost_labor = ? 
            WHERE 
                id = ? AND username = ?";

    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("SQL Error: " . $conn->error);
    }

    // 4. Bind ค่าตัวแปร
    // "ssddddis" = string, string, double, double, double, double, integer, string
    $stmt->bind_param("ssddddis", 
        $crop_type, 
        $variety, 
        $area_rai, 
        $cost_fertilizer, 
        $cost_chemicals, 
        $cost_labor, 
        $id, 
        $current_username
    );

    // 5. รันคำสั่ง
    if ($stmt->execute()) {
        // 6. ถ้าสำเร็จ ให้กลับไปหน้า list_production.php
        header("Location: list_production.php?update_success=1"); 
    } else {
        echo "Error: ไม่สามารถอัปเดตข้อมูลได้ " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST ให้ไล่กลับไป
    header("Location: list_production.php");
}
?>