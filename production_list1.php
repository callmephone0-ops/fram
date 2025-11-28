<?php
session_start();
include "./db.php";

// 1. ตรวจสอบ Login
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$search_term = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    /* ================= TYPOGRAPHY SYSTEM ================= */
    :root {
        --font-primary: 'Prompt', sans-serif;
        --color-primary: #2ecc71;
        --color-text-main: #34495e;
        --color-text-muted: #7f8c8d;
        --bg-soft: #f4f6f9;
    }

    body {
        font-family: var(--font-primary) !important;
        background-color: var(--bg-soft);
        color: var(--color-text-main);
        -webkit-font-smoothing: antialiased; 
        -moz-osx-font-smoothing: grayscale;
        font-size: 0.95rem;
        font-weight: 400;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-primary);
        font-weight: 600;
        color: #2c3e50;
        letter-spacing: -0.5px;
    }

    .content-body { min-height: 85vh; }
    .card-body, .table-responsive { overflow: visible !important; }

    /* ================= CARD STYLE ================= */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        padding: 20px 25px;
        border-radius: 20px 20px 0 0;
    }
    
    .card-title {
        font-size: 1.1rem;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* ================= SEARCH BAR ================= */
    .search-box .form-control {
        border-radius: 25px 0 0 25px;
        border: 1px solid #ddd;
        padding-left: 25px;
        height: 45px;
        font-family: var(--font-primary);
    }
    .search-box .btn {
        border-radius: 0 25px 25px 0;
        background: #27ae60;
        border: 1px solid #27ae60;
        color: white;
        padding: 0 25px;
        font-weight: 500;
    }

    /* ================= TABLE FONTS ================= */
    .table-custom thead th {
        background-color: #f8f9fa;
        color: #95a5a6;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px;
    }
    
    .table-custom tbody td {
        vertical-align: middle !important;
        padding: 18px 15px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 0.95rem;
        color: #555;
        font-weight: 400;
    }
    
    .font-number {
        font-family: var(--font-primary);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .table-custom tbody tr:hover { background-color: #f9fbfd; }

    /* ================= MOBILE FONTS ================= */
    @media (max-width: 991px) {
        .table-responsive-sm { display: none; }

        .mobile-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: visible !important; 
            z-index: 1;
        }
        
        .mobile-card:hover { z-index: 10; transform: translateY(-2px); transition: transform 0.2s; }

        .mobile-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border-radius: 16px 16px 0 0;
        }
        
        .mobile-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text-main);
            margin: 0;
        }
        
        .mobile-card-body { padding: 20px; }
        
        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 8px;
        }
        .data-row:last-child { border-bottom: none; margin-bottom: 0; }
        
        .data-label { 
            color: #999; 
            font-size: 0.9rem; 
            font-weight: 400; 
        }
        .data-value { 
            color: #333; 
            font-weight: 500;
            text-align: right; 
        }
        
        .mobile-card-footer { 
            padding: 15px; 
            background: #fcfcfc; 
            border-radius: 0 0 16px 16px;
            position: relative;
            z-index: 2;
        }

        .btn-mobile-action {
            background-color: #fff;
            color: #555;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            font-weight: 600;
        }
    }

    @media (min-width: 992px) {
        .mobile-view-container { display: none; }
    }
    
    /* Common Dropdown Style */
    .dropdown-menu {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        z-index: 9999 !important;
        font-family: var(--font-primary);
    }
    .dropdown-item { 
        padding: 10px 20px; 
        font-weight: 400;
        color: #555;
    }
    .dropdown-item:hover { background-color: #f0fdf4; color: #2ecc71; }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>

    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-3">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 class="text-dark">🌿 รายการบันทึกผลผลิต</h4>
                            <p class="mb-0 text-muted" style="font-weight: 300;">ข้อมูลการเพาะปลูกทั้งหมดของคุณ</p>
                        </div>
                    </div>
                </div>

                <?php
                if (isset($_GET['status']) && $_GET['status'] == 'deleted') echo '<div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="fa fa-check-circle mr-2"></i> ลบข้อมูลเรียบร้อยแล้ว<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                if (isset($_GET['error']) && $_GET['error'] == 'delete_failed') echo '<div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="fa fa-times-circle mr-2"></i> ผิดพลาด! ไม่สามารถลบข้อมูลได้<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h4 class="card-title text-white m-0"><i class="fa fa-list-alt mr-2"></i> ตารางข้อมูล</h4>
                            </div>
                            <div class="card-body">
                                
                                <form action="" method="GET" class="mb-4">
                                    <div class="input-group search-box mx-auto" style="max-width: 600px;">
                                        <input type="text" name="search" class="form-control form-control-lg" placeholder="🔍 ค้นหาชนิดพืช (เช่น ข้าว, ลำไย)..." value="<?php echo htmlspecialchars($search_term); ?>">
                                        <div class="input-group-append"><button class="btn" type="submit">ค้นหา</button></div>
                                    </div>
                                </form>

                                <?php
                                $current_username = $_SESSION['username'];
                                $search_param = "%" . $search_term . "%";
                                $sql = "SELECT *, (cost_fertilizer + cost_chemicals + cost_labor) AS total_cost_calculated FROM production_cycles WHERE username = ?";
                                if (!empty($search_term)) $sql .= " AND crop_type LIKE ?";
                                $sql .= " ORDER BY id DESC";
                                $stmt = $conn->prepare($sql);
                                if ($stmt) {
                                    if (!empty($search_term)) $stmt->bind_param("ss", $current_username, $search_param);
                                    else $stmt->bind_param("s", $current_username);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $data_rows = $result->fetch_all(MYSQLI_ASSOC);
                                    $stmt->close();
                                } $conn->close();
                                ?>

                                <div class="table-responsive-sm"> 
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>พืช</th>
                                                <th>สายพันธุ์</th>
                                                <th>รอบการผลิต</th>
                                                <th>พื้นที่ (ไร่)</th>
                                                <th>ต้นทุนรวม</th>
                                                <th class="text-center">สถานะ</th>
                                                <th class="text-center">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($data_rows): foreach ($data_rows as $row): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width:40px;height:40px;">
                                                                <i class="fa <?php echo ($row['crop_type']=='ข้าว'?'fa-seedling': ($row['crop_type']=='ยางพารา'?'fa-tree':'fa-apple-alt')); ?> text-success"></i>
                                                            </div>
                                                            <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($row['crop_type']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['variety']); ?></td>
                                                    <td><span class="text-muted"><?php echo htmlspecialchars($row['cycle_code'] ?? '-'); ?></span></td>
                                                    <td><?php echo htmlspecialchars($row['area_rai']); ?></td>
                                                    <td><span class="font-number text-dark">฿<?php echo htmlspecialchars(number_format($row['total_cost_calculated'], 2)); ?></span></td>
                                                    <td class="text-center">
                                                        <?php if ($row['status'] == 'กำลังเพาะปลูก'): ?>
                                                            <span class="badge badge-success px-3 py-2 rounded-pill font-weight-normal"><i class="fa fa-leaf mr-1"></i> เพาะปลูก</span>
                                                        <?php elseif ($row['status'] == 'เก็บเกี่ยวแล้ว'): ?>
                                                            <span class="badge badge-warning text-dark px-3 py-2 rounded-pill font-weight-normal"><i class="fa fa-check-circle mr-1"></i> เก็บเกี่ยวแล้ว</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-light border px-3 py-2 rounded-pill font-weight-normal"><?php echo htmlspecialchars($row['status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn btn-light btn-sm rounded-circle border shadow-sm" type="button" data-toggle="dropdown" data-container="body" style="width: 35px; height: 35px;">
                                                                <i class="fa fa-ellipsis-v text-muted"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="view_details.php?id=<?php echo $row['id']; ?>"><i class="fa fa-eye text-info mr-2"></i> ดูรายละเอียด</a>
                                                                <a class="dropdown-item" href="edit_production_form.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit text-warning mr-2"></i> แก้ไขข้อมูล</a>
                                                                <div class="dropdown-divider"></div>
                                                                <?php if ($row['status'] == 'กำลังเพาะปลูก'): ?>
                                                                    <a class="dropdown-item text-success font-weight-bold" href="harvest_form.php?id=<?php echo $row['id']; ?>"><i class="fa fa-archive mr-2"></i> บันทึกผลผลิต</a>
                                                                <?php endif; ?>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="delete_production.php?id=<?php echo $row['id']; ?>" onclick="return confirm('⚠️ ยืนยันการลบ?');"><i class="fa fa-trash mr-2"></i> ลบข้อมูล</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; else: echo '<tr><td colspan="7" class="text-center py-5 text-muted">ไม่พบข้อมูล</td></tr>'; endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mobile-view-container">
                                    <?php if ($data_rows): foreach ($data_rows as $row): ?>
                                        <div class="mobile-card">
                                            <div class="mobile-card-header">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa <?php echo ($row['crop_type']=='ข้าว'?'fa-seedling': ($row['crop_type']=='ยางพารา'?'fa-tree':'fa-apple-alt')); ?> text-success mr-2" style="font-size: 1.2rem;"></i>
                                                    <h5 class="mobile-card-title"><?php echo htmlspecialchars($row['crop_type']); ?></h5>
                                                </div>
                                                <?php if ($row['status'] == 'กำลังเพาะปลูก'): ?>
                                                    <span class="badge badge-success rounded-pill font-weight-normal"><i class="fa fa-leaf"></i> เพาะปลูก</span>
                                                <?php elseif ($row['status'] == 'เก็บเกี่ยวแล้ว'): ?>
                                                    <span class="badge badge-warning text-dark rounded-pill font-weight-normal"><i class="fa fa-check"></i> เก็บเกี่ยว</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mobile-card-body">
                                                <div class="data-row"><span class="data-label">สายพันธุ์</span><span class="data-value"><?php echo htmlspecialchars($row['variety']); ?></span></div>
                                                <div class="data-row"><span class="data-label">รอบการผลิต</span><span class="data-value text-muted"><?php echo htmlspecialchars($row['cycle_code'] ?? '-'); ?></span></div>
                                                <div class="data-row"><span class="data-label">พื้นที่</span><span class="data-value"><?php echo htmlspecialchars($row['area_rai']); ?> ไร่</span></div>
                                                <div class="data-row pt-2" style="border-top: 1px solid #f0f0f0;"><span class="data-label font-weight-bold">ต้นทุนรวม</span><span class="data-value font-number text-dark">฿<?php echo htmlspecialchars(number_format($row['total_cost_calculated'], 2)); ?></span></div>
                                            </div>
                                            <div class="mobile-card-footer">
                                                <div class="dropdown w-100">
                                                    <button class="btn btn-mobile-action dropdown-toggle" type="button" data-toggle="dropdown" data-container="body">
                                                        <i class="fa fa-cog mr-1"></i> ตัวเลือกจัดการ
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right w-100">
                                                        <a class="dropdown-item" href="view_details.php?id=<?php echo $row['id']; ?>"><i class="fa fa-eye text-info"></i> ดูรายละเอียด</a>
                                                        <a class="dropdown-item" href="edit_production_form.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit text-warning"></i> แก้ไขข้อมูล</a>
                                                        <div class="dropdown-divider"></div>
                                                        <?php if ($row['status'] == 'กำลังเพาะปลูก'): ?>
                                                            <a class="dropdown-item text-success font-weight-bold" href="harvest_form.php?id=<?php echo $row['id']; ?>"><i class="fa fa-archive"></i> บันทึกผลผลิต</a>
                                                        <?php endif; ?>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="delete_production.php?id=<?php echo $row['id']; ?>" onclick="return confirm('⚠️ ยืนยันการลบ?');"><i class="fa fa-trash"></i> ลบข้อมูล</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; else: echo '<div class="text-center py-5 text-muted">ไม่พบข้อมูล</div>'; endif; ?>
                                </div>

                                <div style="height: 150px; clear: both;"></div>

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