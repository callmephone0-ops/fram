<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. ตรวจสอบว่ามี ID ส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ไม่พบ ID ผู้ใช้งาน");
}
$user_id = $_GET['id'];

// 2. ดึงข้อมูล 2 ตารางพร้อมกัน (เพิ่ม u.username)
$sql = "SELECT 
            u.id as user_id, u.username, u.name, u.surname, u.status, u.role,
            f.id as farm_id, f.farm_name, f.farm_address, f.farm_size_rai, f.main_crop, f.contact_phone, f.description
        FROM 
            user u
        LEFT JOIN 
            farm_details f ON u.id = f.user_id
        WHERE 
            u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("ไม่พบข้อมูลผู้ใช้งานรหัสนี้");
}
$data = $result->fetch_assoc();

// 3. จัดการการแสดงผล (เหมือนเดิม)
if ($data['status'] == 1) {
    $status_badge = "<span class='badge bg-success text-white'>เปิดใช้งาน</span>";
} else {
    $status_badge = "<span class='badge bg-secondary text-white'>ยังไม่เปิดใช้งาน</span>";
}

if ($data['role'] == 'admin') {
    $role_badge = "<span class='badge bg-danger'>ผู้ดูแลระบบ</span>";
} else {
    $role_badge = "<span class='badge bg-primary'>เกษตรกร</span>";
}


// ==========================================================
// 4. [ส่วนที่เพิ่มเข้ามา] ดึงข้อมูลการเพาะปลูกของผู้ใช้คนนี้
// ==========================================================

// 4.1 ดึงชื่อ username ของ user ที่กำลังดูอยู่ เพื่อไปค้นหาในตาราง production_cycles
$viewing_username = $data['username'];

// 4.2 ดึงข้อมูล "รอบการผลิตที่กำลังดำเนินอยู่"
$sql_active = "SELECT * FROM production_cycles 
               WHERE username = ? AND status = 'กำลังเพาะปลูก' 
               ORDER BY planting_date DESC";
$stmt_active = $conn->prepare($sql_active);
$stmt_active->bind_param("s", $viewing_username);
$stmt_active->execute();
$result_active = $stmt_active->get_result();

// 4.3 ดึงข้อมูล "ผลผลิตที่เก็บเกี่ยวแล้ว"
$sql_completed = "SELECT * FROM production_cycles 
                  WHERE username = ? AND status = 'เก็บเกี่ยวแล้ว' 
                  ORDER BY harvest_date DESC";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("s", $viewing_username);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<?php 
require './head.html';
?>
<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <div id="main-wrapper">

        <?php 
        require './header.html' ;
        require './sidebar.html' ; 
        ?>
        
        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-xl-4 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">👨‍🌾 ข้อมูลบัญชีผู้ใช้</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
                                <p><strong>นามสกุล:</strong> <?php echo htmlspecialchars($data['surname']); ?></p>
                                <hr>
                                <p><strong>สถานะ:</strong> <?php echo $status_badge; ?></p>
                                <p><strong>บทบาท:</strong> <?php echo $role_badge; ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="admin-list.php" class="btn btn-sm btn-outline-secondary">
                                    ⬅️ กลับไปหน้ารายชื่อ
                                </a>
                                <a href="user-edit.php?id=<?php echo $data['user_id']; ?>" class="btn btn-sm btn-warning">
                                    ✏️ แก้ไขข้อมูล
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">🌾 ข้อมูลการเกษตร</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data['farm_name'])) : ?>
                                    
                                    <p><strong>ชื่อฟาร์ม:</strong> <?php echo htmlspecialchars($data['farm_name']); ?></p>
                                    <p><strong>พืชหลักที่ปลูก:</strong> <?php echo htmlspecialchars($data['main_crop']); ?></p>
                                    <p><strong>ขนาดพื้นที่:</strong> <?php echo htmlspecialchars($data['farm_size_rai']); ?> ไร่</p>
                                    <p><strong>เบอร์ติดต่อ (ฟาร์ม):</strong> <?php echo htmlspecialchars($data['contact_phone']); ?></p>
                                    <p><strong>ที่อยู่ฟาร์ม:</strong></p>
                                    <p><?php echo nl2br(htmlspecialchars($data['farm_address'])); ?></p>
                                    <p><strong>รายละเอียดอื่น ๆ:</strong></p>
                                    <p><?php echo nl2br(htmlspecialchars($data['description'])); ?></p>
                                    
                                <?php else : ?>
                                    
                                    <div class="alert alert-warning text-center">
                                        ยังไม่มีการกรอกข้อมูลการเกษตรสำหรับผู้ใช้งานนี้
                                        <br>
                                        <a href="user-edit.php?id=<?php echo $data['user_id']; ?>" class="btn btn-sm btn-warning mt-2">
                                            ➕ เพิ่ม/แก้ไขข้อมูลการเกษตร
                                        </a>
                                    </div>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">🌱 รายการเพาะปลูก (ที่กำลังดำเนินอยู่)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ชนิดพืช</th>
                                                <th>พันธุ์ / รอบการผลิต</th>
                                                <th>วันที่ปลูก</th>
                                                <th>พื้นที่ (ไร่)</th>
                                                <th>ต้นทุนสะสม (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_active->num_rows > 0): ?>
                                                <?php while ($row = $result_active->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($row['crop_type']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['cycle_code'] ?: $row['variety']); ?></td>
                                                        <td><?php echo $row['planting_date'] ? date("d/m/Y", strtotime($row['planting_date'])) : '-'; ?></td>
                                                        <td><?php echo htmlspecialchars($row['area_rai']); ?></td>
                                                        <td><?php echo number_format($row['total_cost'], 2); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">ไม่พบข้อมูลการเพาะปลูกที่กำลังดำเนินอยู่</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">📈 ประวัติผลผลิต (ที่เก็บเกี่ยวแล้ว)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ชนิดพืช</th>
                                                <th>พันธุ์ / รอบการผลิต</th>
                                                <th>วันที่เก็บเกี่ยว</th>
                                                <th>ผลผลิต (กก.)</th>
                                                <th>รายได้ (บาท)</th>
                                                <th>กำไร/ขาดทุน (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_completed->num_rows > 0): ?>
                                                <?php while ($row = $result_completed->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($row['crop_type']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['cycle_code'] ?: $row['variety']); ?></td>
                                                        <td><?php echo $row['harvest_date'] ? date("d/m/Y", strtotime($row['harvest_date'])) : '-'; ?></td>
                                                        <td><?php echo number_format($row['harvest_kg'], 2); ?></td>
                                                        <td><?php echo number_format($row['total_revenue'], 2); ?></td>
                                                        <td>
                                                            <?php 
                                                            $profit_class = $row['profit'] >= 0 ? 'text-success' : 'text-danger';
                                                            echo "<strong class='{$profit_class}'>" . number_format($row['profit'], 2) . "</strong>";
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">ไม่พบประวัติการเก็บเกี่ยวผลผลิต</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                --------------------------------------------------------------------------------
                                <?php
                                // ปิดการเชื่อมต่อของ statement ที่เพิ่มเข้ามา
                                $stmt_active->close();
                                $stmt_completed->close();
                                $conn->close();
                                ?>
                                --------------------------------------------------------------------------------
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div> 
        
    </div>
    <?php 
    require './script.html';
    ?>
</body>
</html>