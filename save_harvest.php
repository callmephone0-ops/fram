<?php
session_start(); 
include "./db.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 1. ตรวจสอบว่าส่งมาแบบ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. รับข้อมูลจากฟอร์ม
    $id = intval($_POST['id']);
    $harvest_date = $_POST['harvest_date'];
    $harvest_kg = floatval($_POST['harvest_kg']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $current_username = $_SESSION['username'];

    // 3. ดึงต้นทุนเดิมมาคำนวณ
    $sql_cost = "SELECT cost_fertilizer, cost_chemicals, cost_labor 
                 FROM production_cycles 
                 WHERE id = ? AND username = ?";
    $stmt_cost = $conn->prepare($sql_cost);
    $stmt_cost->bind_param("is", $id, $current_username);
    $stmt_cost->execute();
    $result_cost = $stmt_cost->get_result();
    $row_cost = $result_cost->fetch_assoc();
    $stmt_cost->close();

    if (!$row_cost) {
        die("Error: ไม่พบข้อมูลต้นทุน หรือคุณไม่มีสิทธิ์อัปเดตรายการนี้");
    }

    // 4. คำนวณค่าใหม่
    $total_cost = $row_cost['cost_fertilizer'] + $row_cost['cost_chemicals'] + $row_cost['cost_labor'];
    $total_revenue = $harvest_kg * $price_per_kg;
    $profit = $total_revenue - $total_cost;
    $status = 'เก็บเกี่ยวแล้ว';

    // 5. อัปเดตฐานข้อมูล (สำคัญ: ต้องเช็ก id และ username)
    $sql_update = "UPDATE production_cycles 
                   SET 
                       harvest_date = ?,
                       harvest_kg = ?,
                       price_per_kg = ?,
                       total_revenue = ?,
                       total_cost = ?,
                       profit = ?,
                       status = ?
                   WHERE 
                       id = ? AND username = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sddddssis",
        $harvest_date,
        $harvest_kg,
        $price_per_kg,
        $total_revenue,
        $total_cost,
        $profit,
        $status,
        $id,
        $current_username
    );

    if ($stmt_update->execute()) {
        // อัปเดตสำเร็จ กลับไปหน้ารายการ
        header("Location: production_list1.php?success=harvested");
    } else {
        echo "Error: " . htmlspecialchars($stmt_update->error);
    }

    $stmt_update->close();
    $conn->close();

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST
    header("Location: production_list1.php");
    exit();
}
?>