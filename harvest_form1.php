<?php
session_start();
require "./db.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$production_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_username = $_SESSION['username'];
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; 

if ($production_id <= 0) die("ID ไม่ถูกต้อง");

$production_data = null;
$error_message = '';

function format_num($num) { return number_format(floatval($num), 2); }

// --- Handle Form Submit ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_harvest'])) {
    $harvest_date = $_POST['harvest_date'];
    $harvest_kg = floatval($_POST['harvest_kg']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $status = 'เก็บเกี่ยวแล้ว';

    // Get Total Cost
    $cost_sql = "SELECT (cost_fertilizer + cost_chemicals + cost_labor) AS total_cost FROM production_cycles WHERE id = ?";
    $stmt_cost = $conn->prepare($cost_sql);
    $stmt_cost->bind_param("i", $production_id);
    $stmt_cost->execute();
    $result_cost = $stmt_cost->get_result()->fetch_assoc();
    $total_cost = $result_cost['total_cost'] ?? 0;
    $stmt_cost->close();

    // Calculate
    $total_revenue = $harvest_kg * $price_per_kg;
    $profit = $total_revenue - $total_cost;

    // Update
    $update_query = "UPDATE production_cycles SET harvest_date=?, harvest_kg=?, price_per_kg=?, total_revenue=?, profit=?, status=? 
                     WHERE id=? AND (username=? OR ?='admin')";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sddddssis", $harvest_date, $harvest_kg, $price_per_kg, $total_revenue, $profit, $status, $production_id, $current_username, $current_role);

    if ($stmt_update->execute()) {
        if ($stmt_update->affected_rows > 0) {
            header("Location: production_list.php?status=harvest_saved");
            exit();
        } else {
            $error_message = "ไม่สามารถบันทึกข้อมูลได้ (ไม่มีสิทธิ์หรือข้อมูลไม่เปลี่ยนแปลง)";
        }
    } else {
        $error_message = "Error: " . $stmt_update->error;
    }
    $stmt_update->close();
}

// --- Fetch Data ---
$sql = "SELECT *, (cost_fertilizer + cost_chemicals + cost_labor) AS total_cost_calculated 
        FROM production_cycles WHERE id = ? AND (username = ? OR ? = 'admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $production_id, $current_username, $current_role);
$stmt->execute();
$production_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$production_data) die("ไม่พบข้อมูล");

$is_harvested = ($production_data['status'] == 'เก็บเกี่ยวแล้ว');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    /* ================= THEME: Deep Forest ================= */
    :root {
        --primary-dark: #143625;
        --primary-green: #27ae60;
        --light-bg: #f7f9f7;
        --text-dark: #333;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }

    /* Card Styling */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
    }

    .card-header-custom {
        background: var(--primary-dark);
        padding: 20px 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title-custom {
        color: #fff;
        font-weight: 600;
        margin: 0;
        font-size: 1.2rem;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-harvested { background-color: #d4edda; color: #155724; }
    .status-growing { background-color: #fff3cd; color: #856404; }

    /* Detail List */
    .detail-group {
        margin-bottom: 25px;
    }
    .detail-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary-dark);
        border-left: 4px solid var(--primary-green);
        padding-left: 10px;
        margin-bottom: 15px;
    }
    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed #eee;
    }
    .detail-item:last-child { border-bottom: none; }
    .label { color: #666; font-weight: 500; }
    .value { font-weight: 600; color: #333; }

    /* Profit Box */
    .profit-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin-top: 20px;
        border: 1px solid #eee;
    }
    .profit-value { font-size: 1.8rem; font-weight: 700; }
    .text-profit { color: #28a745; }
    .text-loss { color: #dc3545; }

    /* Form */
    .form-control {
        border-radius: 12px;
        height: 48px;
        background-color: #fcfcfc;
        border: 1px solid #e0e0e0;
    }
    .form-control:focus { border-color: var(--primary-green); box-shadow: none; }

    /* Responsive */
    @media (max-width: 768px) {
        .card-header-custom { flex-direction: column; align-items: flex-start; gap: 10px; }
        .profit-value { font-size: 1.5rem; }
    }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>
    
    <div id="main-wrapper">
        <?php require './header.html'; require './sidebar.html'; ?>
        
        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4><?php echo $is_harvested ? 'รายละเอียดผลผลิต' : 'บันทึกการเก็บเกี่ยว'; ?></h4>
                            <p class="mb-0 text-muted">จัดการข้อมูลผลผลิตและต้นทุน</p>
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

                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card card-custom">
                            
                            <div class="card-header-custom">
                                <h4 class="card-title-custom">
                                    <i class="fa fa-leaf mr-2"></i> 
                                    <?php echo htmlspecialchars($production_data['crop_type']) . " - " . htmlspecialchars($production_data['variety'] ?? $production_data['cycle_code']); ?>
                                </h4>
                                <span class="status-badge <?php echo $is_harvested ? 'status-harvested' : 'status-growing'; ?>">
                                    <?php if($is_harvested): ?><i class="fa fa-check-circle mr-1"></i> เก็บเกี่ยวแล้ว<?php else: ?><i class="fa fa-clock mr-1"></i> กำลังเพาะปลูก<?php endif; ?>
                                </span>
                            </div>

                            <div class="card-body p-4">
                                <?php if ($error_message): ?>
                                    <div class="alert alert-danger mb-4"><?php echo $error_message; ?></div>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <div class="detail-group">
                                            <div class="detail-title">ข้อมูลการเพาะปลูก</div>
                                            <div class="detail-item"><span class="label">ผู้บันทึก</span> <span class="value"><?php echo htmlspecialchars($production_data['username']); ?></span></div>
                                            <div class="detail-item"><span class="label">รหัสรอบ</span> <span class="value"><?php echo htmlspecialchars($production_data['cycle_code'] ?: '-'); ?></span></div>
                                            <div class="detail-item"><span class="label">วันที่ปลูก</span> <span class="value"><?php echo date("d/m/Y", strtotime($production_data['planting_date'])); ?></span></div>
                                            <div class="detail-item"><span class="label">พื้นที่</span> <span class="value"><?php echo htmlspecialchars($production_data['area_rai']); ?> ไร่</span></div>
                                            <div class="detail-item"><span class="label">จำนวนต้น</span> <span class="value"><?php echo htmlspecialchars($production_data['plant_count'] ?: '-'); ?></span></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="detail-group">
                                            <div class="detail-title">ข้อมูลต้นทุน</div>
                                            <div class="detail-item"><span class="label">ค่าปุ๋ย</span> <span class="value"><?php echo format_num($production_data['cost_fertilizer']); ?> ฿</span></div>
                                            <div class="detail-item"><span class="label">ค่าเคมีภัณฑ์</span> <span class="value"><?php echo format_num($production_data['cost_chemicals']); ?> ฿</span></div>
                                            <div class="detail-item"><span class="label">ค่าแรงงาน</span> <span class="value"><?php echo format_num($production_data['cost_labor']); ?> ฿</span></div>
                                            <div class="detail-item" style="background-color: #f1f8e9; padding: 10px; border-radius: 8px; margin-top: 10px; border:none;">
                                                <span class="label" style="color:#143625; font-weight:700;">รวมต้นทุน</span> 
                                                <span class="value" style="color:#143625; font-size:1.1rem;"><?php echo format_num($production_data['total_cost_calculated']); ?> ฿</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <?php if ($is_harvested): ?>
                                    <div class="detail-title">สรุปผลประกอบการ</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-group">
                                                <div class="detail-item"><span class="label">วันที่เก็บเกี่ยว</span> <span class="value"><?php echo date("d/m/Y", strtotime($production_data['harvest_date'])); ?></span></div>
                                                <div class="detail-item"><span class="label">ผลผลิตที่ได้</span> <span class="value"><?php echo format_num($production_data['harvest_kg']); ?> กก.</span></div>
                                                <div class="detail-item"><span class="label">ราคาขาย</span> <span class="value"><?php echo format_num($production_data['price_per_kg']); ?> บาท/กก.</span></div>
                                                <div class="detail-item"><span class="label">รายรับรวม</span> <span class="value text-success"><?php echo format_num($production_data['total_revenue']); ?> ฿</span></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="profit-box">
                                                <div class="text-muted mb-2">กำไรสุทธิ (Net Profit)</div>
                                                <div class="profit-value <?php echo ($production_data['profit'] >= 0) ? 'text-profit' : 'text-loss'; ?>">
                                                    <?php echo ($production_data['profit'] >= 0 ? '+' : '') . format_num($production_data['profit']); ?> ฿
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <div class="detail-title text-success"><i class="fa fa-edit mr-2"></i> บันทึกข้อมูลการเก็บเกี่ยว</div>
                                    <form action="" method="POST" class="mt-3">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>วันที่เก็บเกี่ยว <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="harvest_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>ปริมาณที่ได้ (กิโลกรัม) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" name="harvest_kg" placeholder="0.00" required>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>ราคาขายต่อหน่วย (บาท/กก.) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" name="price_per_kg" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right mt-3">
                                            <button type="submit" name="save_harvest" class="btn btn-success btn-lg px-5" style="border-radius: 50px;">
                                                <i class="fa fa-save mr-2"></i> บันทึกผลผลิต
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>

                                <div class="text-center mt-5 pt-3 border-top">
                                    <a href="production_list.php" class="btn btn-light px-4" style="border-radius: 50px;">
                                        <i class="fa fa-arrow-left mr-2"></i> กลับหน้ารายการ
                                    </a>
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