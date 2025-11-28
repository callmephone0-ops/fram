<?php
session_start();
// 1. ตรวจสอบ Session
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
include "./db.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    :root {
        --primary-color: #2ecc71;
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

    /* === Card Design === */
    .form-container {
        max-width: 750px;
        margin: 0 auto;
    }

    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        padding: 25px 30px;
        border: none;
        color: white;
    }

    .card-title {
        font-weight: 600;
        color: white;
        margin: 0;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
    }

    /* === Form Styling === */
    .form-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        height: 50px;
        border: 1px solid #e0e0e0;
        background-color: #fcfcfc;
        padding-left: 15px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1);
        background-color: #fff;
    }

    /* === Buttons === */
    .btn-save {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        transition: all 0.3s;
        min-width: 200px;
    }
    .btn-save:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(46, 204, 113, 0.4);
        color: white;
    }

    .btn-cancel {
        background-color: #fff;
        color: #777;
        border: 1px solid #ddd;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-cancel:hover {
        background-color: #f8f9fa;
        color: #333;
    }

    /* === Mobile Responsiveness === */
    @media (max-width: 768px) {
        .card-header-custom { padding: 20px; }
        .btn-save, .btn-cancel { width: 100%; margin-bottom: 10px; }
        .action-buttons { flex-direction: column-reverse; }
    }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>
    
    <div id="main-wrapper">
        <?php require './header.html'; ?>
        <?php require './sidebar.html'; ?>
        
        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 style="font-weight: 700; color: #333;">เพิ่มผู้ใช้งานใหม่</h4>
                            <p class="mb-0 text-muted">กรอกข้อมูลเพื่อลงทะเบียนเกษตรกรหรือผู้ดูแลระบบ</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-container">
                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title">
                                    <i class="fa fa-user-plus mr-3"></i> ฟอร์มลงทะเบียน
                                </h4>
                            </div>
                            
                            <div class="card-body p-4 p-md-5">
                                <form id="registrationForm">
                                    
                                    <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size: 0.85rem; letter-spacing: 1px;">ข้อมูลบัญชี</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="username" class="form-label">เบอร์โทรศัพท์ (Username)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                                    <i class="fa fa-phone text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="0xx-xxx-xxxx" style="border-radius: 0 12px 12px 0;">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">รหัสผ่าน (Password)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                                    <i class="fa fa-lock text-muted"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="ตั้งรหัสผ่าน" style="border-radius: 0 12px 12px 0;">
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4 border-light">

                                    <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size: 0.85rem; letter-spacing: 1px;">ข้อมูลส่วนตัว</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="firstname" class="form-label">ชื่อจริง</label>
                                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="ชื่อจริง">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastname" class="form-label">นามสกุล</label>
                                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="นามสกุล">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="role" class="form-label">บทบาท (Role)</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="" selected disabled>-- เลือกบทบาท --</option>
                                                <option value="user">👤 เกษตรกร (User)</option>
                                                <option value="admin">👑 ผู้ดูแลระบบ (Admin)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">สถานะ</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="1" selected>🟢 เปิดใช้งาน (Active)</option>
                                                <option value="0">🔴 ระงับการใช้งาน (Inactive)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center action-buttons mt-5">
                                        <a href="admin-user.php" class="btn btn-cancel">
                                            <i class="fa fa-arrow-left mr-2"></i> ยกเลิก
                                        </a>
                                        <button type="submit" class="btn btn-save">
                                            <i class="fa fa-check mr-2"></i> บันทึกข้อมูล
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" id="modalHeader">
                    <h5 class="modal-title text-white" id="confirmModalLabel"><i class="fa fa-info-circle mr-2"></i> แจ้งเตือน</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">&times;</button>
                </div>
                <div class="modal-body p-4 text-center" id="modalBody" style="font-size: 1.1rem;"></div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php require './script.html'; ?>

    <script>
        const form = document.getElementById('registrationForm');
        const modalBody = document.getElementById('modalBody');
        const modalHeader = document.getElementById('modalHeader');
        
        // ใช้ jQuery เพื่อความชัวร์กับ Bootstrap Modal เวอร์ชันเก่า/ใหม่
        function showModal(type, message) {
            if(type === 'success') {
                modalHeader.className = 'modal-header bg-success text-white';
                $('#confirmModalLabel').html('<i class="fa fa-check-circle mr-2"></i> สำเร็จ');
            } else {
                modalHeader.className = 'modal-header bg-danger text-white';
                $('#confirmModalLabel').html('<i class="fa fa-exclamation-circle mr-2"></i> ผิดพลาด');
            }
            modalBody.innerHTML = message;
            $('#confirmModal').modal('show');
        }

        // เมื่อปิด Modal ถ้าสำเร็จให้ไปหน้า list
        $('#confirmModal').on('hidden.bs.modal', function () {
            if (modalHeader.classList.contains('bg-success')) {
                window.location.href = 'admin-user.php';
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // ตรวจสอบค่าเบื้องต้น
            const username = form.username.value.trim();
            const password = form.password.value.trim();
            const firstname = form.firstname.value.trim();
            const lastname = form.lastname.value.trim();
            const role = form.role.value;

            let errors = [];
            if (!username) errors.push('กรุณากรอก Username');
            if (!password) errors.push('กรุณากรอก Password');
            if (!firstname) errors.push('กรุณากรอกชื่อ');
            if (!lastname) errors.push('กรุณากรอกนามสกุล');
            if (!role) errors.push('กรุณาเลือกบทบาท');

            if (errors.length > 0) {
                showModal('error', errors.join('<br>'));
                return;
            }

            // ส่งข้อมูล
            fetch('./save_user.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showModal('success', data.message);
                } else {
                    showModal('error', data.message);
                }
            })
            .catch(err => {
                console.error(err);
                showModal('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        });
    </script>
</body>
</html>