<?php
include "./db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $crop_name = $_POST['crop_name'];
    $price_per_unit = $_POST['price_per_unit'];
    $unit = $_POST['unit'];

    // ตรวจสอบว่ามีข้อมูลพืชนี้อยู่แล้วหรือไม่ (UPSERT logic)
    $check_stmt = $conn->prepare("SELECT id FROM crop_prices WHERE crop_name = ?");
    $check_stmt->bind_param("s", $crop_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // ถ้ามีอยู่แล้ว ให้อัปเดต
        $stmt = $conn->prepare("UPDATE crop_prices SET price_per_unit = ?, unit = ? WHERE crop_name = ?");
        $stmt->bind_param("dss", $price_per_unit, $unit, $crop_name);
    } else {
        // ถ้ายังไม่มี ให้เพิ่มใหม่
        $stmt = $conn->prepare("INSERT INTO crop_prices (crop_name, price_per_unit, unit) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $crop_name, $price_per_unit, $unit);
    }

    if ($stmt->execute()) {
        header("Location: price_management.php?status=success");
    } else {
        header("Location: price_management.php?status=error");
    }
    $stmt->close();
    $conn->close();
}
?>