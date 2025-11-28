<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// 1. รับ ID จาก URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ไม่พบ ID ที่ต้องการแก้ไข");
}
$id = $_GET['id'];

// 2. ดึงข้อมูลเดิมจาก DB
// เพิ่มการเช็คสิทธิ์ (เฉพาะเจ้าของข้อมูล หรือ Admin เท่านั้นที่แก้ไขได้)
$current_username = $_SESSION['username'];
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

$stmt = $conn->prepare("SELECT * FROM production_cycles WHERE id = ? AND (username = ? OR ? = 'admin')");
$stmt->bind_param("iss", $id, $current_username, $current_role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูล หรือคุณไม่มีสิทธิ์แก้ไขรายการนี้");
}
$row = $result->fetch_assoc();
$stmt->close();
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
        --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
        --text-dark: #333;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }

    /* Page Title */
    .page-title {
        color: var(--primary-dark);
        font-weight: 700;
        margin-bottom: 5px;
    }

    /* Card Custom */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
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

    .card-header-custom h4 {
        color: #fff;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
    }

    /* Form Styles */
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 15px;
        border-left: 4px solid var(--primary-green);
        padding-left: 10px;
        display: flex;
        align-items: center;
    }

    .form-group label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 12px;
        height: 48px;
        border: 1px solid #e0e0e0;
        padding-left: 15px;
        font-size: 0.95rem;
        transition: all 0.3s;
        background-color: #fcfcfc;
    }

    .form-control:focus {
        border-color: var(--primary-green);
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
    }

    /* Cost Section Box */
    .cost-box {
        background-color: #f1f8e9;
        border-radius: 15px;
        padding: 20px;
        border: 1px dashed #c5e1a5;
    }

    /* Total Cost Display */
    .total-cost-display {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-green);
        text-align: right;
    }

    /* Buttons */
    .btn-custom {
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        font-size: 1rem;
        transition: 0.3s;
    }
    .btn-save {
        background: linear-gradient(135deg, #f39c12, #e67e22); /* สีส้มสำหรับการแก้ไข */
        border: none;
        color: white;
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        color: white;
    }
    .btn-cancel {
        background-color: #f0f2f5;
        color: #777;
        border: none;
    }
    .btn-cancel:hover {
        background-color: #e4e6eb;
        color: #333;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .card-body { padding: 20px 15px; }
        .btn-custom { width: 100%; margin-bottom: 10px; }
        .total-cost-display { text-align: left; margin-top: 10px; }
    }
</style>

<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">
        <?php require './header.html'; ?>
        <?php require './sidebar.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 class="page-title">แก้ไขข้อมูลการผลิต</h4>
                            <p class="mb-0 text-muted">แก้ไขรายละเอียดและปรับปรุงต้นทุน (ID: <?php echo htmlspecialchars($row['id']); ?>)</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item"><a href="production_list.php">รายการผลิต</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">แก้ไข</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-custom">
                            
                            <div class="card-header-custom">
                                <h4><i class="fa fa-edit mr-2"></i> แบบฟอร์มแก้ไขข้อมูล</h4>
                            </div>
                            
                            <div class="card-body">
                                <form action="update_production.php" method="POST">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

                                    <div class="form-section-title">
                                        <i class="fa fa-info-circle mr-2"></i> ข้อมูลทั่วไป
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label>ชนิดพืช</label>
                                                <input type="text" class="form-control" name="crop_type" value="<?php echo htmlspecialchars($row['crop_type']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label>พันธุ์พืช</label>
                                                <input type="text" class="form-control" name="variety" value="<?php echo htmlspecialchars($row['variety']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>รหัสรอบ/ชื่อแปลง</label>
                                                <input type="text" class="form-control" name="cycle_code" value="<?php echo htmlspecialchars($row['cycle_code']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>วันที่เริ่มปลูก</label>
                                                <input type="date" class="form-control" name="planting_date" value="<?php echo htmlspecialchars($row['planting_date']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>สถานะ</label>
                                                <select class="form-control" name="status">
                                                    <option value="กำลังเพาะปลูก" <?php echo ($row['status'] == 'กำลังเพาะปลูก') ? 'selected' : ''; ?>>กำลังเพาะปลูก</option>
                                                    <option value="เก็บเกี่ยวแล้ว" <?php echo ($row['status'] == 'เก็บเกี่ยวแล้ว') ? 'selected' : ''; ?>>เก็บเกี่ยวแล้ว</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>วิธีการปลูก</label>
                                                <input type="text" class="form-control" name="planting_method" value="<?php echo htmlspecialchars($row['planting_method']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>จำนวนต้น/กอ</label>
                                                <input type="number" class="form-control" name="plant_count" value="<?php echo htmlspecialchars($row['plant_count']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label>พื้นที่ (ไร่)</label>
                                                <input type="number" step="0.01" class="form-control" name="area_rai" value="<?php echo htmlspecialchars($row['area_rai']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">

                                    <div class="form-section-title">
                                        <i class="fa fa-coins mr-2"></i> ปรับปรุงต้นทุน
                                    </div>

                                    <div class="cost-box">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label>ค่าปุ๋ย (บาท)</label>
                                                    <input type="number" step="0.01" class="form-control cost-input" name="cost_fertilizer" value="<?php echo htmlspecialchars($row['cost_fertilizer']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label>ค่าเคมีภัณฑ์ (บาท)</label>
                                                    <input type="number" step="0.01" class="form-control cost-input" name="cost_chemicals" value="<?php echo htmlspecialchars($row['cost_chemicals']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label>ค่าแรงงาน (บาท)</label>
                                                    <input type="number" step="0.01" class="form-control cost-input" name="cost_labor" value="<?php echo htmlspecialchars($row['cost_labor']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="total-cost-display">
                                                    รวมต้นทุนทั้งหมด: <span id="totalCostText">0.00</span> บาท
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-custom btn-save mr-2">
                                                <i class="fa fa-save mr-2"></i> บันทึกการแก้ไข
                                            </button>
                                            <a href="production_list.php" class="btn btn-custom btn-cancel">
                                                <i class="fa fa-times mr-2"></i> ยกเลิก
                                            </a>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <?php require './script.html'; ?>

    <script>
        // ฟังก์ชันคำนวณต้นทุนรวมอัตโนมัติ
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.cost-input');
            const totalText = document.getElementById('totalCostText');

            function calculateTotal() {
                let total = 0;
                inputs.forEach(input => {
                    const val = parseFloat(input.value) || 0;
                    total += val;
                });
                totalText.textContent = total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // คำนวณครั้งแรกตอนโหลดหน้า
            calculateTotal();

            // ผูก Event ให้คำนวณทุกครั้งที่พิมพ์
            inputs.forEach(input => {
                input.addEventListener('input', calculateTotal);
            });
        });
    </script>
</body>
</html>