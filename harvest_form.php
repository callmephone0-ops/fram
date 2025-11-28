<?php
session_start(); 
include "./db.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 1. รับ ID และตรวจสอบสิทธิ์
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_username = $_SESSION['username'];

if ($id <= 0) { echo "<script>alert('ID ไม่ถูกต้อง'); window.history.back();</script>"; exit(); }

// 2. ดึงข้อมูล (ตรวจสอบ ID และ Username)
$sql = "SELECT * FROM production_cycles WHERE id = ? AND username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $current_username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// 3. ถ้าไม่พบข้อมูล
if (!$data) { echo "<script>alert('ไม่พบข้อมูล หรือไม่มีสิทธิ์'); window.history.back();</script>"; exit(); }

// 4. เช็คสถานะ
if ($data['status'] == 'เก็บเกี่ยวแล้ว') {
    echo "<script>alert('รายการนี้เก็บเกี่ยวไปแล้ว'); window.location='production_list1.php';</script>";
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    :root {
        --primary: #2ecc71;
        --primary-dark: #27ae60;
        --bg-soft: #f4f6f9;
        --text-main: #333;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--bg-soft);
        color: var(--text-main);
    }

    .content-body { min-height: 85vh; }

    /* === Card Styling === */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #f39c12, #d35400); /* สีส้มทอง (สื่อถึงการเก็บเกี่ยว) */
        padding: 25px 30px;
        border: none;
        color: white;
    }

    .card-title {
        font-weight: 600;
        color: white;
        margin: 0;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
    }

    /* === Form Styling === */
    .form-group label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        border-radius: 12px;
        height: 50px;
        border: 1px solid #e0e0e0;
        background-color: #fcfcfc;
        padding-left: 15px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #f39c12;
        box-shadow: 0 0 0 4px rgba(243, 156, 18, 0.1);
        background-color: #fff;
    }

    /* Input Group (Icon) */
    .input-group-text {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-right: 0;
        border-radius: 12px 0 0 12px;
        color: #999;
    }
    .input-group .form-control {
        border-left: 0;
        border-radius: 0 12px 12px 0;
    }
    .input-group .form-control:focus {
        border-color: #e0e0e0; /* คงสีขอบเดิม */
        border-left: 1px solid #f39c12; /* เน้นซ้าย */
    }

    /* === Buttons === */
    .btn-save {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 40px;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
        transition: all 0.3s;
        min-width: 200px;
    }
    .btn-save:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(46, 204, 113, 0.4);
        color: white;
    }

    .btn-back {
        background-color: #fff;
        color: #777;
        border: 1px solid #ddd;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-back:hover { background-color: #f8f9fa; color: #333; text-decoration: none; }

    /* === Info Badge === */
    .crop-badge {
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-left: 10px;
        font-weight: 400;
    }

    /* === Mobile Responsive === */
    @media (max-width: 768px) {
        .btn-save, .btn-back { width: 100%; margin-bottom: 10px; }
        .action-buttons { flex-direction: column-reverse; }
        .card-header-custom { padding: 20px; }
        .card-title { flex-direction: column; align-items: flex-start; }
        .crop-badge { margin-left: 0; margin-top: 5px; }
    }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>

    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 style="font-weight: 700; color: #333;">บันทึกผลผลิต</h4>
                            <p class="mb-0 text-muted">กรอกข้อมูลเมื่อทำการเก็บเกี่ยวเสร็จสิ้น</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title">
                                    <span><i class="fa fa-archive mr-2"></i> บันทึกผลผลิต</span>
                                    <span class="crop-badge">
                                        <i class="fa <?php echo ($data['crop_type']=='ข้าว'?'fa-seedling':($data['crop_type']=='ยางพารา'?'fa-tree':'fa-lemon')); ?> mr-1"></i> 
                                        <?php echo htmlspecialchars($data['crop_type']) . " (" . htmlspecialchars($data['variety']) . ")"; ?>
                                    </span>
                                </h4>
                            </div>
                            
                            <div class="card-body p-4 p-md-5">
                                <form action="save_harvest.php" method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                    
                                    <div class="form-group mb-4">
                                        <label for="harvest_date">วันที่เก็บเกี่ยว <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date" class="form-control" id="harvest_date" name="harvest_date" required value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="harvest_kg">ปริมาณที่ได้ (กก.) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa fa-weight-hanging"></i></span>
                                                </div>
                                                <input type="number" step="0.01" class="form-control" id="harvest_kg" name="harvest_kg" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="price_per_kg">ราคาขาย (บาท/กก.) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa fa-tag"></i></span>
                                                </div>
                                                <input type="number" step="0.01" class="form-control" id="price_per_kg" name="price_per_kg" placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-light border-0 bg-light mt-3 mb-4 p-3 rounded-lg">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-info-circle text-info mr-3 fa-2x"></i>
                                            <small class="text-muted">
                                                ระบบจะคำนวณ <strong>รายรับรวม</strong> และ <strong>กำไรสุทธิ</strong> ให้โดยอัตโนมัติหลังจากบันทึกข้อมูล
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center action-buttons">
                                        <a href="production_list1.php" class="btn btn-back">
                                            <i class="fa fa-arrow-left mr-2"></i> ยกเลิก
                                        </a>
                                        <button type="submit" class="btn btn-save">
                                            <i class="fa fa-save mr-2"></i> บันทึกข้อมูล
                                        </button>
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
    <script>
    // Form Validation Script
    (function() { 'use strict'; window.addEventListener('load', function() { var forms = document.getElementsByClassName('needs-validation'); var validation = Array.prototype.filter.call(forms, function(form) { form.addEventListener('submit', function(event) { if (form.checkValidity() === false) { event.preventDefault(); event.stopPropagation(); } form.classList.add('was-validated'); }, false); }); }, false); })();
    </script>
</body>
</html>