<?php
// session_start(); // เอาออก
include "./db.php"; // ต้องมี $conn

// if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) { // เอาออก
//     header("Location: login.html");
//     exit();
// }

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $cycle_id = mysqli_real_escape_string($conn, $_GET['id']);
    // $user_id = $_SESSION['user_id']; // เอาออก

    // *** ลบโดยใช้ id อย่างเดียว ***
    // (อันตราย! ทุกคนสามารถลบข้อมูลของทุกคนได้)
    $sql = "DELETE FROM production_cycles WHERE id = '$cycle_id'";
    // $sql = "DELETE FROM production_cycles WHERE id = '$cycle_id' AND user_id = '$user_id'"; // แบบเดิม

    if (mysqli_query($conn, $sql)) {
        // ลบสำเร็จ
        header("Location: data_list.php?status=delete_success");
    } else {
        // ลบไม่สำเร็จ
        header("Location: data_list.php?status=delete_error");
    }
} else {
    // ไม่มี id ส่งมา
    header("Location: data_list.php");
}
exit();
?>