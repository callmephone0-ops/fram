<?php
session_start();
// 1. ตรวจสอบ Session และต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "./db.php";

// ---- รับค่าค้นหา ----
$search_query = $_GET['search_query'] ?? '';

// 2. ดึงข้อมูลผู้ใช้ทั้งหมด
$query = "SELECT id, name, surname, username, role, status FROM user";

// 3. เพิ่ม WHERE clause
if (!empty($search_query)) {
    $query .= " WHERE name LIKE ? OR surname LIKE ? OR username LIKE ?";
}

$query .= " ORDER BY name ASC";

// 4. Prepared Statements
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) { die("SQL Prepare Error: " . mysqli_error($conn)); }

// 5. Bind parameter
if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
}

// 6. Execute
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) { die("SQL Error: " . mysqli_stmt_error($stmt)); }
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
        --secondary-color: #27ae60;
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: white;
        overflow: hidden;
    }
    
    .card-header-custom {
        background: white;
        padding: 20px 25px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-title {
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        font-size: 1.2rem;
    }

    /* === Search Bar === */
    .search-box .form-control {
        border-radius: 50px 0 0 50px;
        border: 1px solid #eee;
        background-color: #f9f9f9;
        padding-left: 20px;
        height: 45px;
    }
    .search-box .btn {
        border-radius: 0 50px 50px 0;
        background-color: var(--primary-color);
        border: 1px solid var(--primary-color);
        color: white;
        padding: 0 25px;
    }
    
    /* === Table Design === */
    .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-custom thead th {
        background-color: #f8f9fa;
        color: #666;
        font-weight: 600;
        padding: 15px;
        border-bottom: 2px solid #eee;
        text-align: left;
    }
    .table-custom tbody td {
        padding: 15px;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
        color: #444;
    }
    .table-custom tbody tr:last-child td { border-bottom: none; }
    .table-custom tbody tr:hover { background-color: #fcfcfc; }

    /* === Badges === */
    .badge-pill {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    .badge-role-admin { background-color: #ffebee; color: #c62828; }
    .badge-role-user { background-color: #e3f2fd; color: #1565c0; }
    .badge-status-on { background-color: #e8f5e9; color: #2e7d32; }
    .badge-status-off { background-color: #f5f5f5; color: #616161; }

    /* === Buttons === */
    .btn-add-user {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        transition: all 0.3s;
        font-weight: 500;
    }
    .btn-add-user:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(46, 204, 113, 0.4); }

    .btn-action {
        width: 35px; height: 35px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: 0.2s;
        margin-right: 5px;
    }
    .btn-edit { background-color: #fff3e0; color: #ef6c00; }
    .btn-edit:hover { background-color: #ffe0b2; }
    .btn-delete { background-color: #ffebee; color: #c62828; }
    .btn-delete:hover { background-color: #ffcdd2; }

    /* === Mobile Responsive (Card View) === */
    @media (max-width: 768px) {
        .table-custom thead { display: none; } /* ซ่อนหัวตาราง */
        .table-custom tbody tr {
            display: block;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border: 1px solid #eee;
            padding: 15px;
        }
        .table-custom tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
            text-align: right;
        }
        .table-custom tbody td:last-child { border-bottom: none; justify-content: flex-end; gap: 10px; }
        
        /* ใส่ Label หน้าข้อมูล */
        .table-custom tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #888;
            text-align: left;
            font-size: 0.9rem;
        }
        
        /* ปรับแต่งชื่อให้เด่นในมือถือ */
        .user-name-cell {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-color);
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
                            <h4 style="color: #333; font-weight: 700;">จัดการผู้ใช้งาน</h4>
                            <p class="mb-0 text-muted">ระบบจัดการรายชื่อและสิทธิ์การใช้งาน</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header-custom">
                                <h4 class="card-title"><i class="fa fa-users mr-2"></i> รายชื่อผู้ใช้ทั้งหมด</h4>
                                <a href="admin-register.php" class="btn btn-add-user">
                                    <i class="fa fa-user-plus mr-2"></i> เพิ่มผู้ใช้ใหม่
                                </a>
                            </div>
                            
                            <div class="card-body">
                                <form action="" method="GET" class="mb-4">
                                    <div class="input-group search-box mx-auto" style="max-width: 600px;">
                                        <input type="text" class="form-control" name="search_query"
                                            placeholder="ค้นหา ชื่อ, นามสกุล, เบอร์โทร..."
                                            value="<?php echo htmlspecialchars($search_query); ?>">
                                        <div class="input-group-append">
                                            <button class="btn" type="submit"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>ชื่อ - นามสกุล</th>
                                                <th>เบอร์โทร (Username)</th>
                                                <th>บทบาท</th>
                                                <th>สถานะ</th>
                                                <th class="text-right">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr id="row-user-<?php echo $row['id']; ?>">
                                                    <td data-label="ชื่อ - นามสกุล" class="user-name-cell">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3 d-none d-md-flex" style="width: 40px; height: 40px;">
                                                                <i class="fa fa-user text-muted"></i>
                                                            </div>
                                                            <?php echo htmlspecialchars($row['name'] . ' ' . $row['surname']); ?>
                                                        </div>
                                                    </td>
                                                    <td data-label="เบอร์โทร">
                                                        <span class="text-muted"><?php echo htmlspecialchars($row['username']); ?></span>
                                                    </td>
                                                    <td data-label="บทบาท">
                                                        <span class="badge badge-pill badge-role-<?php echo ($row['role'] == 'admin') ? 'admin' : 'user'; ?>">
                                                            <?php echo ($row['role'] == 'admin') ? '<i class="fa fa-crown mr-1"></i> ผู้ดูแลระบบ' : '<i class="fa fa-user mr-1"></i> เกษตรกร'; ?>
                                                        </span>
                                                    </td>
                                                    <td data-label="สถานะ">
                                                        <span class="badge badge-pill badge-status-<?php echo ($row['status'] == 1) ? 'on' : 'off'; ?>">
                                                            <i class="fa fa-circle" style="font-size: 8px;"></i> 
                                                            <?php echo ($row['status'] == 1) ? 'ปกติ' : 'ระงับ'; ?>
                                                        </span>
                                                    </td>
                                                    <td data-label="จัดการ" class="text-right">
                                                        <a href="user-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-edit" title="แก้ไข">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </a>
                                                        <button onclick="deleteUser(<?php echo $row['id']; ?>)" class="btn btn-action btn-delete" title="ลบ">
                                                            <i class="fa fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            
                                            <?php if (mysqli_num_rows($result) == 0): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        <i class="fa fa-user-slash fa-3x mb-3"></i><br>
                                                        ไม่พบข้อมูลผู้ใช้
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
        function deleteUser(id) {
            // เช็คว่ามี SweetAlert ให้ใช้ไหม
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: "ข้อมูลผู้ใช้และประวัติทั้งหมดจะถูกลบถาวร!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef5350',
                    cancelButtonColor: '#7f8c8d',
                    confirmButtonText: 'ลบผู้ใช้',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceedDeleteUser(id);
                    }
                });
            } else {
                // Fallback ถ้าไม่มี SweetAlert
                if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้คนนี้?")) {
                    proceedDeleteUser(id);
                }
            }
        }

        function proceedDeleteUser(id) {
            fetch('user-delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animation ลบแถวออกแบบนุ่มนวล
                    const row = document.getElementById('row-user-' + id);
                    row.style.transition = "all 0.5s ease";
                    row.style.opacity = "0";
                    row.style.transform = "translateX(50px)";
                    setTimeout(() => row.remove(), 500);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire('เรียบร้อย!', data.message, 'success');
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('ผิดพลาด', data.message, 'error');
                    else alert(data.message);
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