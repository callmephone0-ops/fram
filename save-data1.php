<?php
session_start();

// 1. เช็ค Login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. ป้องกัน Cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "./db.php";

// ========== FUNCTION ดึงข้อมูลจากฐานข้อมูล ==========

// 1. ดึงสายพันธุ์ (แยกตามประเภทพืช)
function getVarieties($conn, $cropType) {
    $varieties = [];
    if ($stmt = $conn->prepare("SELECT id, variety_name FROM crop_varieties WHERE crop_type = ? ORDER BY variety_name ASC")) {
        $stmt->bind_param("s", $cropType);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $varieties[] = $row; }
        $stmt->close();
    }
    return $varieties;
}

// 2. ดึงรอบการผลิตข้าว
function getRiceCycles($conn) {
    $cycles = [];
    $sql = "SELECT id, cycle_name FROM rice_production_cycles ORDER BY cycle_name ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) { $cycles[] = $row; }
    }
    return $cycles;
}

// 3. [เพิ่มใหม่] ดึงวิธีการปลูก
function getPlantingMethods($conn) {
    $methods = [];
    $sql = "SELECT id, method_name FROM planting_methods ORDER BY method_name ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) { $methods[] = $row; }
    }
    return $methods;
}

// 4. [เพิ่มใหม่] ดึงหน่วยนับพื้นที่
function getAreaUnits($conn) {
    $units = [];
    $sql = "SELECT id, unit_name FROM area_units ORDER BY unit_name ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) { $units[] = $row; }
    }
    return $units;
}

// --- เรียกใช้ฟังก์ชันเพื่อเตรียมข้อมูล ---
$rice_varieties = getVarieties($conn, 'ข้าว');
$longan_varieties = getVarieties($conn, 'ลำไย');
$rubber_varieties = getVarieties($conn, 'ยางพารา');

$rice_cycles = getRiceCycles($conn);
$planting_methods = getPlantingMethods($conn); // ข้อมูลจาก Admin
$area_units = getAreaUnits($conn);             // ข้อมูลจาก Admin
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<style>
    :root { --primary: #2ecc71; --primary-dark: #27ae60; --text-dark: #333; --bg-color: #f4f6f9; }
    body { font-family: 'Prompt', sans-serif; background-color: var(--bg-color); color: var(--text-dark); }
    .content-body { min-height: 85vh; }
    .card-custom { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); background-color: #fff; overflow: visible; }
    
    /* Tabs */
    .nav-pills-custom { background-color: #f1f3f5; border-radius: 20px; padding: 6px; margin-bottom: 30px; }
    .nav-pills-custom .nav-item { flex: 1; text-align: center; }
    .nav-pills-custom .nav-link { border-radius: 16px; color: #888; font-weight: 500; padding: 12px; transition: 0.3s; }
    .nav-pills-custom .nav-link.active { background-color: #fff; color: var(--primary-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-weight: 600; }
    .nav-pills-custom i { font-size: 1.2rem; vertical-align: middle; margin-right: 5px; }

    /* Forms */
    .form-group label { font-size: 0.9rem; font-weight: 600; color: #555; margin-bottom: 8px; display: block; }
    .form-control { border-radius: 12px; height: 48px; border: 1px solid #e0e0e0; padding-left: 15px; background-color: #fcfcfc; font-size: 0.95rem; transition: all 0.3s; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); background-color: #fff; }

    /* Select2 */
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single { border-radius: 12px; height: 48px; border: 1px solid #e0e0e0; background-color: #fcfcfc; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 15px; color: #333; font-size: 0.95rem; line-height: 48px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; right: 10px; }
    .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); }

    .section-label { font-size: 0.9rem; font-weight: 700; color: #aaa; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 15px 0; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
    .btn-save { background: linear-gradient(135deg, #2ecc71, #27ae60); border: none; border-radius: 50px; padding: 14px 40px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3); transition: all 0.3s; width: 100%; color: #fff; }
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(46, 204, 113, 0.4); color: #fff; }
    @media (min-width: 768px) { .btn-save { width: auto; min-width: 250px; } }
</style>

<body>

    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <h4 class="text-primary font-weight-bold">📝 บันทึกข้อมูลการเกษตร</h4>
                        <p class="mb-0 text-muted">กรอกข้อมูลรอบการผลิตใหม่ของคุณ</p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <div class="card card-custom">
                            <div class="card-body p-4">
                                
                                <ul class="nav nav-pills nav-pills-custom" id="cropTab" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" id="rice-tab" data-toggle="tab" href="#rice-content"><i class="fa fa-seedling"></i> ข้าว</a></li>
                                    <li class="nav-item"><a class="nav-link" id="longan-tab" data-toggle="tab" href="#longan-content"><i class="fa fa-lemon"></i> ลำไย</a></li>
                                    <li class="nav-item"><a class="nav-link" id="rubber-tab" data-toggle="tab" href="#rubber-content"><i class="fa fa-tree"></i> ยางพารา</a></li>
                                </ul>

                                <div class="tab-content pt-2">

                                    <div class="tab-pane fade show active" id="rice-content">
                                        <form action="save_production_cycle.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="crop_type" value="ข้าว">
                                            
                                            <div class="section-label mt-0">ข้อมูลทั่วไป</div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>สายพันธุ์ข้าว</label>
                                                    <select class="form-control select2" name="variety" required>
                                                        <option value="" disabled selected>-- เลือกสายพันธุ์ --</option>
                                                        <?php foreach ($rice_varieties as $v): ?>
                                                            <option value="<?= htmlspecialchars($v['variety_name']); ?>"><?= htmlspecialchars($v['variety_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>รอบการผลิต</label>
                                                    <select class="form-control select2" name="cycle_code" required>
                                                        <option value="" disabled selected>-- เลือกรอบการผลิต --</option>
                                                        <?php foreach ($rice_cycles as $c): ?>
                                                            <option value="<?= htmlspecialchars($c['cycle_name']); ?>"><?= htmlspecialchars($c['cycle_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label>วันที่เริ่มปลูก</label>
                                                    <input type="date" class="form-control" name="planting_date" required>
                                                </div>
                                                <div class="form-group col-6 col-md-2">
                                                    <label>พื้นที่ (จำนวน)</label>
                                                    <input type="number" step="0.01" class="form-control" name="area_value" placeholder="0.00" required>
                                                </div>
                                                <div class="form-group col-6 col-md-2">
                                                    <label>หน่วย</label>
                                                    <select class="form-control select2" name="area_unit" required>
                                                        <?php foreach ($area_units as $u): ?>
                                                            <option value="<?= htmlspecialchars($u['unit_name']); ?>"><?= htmlspecialchars($u['unit_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>วิธีการปลูก</label>
                                                    <select class="form-control select2" name="planting_method">
                                                        <option value="" disabled selected>-- เลือกวิธีการ --</option>
                                                        <?php foreach ($planting_methods as $m): ?>
                                                            <option value="<?= htmlspecialchars($m['method_name']); ?>"><?= htmlspecialchars($m['method_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="section-label">ต้นทุนเบื้องต้น (บาท)</div>
                                            <div class="form-row">
                                                <div class="form-group col-4"><label>ค่าปุ๋ย</label><input type="number" class="form-control" name="cost_fertilizer" value="0"></div>
                                                <div class="form-group col-4"><label>ค่ายา</label><input type="number" class="form-control" name="cost_chemicals" value="0"></div>
                                                <div class="form-group col-4"><label>ค่าแรง</label><input type="number" class="form-control" name="cost_labor" value="0"></div>
                                            </div>

                                            <div class="text-center mt-4">
                                                <button type="submit" class="btn btn-success btn-save"><i class="fa fa-check-circle mr-2"></i> บันทึกข้อมูลข้าว</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="tab-pane fade" id="longan-content">
                                        <form action="save_production_cycle.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="crop_type" value="ลำไย">
                                            
                                            <div class="section-label mt-0">ข้อมูลทั่วไป</div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>สายพันธุ์</label>
                                                    <select class="form-control select2" name="variety" required>
                                                        <option value="" disabled selected>-- เลือกสายพันธุ์ --</option>
                                                        <?php foreach ($longan_varieties as $v): ?>
                                                            <option value="<?= htmlspecialchars($v['variety_name']); ?>"><?= htmlspecialchars($v['variety_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>วันที่เริ่มปลูก</label>
                                                    <input type="date" class="form-control" name="planting_date" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label>จำนวนต้น</label>
                                                    <input type="number" class="form-control" name="plant_count" placeholder="0" required>
                                                </div>
                                                <div class="form-group col-6 col-md-4">
                                                    <label>พื้นที่ (จำนวน)</label>
                                                    <input type="number" step="0.01" class="form-control" name="area_value" placeholder="0.00" required>
                                                </div>
                                                <div class="form-group col-6 col-md-4">
                                                    <label>หน่วย</label>
                                                    <select class="form-control select2" name="area_unit" required>
                                                        <?php foreach ($area_units as $u): ?>
                                                            <option value="<?= htmlspecialchars($u['unit_name']); ?>"><?= htmlspecialchars($u['unit_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="section-label">ต้นทุนเบื้องต้น (บาท)</div>
                                            <div class="form-row">
                                                <div class="form-group col-4"><label>ค่าปุ๋ย</label><input type="number" class="form-control" name="cost_fertilizer" value="0"></div>
                                                <div class="form-group col-4"><label>ค่ายา</label><input type="number" class="form-control" name="cost_chemicals" value="0"></div>
                                                <div class="form-group col-4"><label>ค่าแรง</label><input type="number" class="form-control" name="cost_labor" value="0"></div>
                                            </div>

                                            <div class="text-center mt-4">
                                                <button type="submit" class="btn btn-success btn-save"><i class="fa fa-check-circle mr-2"></i> บันทึกข้อมูลลำไย</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="tab-pane fade" id="rubber-content">
                                        <form action="save_production_cycle.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="crop_type" value="ยางพารา">
                                            
                                            <div class="section-label mt-0">ข้อมูลทั่วไป</div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>สายพันธุ์</label>
                                                    <select class="form-control select2" name="variety" required>
                                                        <option value="" disabled selected>-- เลือกสายพันธุ์ --</option>
                                                        <?php foreach ($rubber_varieties as $v): ?>
                                                            <option value="<?= htmlspecialchars($v['variety_name']); ?>"><?= htmlspecialchars($v['variety_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>วันที่เริ่มปลูก</label>
                                                    <input type="date" class="form-control" name="planting_date" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label>จำนวนต้น</label>
                                                    <input type="number" class="form-control" name="plant_count" placeholder="0" required>
                                                </div>
                                                <div class="form-group col-6 col-md-4">
                                                    <label>พื้นที่ (จำนวน)</label>
                                                    <input type="number" step="0.01" class="form-control" name="area_value" placeholder="0.00" required>
                                                </div>
                                                <div class="form-group col-6 col-md-4">
                                                    <label>หน่วย</label>
                                                    <select class="form-control select2" name="area_unit" required>
                                                        <?php foreach ($area_units as $u): ?>
                                                            <option value="<?= htmlspecialchars($u['unit_name']); ?>"><?= htmlspecialchars($u['unit_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="section-label">ต้นทุนเบื้องต้น (บาท)</div>
                                            <div class="form-row">
                                                <div class="form-group col-4"><label>ค่าปุ๋ย</label><input type="number" class="form-control" name="cost_fertilizer" value="0"></div>
                                                <div class="form-group col-4"><label>ค่ายา</label><input type="number" class="form-control" name="cost_chemicals" value="0"></div>
                                                <div class="form-group col-4"><label>ค่าแรง</label><input type="number" class="form-control" name="cost_labor" value="0"></div>
                                            </div>

                                            <div class="text-center mt-4">
                                                <button type="submit" class="btn btn-success btn-save"><i class="fa fa-check-circle mr-2"></i> บันทึกข้อมูลยางพารา</button>
                                            </div>
                                        </form>
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
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // ตั้งค่า Select2
            $('.select2').select2({
                width: '100%',
                placeholder: 'เลือกข้อมูล',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "ไม่พบข้อมูล";
                    }
                }
            });

            // เคลียร์ค่าที่ค้างอยู่เพื่อให้ Placeholder ทำงาน
            $('.select2').val(null).trigger('change'); 
        });
    </script>

</body>
</html>