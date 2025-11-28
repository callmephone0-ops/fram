<?php
session_start();
include "./db.php";

// 1. เช็คว่าล็อกอินหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// 2. ป้องกันการกดปุ่ม Back หลังจาก Logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ================= START: ดึงชื่อผู้ใช้ =================
$display_name = "";
$current_username = $_SESSION['username'];

$sql_user = "SELECT name, surname FROM user WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user) {
    $stmt_user->bind_param("s", $current_username);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user_row = $result_user->fetch_assoc();
        $display_name = htmlspecialchars($user_row['name']) . ' ' . htmlspecialchars($user_row['surname']);
    }
    $stmt_user->close();
} else {
    $display_name = htmlspecialchars($current_username);
}
// ================= END: ดึงชื่อผู้ใช้ =================
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    /* ใช้ฟอนต์สวยงาม */
    body {
        font-family: 'Prompt', sans-serif;
        background-color: #f4f6f9;
    }

    /* ปรับแต่งการ์ดต้อนรับ */
    .welcome-card {
        border: none;
        border-radius: 15px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-left: 5px solid #2ecc71;
        /* สีเขียว */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .welcome-icon i {
        font-size: 3.5rem;
        color: #2ecc71;
    }

    /* ปรับแต่งเมนูลัด (Menu Cards) */
    .menu-card {
        background: #fff;
        border: none;
        border-radius: 20px;
        text-align: center;
        padding: 25px 15px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-decoration: none !important;
        /* ลบขีดเส้นใต้ลิงก์ */
        position: relative;
        overflow: hidden;
    }

    /* Effect ตอนเอาเมาส์ชี้ */
    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(46, 204, 113, 0.2);
    }

    /* วงกลมรองหลังไอคอน */
    .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background-color: #eafaf1;
        /* สีเขียวอ่อนจางๆ */
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        transition: 0.3s;
    }

    .menu-card:hover .icon-wrapper {
        background-color: #2ecc71;
        /* เปลี่ยนเป็นสีเขียวเข้มตอนชี้ */
    }

    .menu-card i {
        font-size: 2rem;
        color: #2ecc71;
        transition: 0.3s;
    }

    .menu-card:hover i {
        color: #fff;
        /* ไอคอนเปลี่ยนเป็นสีขาว */
    }

    .menu-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .menu-desc {
        font-size: 0.85rem;
        color: #888;
        display: block;
        /* แสดงในจอใหญ่ */
    }

    /* Responsive: ปรับรูป Slide ให้ไม่สูงเกินไป */
    .carousel-item img {
        height: 300px;
        object-fit: cover;
        border-radius: 15px;
    }

    /* ปรับแต่งสำหรับมือถือ */
    @media (max-width: 768px) {
        .carousel-item img {
            height: 200px;
            /* ลดความสูงรูปในมือถือ */
        }

        .menu-desc {
            display: none;
            /* ซ่อนคำอธิบายยาวๆ ในมือถือเพื่อให้ดูสะอาดตา */
        }

        .welcome-icon {
            display: none;
            /* ซ่อนไอคอนใหญ่ใน welcome card บนมือถือ */
        }

        .menu-card {
            padding: 15px;
        }

        .icon-wrapper {
            width: 55px;
            height: 55px;
        }

        .menu-card i {
            font-size: 1.5rem;
        }
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

        <?php
        require './header.html';
        require './sidebar.html';
        ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row mb-4">
                    <div class="col-lg-12">
                        <div id="heroCarousel" class="carousel slide shadow-sm" data-bs-ride="carousel"
                            style="border-radius: 15px;">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="./images/ภาพ2.png" class="d-block w-100" alt="Farm Banner">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card welcome-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="welcome-icon mr-4">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1 font-weight-bold text-dark">สวัสดี Admin,
                                            <?php echo $display_name; ?>! 👋</h3>
                                        <p class="mb-0 text-muted">ยินดีต้อนรับสู่ระบบสมุดบันทึกหมู่บ้านแม่ต่ำต้นโพธิ์
                                            เริ่มต้นจัดการฟาร์มของคุณได้ที่นี่</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3 font-weight-bold text-dark pl-2" style="border-left: 4px solid #2ecc71;">เมนูลัด
                        </h4>
                    </div>

                    <div class="col-6 col-md-3 mb-4">
                        <a href="dashboard.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <span class="menu-title">ภาพรวม</span>
                            <span class="menu-desc">ดูสถิติและสรุปยอด</span>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 mb-4">
                        <a href="app-calender1.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span class="menu-title">ปฏิทิน</span>
                            <span class="menu-desc">ตารางกิจกรรม</span>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 mb-4">
                        <a href="save-data.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-edit"></i>
                            </div>
                            <span class="menu-title">บันทึกข้อมูล</span>
                            <span class="menu-desc">ลงข้อมูลการเพาะปลูก</span>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 mb-4">
                        <a href="production_list.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <span class="menu-title">รายการผลผลิต</span>
                            <span class="menu-desc">ประวัติการทำเกษตร</span>
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="col-6 col-md-3 mb-4">
                        <a href="admin-user.php" class="menu-card">
                            <div class="icon-wrapper" style="background-color: #fff3cd;"> 
                                <i class="fas fa-users-cog" style="color: #f39c12;"></i>
                            </div>
                            <span class="menu-title">จัดการผู้ใช้งาน</span>
                            <span class="menu-desc">เพิ่ม/ลบ สมาชิก</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php require './footer.html'; ?>

    </div>

    <?php require './script.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>