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
$title = $_POST['title'];
$start = $_POST['start'];
$end = !empty($_POST['end']) ? $_POST['end'] : NULL;

// ✅ [แก้ไข] รับค่าสีจาก Radio button
$color = !empty($_POST['color']) ? $_POST['color'] : '#007bff'; // ถ้าค่าว่างมา ให้ใช้สีน้ำเงิน

header('Content-Type: application/json');

if (empty($title) || empty($start)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit();
}

try {
    // ถ้ามี ID = Update
    if (!empty($id)) {
        $sql = "UPDATE calendar_events SET 
                    title = ?, 
                    start_event = ?, 
                    end_event = ?, 
                    backgroundColor = ?, 
                    borderColor = ?
                WHERE id = ? AND username = ?";
        
        $stmt = $conn->prepare($sql);
        // borderColor ใช้สีเดียวกับ backgroundColor
        $stmt->bind_param("sssssis", $title, $start, $end, $color, $color, $id, $username);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            // ถ้าไม่กระทบ (ข้อมูลเหมือนเดิม) ก็ถือว่าสำเร็จ
            echo json_encode(['success' => true, 'message' => 'No changes detected']);
        }

    // ถ้าไม่มี ID = Insert
    } else {
        $sql = "INSERT INTO calendar_events (username, title, start_event, end_event, backgroundColor, borderColor) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        // borderColor ใช้สีเดียวกับ backgroundColor
        $stmt->bind_param("ssssss", $username, $title, $start, $end, $color, $color);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถเพิ่มข้อมูลได้']);
        }
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>