<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. รับ ID และตรวจสอบความถูกต้อง
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // ถ้าไม่มี ID หรือ ID ไม่ใช่ตัวเลข ให้แสดงข้อความผิดพลาด
    echo "Error: Invalid Production Cycle ID.";
    exit();
}

$id = $_GET['id'];

// 2. ดึงข้อมูลจากฐานข้อมูล โดยใช้ Prepared Statement เพื่อความปลอดภัย
$query = "SELECT * FROM production_cycles WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 3. ตรวจสอบว่าเจอข้อมูลหรือไม่
if (mysqli_num_rows($result) == 0) {
    echo "Error: Production Cycle not found.";
    exit();
}

// 4. นำข้อมูลที่ได้มาเก็บในตัวแปร
$cycle = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="th">
<?php require './head.html'; ?>

<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <div id="main-wrapper">
        <?php require './header1.html'; ?>
        <?php require './sidebar1.html'; ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>รายละเอียดรอบการผลิต</h4>
                            <p class="mb-0">ข้อมูลของ: <?php echo htmlspecialchars($cycle['crop_type'] . ' - ' . ($cycle['cycle_code'] ?: $cycle['variety'])); ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="production_list.php">รายการผลิต</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">รายละเอียด</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">ข้อมูลรอบการผลิต</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td style="width: 200px;"><strong>ชนิดพืช</strong></td>
                                                <td><?php echo htmlspecialchars($cycle['crop_type']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>รอบการผลิต/พันธุ์</strong></td>
                                                <td><?php echo htmlspecialchars($cycle['cycle_code'] ?: $cycle['variety']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>วันเพาะปลูก</strong></td>
                                                <td><?php echo date("d F Y", strtotime($cycle['planting_date'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>สถานะ</strong></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($cycle['status'] == 'เก็บเกี่ยวแล้ว') ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($cycle['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>ต้นทุนทั้งหมด (บาท)</strong></td>
                                                <td><?php echo number_format($cycle['total_cost'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>วันที่สร้างข้อมูล</strong></td>
                                                <td><?php echo date("d F Y, H:i", strtotime($cycle['created_at'])); ?> น.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="production_list1.php" class="btn btn-secondary">ย้อนกลับ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require './script.html'; ?>
</body>

</html>