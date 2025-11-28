<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include "./db.php";

$current_username = $_SESSION['username'];
$info_message = ""; $info_message_type = "";
$pass_message = ""; $pass_message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. อัปเดตข้อมูลส่วนตัว
    if (isset($_POST['action']) && $_POST['action'] == 'update_info') {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $sql = "UPDATE user SET name = ?, surname = ? WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $surname, $current_username);
        if (mysqli_stmt_execute($stmt)) {
            $info_message = "อัปเดตข้อมูลสำเร็จ!"; $info_message_type = "success";
        } else {
            $info_message = "ผิดพลาด: " . mysqli_error($conn); $info_message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }

    // 2. เปลี่ยนรหัสผ่าน
    if (isset($_POST['action']) && $_POST['action'] == 'update_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass !== $confirm_pass) {
            $pass_message = "รหัสผ่านใหม่ไม่ตรงกัน"; $pass_message_type = "danger";
        } else {
            $sql_pass = "SELECT password FROM user WHERE username = ?";
            $stmt_pass = mysqli_prepare($conn, $sql_pass);
            mysqli_stmt_bind_param($stmt_pass, "s", $current_username);
            mysqli_stmt_execute($stmt_pass);
            $result_pass = mysqli_stmt_get_result($stmt_pass);
            $user_pass_row = mysqli_fetch_assoc($result_pass);
            
            if ($user_pass_row && password_verify($current_pass, $user_pass_row['password'])) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $sql_up = "UPDATE user SET password = ? WHERE username = ?";
                $stmt_up = mysqli_prepare($conn, $sql_up);
                mysqli_stmt_bind_param($stmt_up, "ss", $new_hash, $current_username);
                if (mysqli_stmt_execute($stmt_up)) {
                    $pass_message = "เปลี่ยนรหัสผ่านสำเร็จ!"; $pass_message_type = "success";
                } else {
                    $pass_message = "เกิดข้อผิดพลาด"; $pass_message_type = "danger";
                }
            } else {
                $pass_message = "รหัสผ่านเดิมไม่ถูกต้อง"; $pass_message_type = "danger";
            }
        }
    }
}

$sql_get = "SELECT name, surname, username, role FROM user WHERE username = ?";
$stmt_get = mysqli_prepare($conn, $sql_get);
mysqli_stmt_bind_param($stmt_get, "s", $current_username);
mysqli_stmt_execute($stmt_get);
$user_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get));

if (!$user_row) { session_destroy(); header("Location: login.php"); exit(); }
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

    /* === Profile Card (Left) === */
    .profile-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
        text-align: center;
        padding-bottom: 30px;
    }
    
    .profile-header-bg {
        height: 120px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .profile-avatar {
        margin-top: -60px;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }

    .profile-avatar i {
        font-size: 6rem;
        color: #fff;
        background: #2ecc71;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-name { font-size: 1.5rem; font-weight: 600; color: #333; margin-bottom: 5px; }
    .profile-role { font-size: 0.9rem; color: #777; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

    /* === Form Card (Right) === */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: white;
        margin-bottom: 30px;
    }
    
    .card-header-custom {
        padding: 20px 30px;
        border-bottom: 1px solid #f0f0f0;
        background: white;
        border-radius: 20px 20px 0 0;
    }

    .card-title { font-weight: 600; margin: 0; font-size: 1.1rem; color: #333; display: flex; align-items: center; }

    /* === Form Controls === */
    .form-control {
        border-radius: 12px;
        height: 48px;
        border: 1px solid #e0e0e0;
        background-color: #fcfcfc;
        padding-left: 15px;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1);
        background-color: #fff;
    }
    .form-control[readonly] { background-color: #f1f3f5; color: #6c757d; }

    /* === Buttons === */
    .btn-save {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        transition: 0.3s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4); color: white; }

    .btn-password {
        background-color: #34495e;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(52, 73, 94, 0.3);
        transition: 0.3s;
    }
    .btn-password:hover { transform: translateY(-2px); background-color: #2c3e50; color: white; }

    /* === Back Button Style === */
    .btn-back {
        background-color: #fff;
        color: #555;
        border: 1px solid #ddd;
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
    }
    .btn-back:hover {
        background-color: #f1f1f1;
        color: #333;
        text-decoration: none;
    }

    /* === Mobile Fixes === */
    @media (max-width: 768px) {
        .btn-save, .btn-password { width: 100%; }
        .profile-header-bg { height: 100px; }
        .profile-avatar i { width: 100px; height: 100px; font-size: 4rem; margin-top: -50px; }
        
        /* จัดปุ่มย้อนกลับในมือถือ */
        .page-titles .col-sm-6.justify-content-sm-end {
            justify-content: flex-start !important;
            margin-top: 15px;
        }
    }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>

    <div id="main-wrapper">
        <?php
        if ($_SESSION['role'] == 'admin') { require './header.html'; require './sidebar.html'; }
        else { require './header1.html'; require './sidebar1.html'; }
        ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4 align-items-center">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 style="font-weight: 700; color: #333;">โปรไฟล์ของฉัน</h4>
                            <p class="mb-0 text-muted">จัดการข้อมูลส่วนตัวและความปลอดภัย</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end d-flex">
                        <a href="javascript:history.back()" class="btn btn-back shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับ
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="profile-card">
                            <div class="profile-header-bg"></div>
                            <div class="profile-avatar">
                                <i class="fa fa-user"></i>
                            </div>
                            <h3 class="profile-name">
                                <?php echo htmlspecialchars($user_row['name'] . ' ' . $user_row['surname']); ?>
                            </h3>
                            <p class="text-muted mb-2">@<?php echo htmlspecialchars($user_row['username']); ?></p>
                            <span class="badge badge-<?php echo ($user_row['role'] == 'admin') ? 'danger' : 'success'; ?> px-3 py-2 rounded-pill">
                                <?php echo ucfirst(htmlspecialchars($user_row['role'])); ?>
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        
                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title"><i class="fa fa-id-card mr-2 text-primary"></i> แก้ไขข้อมูลส่วนตัว</h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if (!empty($info_message)): ?>
                                    <div class="alert alert-<?php echo $info_message_type; ?> border-0 shadow-sm rounded-lg mb-4">
                                        <?php echo $info_message; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="app-profile.php" method="POST">
                                    <input type="hidden" name="action" value="update_info">
                                    <div class="form-group mb-4">
                                        <label class="text-muted font-weight-bold">เบอร์โทรศัพท์ (Username)</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_row['username']); ?>" readonly>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="text-muted font-weight-bold">ชื่อจริง</label>
                                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_row['name']); ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="text-muted font-weight-bold">นามสกุล</label>
                                            <input type="text" class="form-control" name="surname" value="<?php echo htmlspecialchars($user_row['surname']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="text-right mt-3">
                                        <button type="submit" class="btn btn-save">
                                            <i class="fa fa-save mr-2"></i> บันทึกข้อมูล
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title"><i class="fa fa-key mr-2 text-warning"></i> เปลี่ยนรหัสผ่าน</h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if (!empty($pass_message)): ?>
                                    <div class="alert alert-<?php echo $pass_message_type; ?> border-0 shadow-sm rounded-lg mb-4">
                                        <?php echo $pass_message; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="app-profile.php" method="POST">
                                    <input type="hidden" name="action" value="update_password">
                                    <div class="form-group">
                                        <label class="text-muted font-weight-bold">รหัสผ่านปัจจุบัน</label>
                                        <input type="password" class="form-control" name="current_password" required placeholder="••••••">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="text-muted font-weight-bold">รหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" name="new_password" required placeholder="ตั้งรหัสผ่านใหม่">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="text-muted font-weight-bold">ยืนยันรหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" name="confirm_password" required placeholder="ยืนยันอีกครั้ง">
                                        </div>
                                    </div>
                                    <div class="text-right mt-3">
                                        <button type="submit" class="btn btn-password">
                                            <i class="fa fa-check-circle mr-2"></i> เปลี่ยนรหัสผ่าน
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
</body>
</html>