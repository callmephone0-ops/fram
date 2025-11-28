<?php
// session_start(); // เอาออก
// if (!isset($_SESSION['username'])) { // เอาออก
//     header("Location: login.html");
//     exit();
// }
include "./db.php"; // ต้องมี $conn

// ดึงข้อมูลทั้งหมดในตาราง (ไม่กรอง user_id)
$sql = "SELECT * FROM production_cycles ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="th">

<?php
require './head.html';
?>

<body>
    <div id="preloader">...</div>
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
                            <h4>รายการข้อมูลการเพาะปลูก</h4>
                            <p class="mb-0">ข้อมูลทั้งหมดที่บันทึกไว้ในระบบ</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">รายการข้อมูล</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">📚 ข้อมูลการเพาะปลูกทั้งหมด</h4>
                                <a href="form.php" class="btn btn-success">
                                    <i class="fa fa-plus"></i> เพิ่มข้อมูลใหม่
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ชนิดพืช</th>
                                                <th>สายพันธุ์</th>
                                                <th>รอบการผลิต/จำนวนต้น</th>
                                                <th>พื้นที่ (ไร่)</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (mysqli_num_rows($result) > 0) {
                                                $count = 1;
                                                while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td>
                                                            <?php 
                                                                if ($row['crop_type'] == 'ข้าว') echo '🌾 ';
                                                                elseif ($row['crop_type'] == 'ลำไย') echo '🍈 ';
                                                                elseif ($row['crop_type'] == 'ยางพารา') echo '🌳 ';
                                                                echo htmlspecialchars($row['crop_type']); 
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['variety']); ?></td>
                                                        <td>
                                                            <?php
                                                            if ($row['crop_type'] == 'ข้าว') {
                                                                echo 'รอบ: ' . htmlspecialchars($row['cycle_code']);
                                                            } else {
                                                                echo htmlspecialchars($row['plant_count']) . ' ต้น';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['area_rai']); ?></td>
                                                        <td>
                                                            <a href="form.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                                <i class="fa fa-pencil"></i> แก้ไข
                                                            </a>
                                                            
                                                            <a href="delete_cycle.php?id=<?php echo $row['id']; ?>" 
                                                               class="btn btn-danger btn-sm" 
                                                               onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?');">
                                                                <i class="fa fa-trash"></i> ลบ
                                                            </a>
                                                        </td>
                                                    </tr>
                                            <?php
                                                } // end while
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">ไม่พบข้อมูล</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require './footer.html'; ?>

    </div>

    <?php require './script.html'; ?>
</body>
</html>