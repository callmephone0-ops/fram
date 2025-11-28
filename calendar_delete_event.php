<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่ได้เข้าสู่ระบบ']);
    exit();
}

include "./db.php";

$username = $_SESSION['username'];
$id = $_POST['id'];

header('Content-Type: application/json');

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ ID กิจกรรม']);
    exit();
}

try {
    // ลบโดยเช็ค id และ username (เพื่อความปลอดภัย)
    $sql = "DELETE FROM calendar_events WHERE id = ? AND username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล หรือ ไม่มีสิทธิ์ลบ']);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>