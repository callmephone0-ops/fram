<?php
session_start();
include "./db.php";

// 1. เช็ค Login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. ตรวจสอบว่ามี ID ส่งมาไหม
if (!isset($_GET['id'])) {
    header("Location: production_list1.php");
    exit();
}

$id = $_GET['id'];
$current_username = $_SESSION['username'];

// 3. ดึงข้อมูลเดิมออกมา
$sql = "SELECT * FROM production_cycles WHERE id = ? AND username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $current_username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "ไม่พบข้อมูล หรือคุณไม่มีสิทธิ์แก้ไข";
    exit();
}

// 4. ดึงข้อมูลตัวเลือก (เหมือนหน้าบันทึก)
function getVarieties($conn, $cropType) {
    $varieties = [];
    if ($stmt = $conn->prepare("SELECT id, variety_name FROM crop_varieties WHERE crop_type = ? ORDER BY variety_name ASC")) {
        $stmt->bind_param("s", $cropType);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($r = $result->fetch_assoc()) { $varieties[] = $r; }
        $stmt->close();
    }
    return $varieties;
}
function getRiceCycles($conn) {
    $cycles = [];
    if ($stmt = $conn->prepare("SELECT id, cycle_name FROM rice_production_cycles ORDER BY cycle_name ASC")) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($r = $result->fetch_assoc()) { $cycles[] = $r; }
        $stmt->close();
    }
    return $cycles;
}

$rice_varieties = getVarieties($conn, 'ข้าว');
$longan_varieties = getVarieties($conn, 'ลำไย');
$rubber_varieties = getVarieties($conn, 'ยางพารา');
$rice_cycles = getRiceCycles($conn);

// เก็บประเภทพืชของรายการนี้ เพื่อเลือกแสดงฟอร์มให้ถูก
$current_crop_type = $row['crop_type'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<style>
    /* === Theme Styles (เหมือนหน้าบันทึกเป๊ะๆ) === */
    :root { --primary: #2ecc71; --primary-dark: #27ae60; --text-dark: #333; --bg-color: #f4f6f9; }
    body { font-family: 'Prompt', sans-serif; background-color: var(--bg-color); color: var(--text-dark); }
    .content-body { min-height: 85vh; }
    
    /* Card */
    .card-custom { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); background-color: #fff; overflow: visible; }
    
    /* Form Styling */
    .form-group label { font-size: 0.9rem; font-weight: 600; color: #555; margin-bottom: 8px; display: block; }
    .form-control { border-radius: 12px; height: 48px; border: 1px solid #e0e0e0; padding-left: 15px; background-color: #fcfcfc; font-size: 0.95rem; transition: all 0.3s; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); background-color: #fff; }
    
    /* Select2 */
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single { border-radius: 12px; height: 48px; border: 1px solid #e0e0e0; background-color: #fcfcfc; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 15px; color: #333; font-size: 0.95rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; right: 10px; }
    .select2-dropdown { border-radius: 12px; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 9999; }

    /* Section Label */
    .section-label { font-size: 0.9rem; font-weight: 700; color: #aaa; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 15px 0; border-bottom: 1px dashed #eee; padding-bottom: 5px; }

    /* Button */
    .btn-save { background: linear-gradient(135deg, #f39c12, #d35400); border: none; border-radius: 50px; padding: 14px 40px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3); transition: all 0.3s; width: 100%; color: white; }
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(243, 156, 18, 0.4); color: white; }
    .btn-back { border-radius: 50px; padding: 14px 30px; font-weight: 600; color: #777; }

    @media (min-width: 768px) { .btn-save { width: auto; min-width: 250px; } }
</style>

<body>
    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <h4 class="text-primary font-weight-bold">✏️ แก้ไขข้อมูลการเกษตร</h4>
                        <p class="mb-0 text-muted">แก้ไขข้อมูล: <strong><?php echo htmlspecialchars($current_crop_type); ?></strong></p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <div class="card card-custom">
                            <div class="card-body p-4">
                                
                                <form action="update_production_cycle.php" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="crop_type" value="<?php echo $current_crop_type; ?>">

                                    <?php if ($current_crop_type == 'ข้าว'): ?>
                                        <div class="section-label mt-0">ข้อมูลทั่วไป (ข้าว)</div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>สายพันธุ์ข้าว</label>
                                                <select class="form-control select2" name="variety" required>
                                                    <?php foreach ($rice_varieties as $v): ?>
                                                        <option value="<?php echo htmlspecialchars($v['variety_name']); ?>" <?php echo ($row['variety'] == $v['variety_name']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($v['variety_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>รอบการผลิต</label>
                                                <select class="form-control select2" name="cycle_code" required>
                                                    <?php foreach ($rice_cycles as $c): ?>
                                                        <option value="<?php echo htmlspecialchars($c['cycle_name']); ?>" <?php echo ($row['cycle_code'] == $c['cycle_name']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($c['cycle_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>วันที่เริ่มปลูก</label>
                                                <input type="date" class="form-control" name="planting_date" value="<?php echo $row['planting_date']; ?>" required>
                                            </div>
                                            <div class="form-group col-6 col-md-4">
                                                <label>พื้นที่ (ไร่)</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" name="area_rai" value="<?php echo $row['area_rai']; ?>" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white border-left-0">ไร่</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-6 col-md-4">
                                                <label>วิธีการปลูก</label>
                                                <select class="form-control select2" name="planting_method">
                                                    <?php 
                                                    $methods = ['หว่าน', 'ปักดำ', 'หยอดหลุม', 'เครื่องจักร', 'อื่นๆ'];
                                                    foreach ($methods as $m) {
                                                        $selected = ($row['planting_method'] == $m) ? 'selected' : '';
                                                        echo "<option value='$m' $selected>$m</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <div class="section-label mt-0">ข้อมูลทั่วไป (<?php echo $current_crop_type; ?>)</div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>สายพันธุ์</label>
                                                <select class="form-control select2" name="variety" required>
                                                    <?php 
                                                    $varieties = ($current_crop_type == 'ลำไย') ? $longan_varieties : $rubber_varieties;
                                                    foreach ($varieties as $v): ?>
                                                        <option value="<?php echo htmlspecialchars($v['variety_name']); ?>" <?php echo ($row['variety'] == $v['variety_name']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($v['variety_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>วันที่เริ่มปลูก</label>
                                                <input type="date" class="form-control" name="planting_date" value="<?php echo $row['planting_date']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>จำนวนต้น</label>
                                                <input type="number" class="form-control" name="plant_count" value="<?php echo $row['plant_count']; ?>" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>พื้นที่ (ไร่)</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" name="area_rai" value="<?php echo $row['area_rai']; ?>" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white border-left-0">ไร่</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="section-label mt-4">ต้นทุนเบื้องต้น (บาท)</div>
                                    <div class="form-row">
                                        <div class="form-group col-4">
                                            <label>ค่าปุ๋ย</label>
                                            <input type="number" class="form-control" name="cost_fertilizer" value="<?php echo $row['cost_fertilizer']; ?>">
                                        </div>
                                        <div class="form-group col-4">
                                            <label>ค่ายา</label>
                                            <input type="number" class="form-control" name="cost_chemicals" value="<?php echo $row['cost_chemicals']; ?>">
                                        </div>
                                        <div class="form-group col-4">
                                            <label>ค่าแรง</label>
                                            <input type="number" class="form-control" name="cost_labor" value="<?php echo $row['cost_labor']; ?>">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <a href="production_list1.php" class="btn btn-light btn-back"><i class="fa fa-arrow-left mr-2"></i> ยกเลิก</a>
                                        <button type="submit" class="btn btn-save"><i class="fa fa-save mr-2"></i> อัปเดตข้อมูล</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './footer.html'; ?>
    </div>

    <?php require './script.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%', placeholder: "เลือกข้อมูล" });
        });
    </script>
</body>
</html>