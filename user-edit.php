<?php
session_start();
// 1. ตรวจสอบ Session และต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "./db.php";

// 2. รับค่า id
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    echo "<script>alert('ID ไม่ถูกต้อง'); window.location='admin-user.php';</script>";
    exit();
}

// 3. ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<script>alert('ไม่พบข้อมูลผู้ใช้'); window.location='admin-user.php';</script>";
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
        height: 50px; /* สูงขึ้นเพื่อให้กดง่ายในมือถือ */
        border: 1px solid #eee;
        background-color: #fcfcfc;
        padding-left: 15px;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1);
        background-color: #fff;
    }

    .form-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }

    /* === Select Box === */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
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

    .btn-back {
        background-color: #fff;
        color: #777;
        border: 1px solid #ddd;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-back:hover {
        background-color: #f8f9fa;
        color: #333;
    }

    /* === Mobile Responsiveness === */
    @media (max-width: 768px) {
        .btn-save, .btn-back {
            width: 100%; /* ปุ่มเต็มจอในมือถือ */
            margin-bottom: 10px;
        }
        .card-header-custom {
            padding: 20px;
        }
        .action-buttons {
            flex-direction: column-reverse; /* ปุ่มย้อนกลับอยู่ล่าง */
        }
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
                            <h4 style="font-weight: 700; color: #333;">จัดการข้อมูลผู้ใช้</h4>
                            <p class="mb-0 text-muted">แก้ไขรายละเอียดบัญชีผู้ใช้งาน</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title">
                                    <i class="fa fa-user-edit mr-3"></i> 
                                    แก้ไขข้อมูล: <?php echo htmlspecialchars($user['name']); ?>
                                </h4>
                            </div>
                            
                            <div class="card-body p-4 p-md-5">
                                <form action="user-update.php" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="name">ชื่อจริง</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="surname">นามสกุล</label>
                                            <input type="text" class="form-control" id="surname" name="surname"
                                                value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="username">เบอร์โทรศัพท์ (Username)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0" style="border-radius: 12px 0 0 12px;">
                                                    <i class="fa fa-phone text-muted"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control border-left-0" id="username" name="username"
                                                value="<?php echo htmlspecialchars($user['username']); ?>" required
                                                style="border-radius: 0 12px 12px 0;">
                                        </div>
                                    </div>

                                    <div class="form-row mt-2">
                                        <div class="form-group col-md-6">
                                            <label for="role">บทบาท (Role)</label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>👤 เกษตรกร (User)</option>
                                                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>👑 ผู้ดูแลระบบ (Admin)</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="status">สถานะบัญชี</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="1" <?php if ($user['status'] == 1) echo 'selected'; ?> style="color: green;">🟢 เปิดใช้งาน (Active)</option>
                                                <option value="0" <?php if ($user['status'] == 0) echo 'selected'; ?> style="color: red;">🔴 ระงับการใช้งาน (Inactive)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between align-items-center action-buttons">
                                        <a href="admin-user.php" class="btn btn-back">
                                            <i class="fa fa-arrow-left mr-2"></i> ยกเลิก
                                        </a>
                                        <button type="submit" class="btn btn-save">
                                            <i class="fa fa-save mr-2"></i> บันทึกการเปลี่ยนแปลง
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
    
    <?php require './script.html'; ?>
</body>
</html>