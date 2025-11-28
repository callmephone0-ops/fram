<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<style>
    body {
        font-family: 'Prompt', sans-serif;
        background-color: #f8f9fa;
    }

    .content-body {
        min-height: 85vh;
    }

    /* === Carousel Styling === */
    .carousel-item img {
        height: 350px; /* ความสูงคงที่สำหรับจอใหญ่ */
        object-fit: cover; /* ป้องกันภาพเบี้ยว */
        border-radius: 15px;
    }
    /* ปรับความสูงรูปสำหรับมือถือ */
    @media (max-width: 768px) {
        .carousel-item img {
            height: 200px;
        }
    }

    /* === Welcome Card === */
    .welcome-card {
        background: linear-gradient(135deg, #1A592D 0%, #2ecc71 100%);
        border: none;
        border-radius: 15px;
        color: white;
        box-shadow: 0 4px 15px rgba(26, 89, 45, 0.2);
    }
    .welcome-text h3 {
        color: white;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .welcome-text p {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 300;
    }
    .welcome-icon i {
        font-size: 3.5rem;
        color: rgba(255, 255, 255, 0.8);
    }

    /* === Menu Action Cards === */
    .menu-card {
        background: white;
        border: none;
        border-radius: 15px;
        padding: 25px 15px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        height: 100%; /* ให้การ์ดสูงเท่ากันในแถว */
        text-decoration: none; /* ลบขีดเส้นใต้ลิงก์ */
        display: block;
        border-bottom: 4px solid transparent;
    }

    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(26, 89, 45, 0.15);
        border-bottom: 4px solid #1A592D;
    }

    .icon-wrapper {
        width: 70px;
        height: 70px;
        background-color: #f1f8e9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        transition: 0.3s;
    }

    .menu-card:hover .icon-wrapper {
        background-color: #1A592D;
    }

    .menu-card i {
        font-size: 1.8rem;
        color: #1A592D;
        transition: 0.3s;
    }

    .menu-card:hover i {
        color: white;
    }

    .menu-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        display: block;
    }

    .menu-desc {
        font-size: 0.85rem;
        color: #777;
        margin: 0;
        display: block;
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
        require './header1.html';
        require './sidebar1.html';
        ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-12">
                        <div id="heroCarousel" class="carousel slide shadow-sm rounded-lg" data-bs-ride="carousel" style="border-radius: 15px; overflow: hidden;">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="./images/ภาพ2.png" class="d-block w-100" alt="Farm Overview">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="welcome-card p-4">
                            <div class="d-flex align-items-center">
                                <div class="welcome-icon me-4 d-none d-sm-block">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <div class="welcome-text">
                                    <h3>👋 สวัสดี, <?php echo $display_name; ?>!</h3>
                                    <p class="mb-0">ยินดีต้อนรับสู่ระบบสมุดบันทึกการเกษตร จัดการฟาร์มของคุณได้ง่ายๆ ที่นี่</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 g-3 row-cols-2 row-cols-lg-4">
                    
                    <div class="col">
                        <a href="dashboard1.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span class="menu-title">ภาพรวม</span>
                            <span class="menu-desc d-none d-sm-block">ดูสถิติและข้อมูลสรุป</span>
                        </a>
                    </div>

                    <div class="col">
                        <a href="app-calender.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span class="menu-title">ปฏิทิน</span>
                            <span class="menu-desc d-none d-sm-block">ตารางกิจกรรมการเกษตร</span>
                        </a>
                    </div>

                    <div class="col">
                        <a href="save-data1.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-edit"></i> </div>
                            <span class="menu-title">บันทึกข้อมูล</span>
                            <span class="menu-desc d-none d-sm-block">ลงข้อมูลการเพาะปลูก</span>
                        </a>
                    </div>

                    <div class="col">
                        <a href="production_list1.php" class="menu-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <span class="menu-title">ผลผลิต</span>
                            <span class="menu-desc d-none d-sm-block">จัดการข้อมูลผลผลิต</span>
                        </a>
                    </div>

                </div> </div>
        </div>

        <?php require './footer.html'; ?>
    </div>

    <?php require './script.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>