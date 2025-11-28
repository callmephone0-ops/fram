<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. รับค่า id จาก URL และตรวจสอบว่าเป็นตัวเลขเท่านั้น
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    echo "ID ผู้ใช้ไม่ถูกต้อง";
    exit();
}

// 2. ดึงข้อมูลผู้ใช้จากฐานข้อมูลด้วย Prepared Statement เพื่อความปลอดภัย
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "ไม่พบข้อมูลผู้ใช้";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require './head.html'; ?>
<body>
    <div id="main-wrapper">
        <?php 
        require './header.html';
        require './sidebar.html'; 
        ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>รายละเอียดเกษตรกร</h4>
                            <p class="mb-0">ข้อมูลส่วนตัวของ <?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">ข้อมูลผู้ใช้ ID: <?php echo $user['id']; ?></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px;">ชื่อ</th>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>นามสกุล</th>
                                                <td><?php echo htmlspecialchars($user['surname']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>เบอร์โทร (Username)</th>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>สถานะ</th>
                                                <td>
                                                    <?php 
                                                        if ($user['status'] == 1) {
                                                            echo "<span class='badge bg-success text-white'>เปิดใช้งาน</span>";
                                                        } else {
                                                            echo "<span class='badge bg-secondary text-white'>ยังไม่เปิดใช้งาน</span>";
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-right mt-3">
                                    <a href="admin-user.php" class="btn btn-secondary">ย้อนกลับ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './script.html'; ?>
    </div>
</body>
</html>