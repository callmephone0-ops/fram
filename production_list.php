<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// ---- ส่วนรับค่าค้นหา ----
$search_query = $_GET['search_query'] ?? '';

// ---- SQL Query ----
$query = "SELECT pc.*, CONCAT(u.name, ' ', u.surname) AS recorder_name,
(pc.cost_fertilizer + pc.cost_chemicals + pc.cost_labor) AS calculated_total_cost
FROM production_cycles AS pc
LEFT JOIN user AS u ON pc.username = u.username";

if (!empty($search_query)) {
    $query .= " WHERE CONCAT(u.name, ' ', u.surname) LIKE ?";
}

$query .= " ORDER BY pc.created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("SQL Prepare Error: " . mysqli_error($conn));
}

if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    mysqli_stmt_bind_param($stmt, "s", $search_param);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="th">
<?php require './head.html'; ?>

<style>
    /* ================= THEME: Deep Forest ================= */
    :root {
        --primary-dark: #143625;
        --primary-green: #27ae60;
        --light-bg: #f7f9f7;
        --card-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        --text-dark: #333;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }

    /* --- [สำคัญ] แก้บัคปุ่มโปรไฟล์กดไม่ได้ --- */
    .header {
        z-index: 1000 !important;
        /* ดัน Header ให้ลอยเหนือเนื้อหา */
    }

    .nav-header {
        z-index: 1001 !important;
        /* ดันโลโก้ให้สูงกว่า Header */
    }

    /* -------------------------------------- */

    /* Card Styling */
    .card-custom {
        border: none;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        background: #fff;
        overflow: visible;
        /* เปิดให้ Dropdown ล้นออกนอกการ์ดได้ */
    }

    .card-header-custom {
        background: #fff;
        padding: 20px 25px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border-radius: 20px 20px 0 0;
    }

    /* Search & Buttons */
    .search-group {
        position: relative;
        max-width: 400px;
        width: 100%;
    }

    .search-input {
        border-radius: 50px;
        padding-left: 20px;
        padding-right: 50px;
        height: 45px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }

    .search-input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
    }

    .search-btn {
        position: absolute;
        right: 5px;
        top: 3px;
        height: 39px;
        width: 39px;
        border-radius: 50%;
        border: none;
        background: var(--primary-dark);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
    }

    .search-btn:hover {
        background: var(--primary-green);
    }

    /* Table Styling */
    .table-custom thead th {
        background-color: #f8fcf9;
        color: var(--primary-dark);
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
        white-space: nowrap;
    }

    .table-custom td {
        vertical-align: middle;
        color: #555;
        font-size: 0.95rem;
    }

    /* Action Button */
    .btn-action-trigger {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-color: #f0f2f5;
        color: #555;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-action-trigger:hover,
    .show>.btn-action-trigger {
        background-color: var(--primary-dark);
        color: #fff;
    }

    /* Dropdown Menu Style */
    .dropdown-menu-custom {
        border: none;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
        border-radius: 12px;
        padding: 8px;
        min-width: 200px;
        z-index: 1050;
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.9rem;
        color: #555;
    }

    .dropdown-item:hover {
        background-color: #f1f8e9;
        color: var(--primary-dark);
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
    }

    /* ================= MOBILE RESPONSIVE FIXES ================= */
    @media (max-width: 991px) {
        .table-responsive { overflow: visible !important; }
        .table-custom thead { display: none; }
        
        /* การ์ดข้อมูลแต่ละแถว */
        .table-custom tbody tr {
            display: block;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 15px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            position: relative; 
        }

        .table-custom td {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-top: none; border-bottom: 1px dashed #f0f0f0;
        }

        .table-custom td:last-child {
            border-bottom: none;
            padding-top: 15px; margin-top: 5px; justify-content: flex-end;
            background: #fafafa;
            margin-left: -20px; margin-right: -20px; margin-bottom: -20px;
            padding-right: 20px; padding-bottom: 15px;
            border-radius: 0 0 15px 15px;
            border-top: 1px solid #eee;
        }

        /* จัดการปุ่ม Dropdown */
        .table-custom td:last-child .dropdown {
            position: relative !important; 
            display: inline-block;
        }
        
        /* --- [ส่วนที่ 1] ตั้งค่าปกติให้เปิดลงล่าง --- */
        .dropdown-menu-custom {
            position: absolute !important;
            top: 100% !important; 
            bottom: auto !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            margin-top: 5px;
            width: 200px;
            z-index: 9999;
        }

        /* --- [ส่วนที่ 2 (เพิ่มใหม่)] สั่งเฉพาะแถวสุดท้าย ให้เด้งขึ้นบน --- */
        /* ใช้เงื่อนไข: เป็นแถวสุดท้าย และ ต้องไม่ใช่แถวแรก (กันกรณีมีข้อมูลแค่แถวเดียวแล้วเด้งไปชน Header) */
        .table-custom tbody tr:last-child:not(:first-child) .dropdown-menu-custom {
            top: auto !important;      /* ยกเลิกการยึดด้านบน */
            bottom: 100% !important;   /* ให้ก้นเมนูชนกับขอบบนปุ่ม */
            margin-bottom: 5px;        /* เว้นระยะห่าง */
            margin-top: 0 !important;  /* ยกเลิกระยะห่างด้านบน */
            
            /* เพิ่มเงาให้ดูเหมือนลอยขึ้น */
            box-shadow: 0 -5px 25px rgba(0,0,0,0.15); 
        }

        .table-custom td::before {
            content: attr(data-label); font-weight: 700; color: var(--primary-dark); font-size: 0.9rem;
        }

        .btn-add-new { width: 100%; margin-top: 10px; }
        .card-header-custom { flex-direction: column; align-items: stretch; }
        .search-group { max-width: 100%; }
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

                <div class="row page-titles mx-0 mb-3">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 style="color: #143625; font-weight: 700;">
                                <i class="fa fa-list-ul mr-2"></i> รายการรอบการผลิต
                            </h4>
                            <p class="mb-0 text-muted">ประวัติและสถานะการเพาะปลูกทั้งหมด</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">

                            <div class="card-header-custom">
                                <h5 class="card-title mb-0 d-none d-md-block">ข้อมูลการผลิต</h5>

                                <form action="" method="GET" class="search-group">
                                    <input type="text" class="form-control search-input" name="search_query"
                                        placeholder="ค้นหาชื่อผู้บันทึก..."
                                        value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button class="search-btn" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </form>

                                <a href="production_form.php" class="btn btn-success btn-rounded btn-add-new">
                                    <i class="fa fa-plus mr-2"></i> เพิ่มรอบผลิต
                                </a>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive" style="min-height: 300px;">
                                    <table class="table table-custom table-hover">
                                        <thead>
                                            <tr>
                                                <th>ผู้บันทึก</th>
                                                <th>ชนิดพืช</th>
                                                <th>รอบ/พันธุ์</th>
                                                <th>วันที่ปลูก</th>
                                                <th>สถานะ</th>
                                                <th class="text-right">ต้นทุน (บาท)</th>
                                                <th class="text-center" style="width: 100px;">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr id="row-<?php echo $row['id']; ?>">

                                                    <td data-label="ผู้บันทึก">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-2 d-none d-md-block">
                                                                <span
                                                                    class="avatar avatar-sm rounded-circle bg-light text-success d-flex align-items-center justify-content-center"
                                                                    style="width:35px; height:35px;">
                                                                    <i class="fa fa-user"></i>
                                                                </span>
                                                            </div>
                                                            <span
                                                                class="font-weight-bold"><?php echo htmlspecialchars($row['recorder_name']); ?></span>
                                                        </div>
                                                    </td>

                                                    <td data-label="ชนิดพืช">
                                                        <span
                                                            class="text-dark font-weight-bold"><?php echo htmlspecialchars($row['crop_type']); ?></span>
                                                    </td>

                                                    <td data-label="รอบ/พันธุ์">
                                                        <span
                                                            class="text-muted small"><?php echo htmlspecialchars($row['cycle_code'] ?: $row['variety']); ?></span>
                                                    </td>

                                                    <td data-label="วันที่ปลูก">
                                                        <span><i class="far fa-calendar-alt text-muted mr-1"></i>
                                                            <?php echo date("d/m/Y", strtotime($row['planting_date'])); ?></span>
                                                    </td>

                                                    <td data-label="สถานะ">
                                                        <?php if ($row['status'] == 'เก็บเกี่ยวแล้ว'): ?>
                                                            <span class="badge badge-success"
                                                                style="background-color: #d4edda; color: #155724; border-radius: 50px;"><i
                                                                    class="fa fa-check-circle mr-1"></i> เก็บเกี่ยวแล้ว</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning"
                                                                style="background-color: #fff3cd; color: #856404; border-radius: 50px;"><i
                                                                    class="fa fa-clock mr-1"></i> กำลังปลูก</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td data-label="ต้นทุน" class="text-md-right text-right">
                                                        <span
                                                            class="font-weight-bold text-danger"><?php echo number_format($row['calculated_total_cost'], 2); ?></span>
                                                    </td>

                                                    <td data-label="จัดการ (คลิก)" class="text-center">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn-action-trigger mx-auto shadow-sm"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false" data-boundary="viewport">
                                                                <i class="fa fa-ellipsis-h"></i>
                                                            </button>

                                                            <div
                                                                class="dropdown-menu dropdown-menu-right dropdown-menu-custom">

                                                                <div class="dropdown-header d-block d-md-none text-muted">
                                                                    จัดการข้อมูล</div>

                                                                <a class="dropdown-item"
                                                                    href="harvest_form1.php?id=<?php echo $row['id']; ?>">
                                                                    <i class="fa fa-eye text-info"></i> ดูรายละเอียด
                                                                </a>

                                                                <a class="dropdown-item"
                                                                    href="edit_production_form1.php?id=<?php echo $row['id']; ?>">
                                                                    <i class="fa fa-wrench text-warning"></i> แก้ไขข้อมูล
                                                                </a>

                                                                <?php if ($row['status'] == 'กำลังเพาะปลูก'): ?>
                                                                    <a class="dropdown-item"
                                                                        href="harvest_form1.php?id=<?php echo $row['id']; ?>">
                                                                        <i class="fa fa-leaf text-success"></i> บันทึกผลผลิต
                                                                    </a>
                                                                <?php endif; ?>

                                                                <div class="dropdown-divider"></div>

                                                                <a class="dropdown-item text-danger"
                                                                    href="javascript:void(0)"
                                                                    onclick="deleteProduction(<?php echo $row['id']; ?>)">
                                                                    <i class="fa fa-trash"></i> ลบรายการ
                                                                </a>

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>

                                            <?php if (mysqli_num_rows($result) == 0): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-5 text-muted">
                                                        <i class="fa fa-folder-open fa-3x mb-3"
                                                            style="opacity: 0.3;"></i><br>
                                                        ไม่พบข้อมูลรอบการผลิต
                                                    </td>
                                                </tr>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require './script.html'; ?>

    <script>
        function deleteProduction(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: "ข้อมูลจะถูกลบถาวร ไม่สามารถกู้คืนได้!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceedDelete(id);
                    }
                });
            } else {
                if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?")) {
                    proceedDelete(id);
                }
            }
        }

        function proceedDelete(id) {
            fetch('delete_production.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#row-' + id).fadeOut(300, function () { $(this).remove(); });
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('สำเร็จ!', data.message, 'success');
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('ผิดพลาด!', data.message, 'error');
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                });
        }
    </script>
</body>

</html>