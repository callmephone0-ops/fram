<?php
session_start();
include "./db.php";

// 1. ตรวจสอบการล็อกอิน
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. รับ ID จาก URL
if (!isset($_GET['id'])) {
    die("ไม่พบ ID ของรอบการผลิต");
}
$cycle_id = mysqli_real_escape_string($conn, $_GET['id']);


// 3. 🚨 นี่คือจุดที่แก้ไข SQL Query 🚨
// เราจะ "ไม่" ดึง total_cost ที่เป็น 0
// แต่เราจะ "คำนวณใหม่" โดยใช้ (cost_... + cost_... + ...) AS calculated_total_cost

// $sql = "SELECT * FROM production_cycles WHERE id = '$cycle_id'"; // <-- (อันนี้คือโค้ดเดิม)

$sql = "SELECT *, 
           (cost_fertilizer + cost_chemicals + cost_labor) AS calculated_total_cost 
        FROM production_cycles 
        WHERE id = '$cycle_id'";

$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    die("ไม่พบข้อมูลรอบการผลิต ID: " . $cycle_id);
}
// ดึงข้อมูลมาเก็บใน $row
$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="th">
<?php require './head.html'; ?>

<style>
    .card-body,
    .form-label,
    .detail-item,
    h4, p {
        color: #000 !important; /* บังคับให้เป็นสีดำ */
    }
    .detail-item {
        font-size: 1.1rem;
        margin-bottom: 12px;
    }
</style>

<body>
    <div id="preloader">...</div>
    <div id="main-wrapper">
        <?php require './header1.html'; ?>
        <?php require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">ข้อมูลการเพาะปลูก</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    
                                    <div class="col-md-6">
                                        <p class="detail-item"><strong>ชนิดพืช:</strong> <?php echo htmlspecialchars($row['crop_type']); ?></p>
                                        <p class="detail-item"><strong>พันธุ์:</strong> <?php echo htmlspecialchars($row['variety']); ?></p>
                                        <p class="detail-item"><strong>วันที่เพาะปลูก:</strong> <?php echo date("d/m/Y", strtotime($row['planting_date'])); ?></p>
                                    </div>
                                    
                                    <div class="col-md-6 text-md-right">
                                        <p class="detail-item">
                                            <strong>สถานะ:</strong> 
                                            <span class="badge badge-warning">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </p>
                                        
                                        <h4 class="detail-item">
                                            <strong>ต้นทุน (บาท): <?php echo number_format($row['calculated_total_cost'], 2); ?></strong>
                                        </h4>
                                        </div>
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