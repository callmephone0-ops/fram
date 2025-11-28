<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

// --- 1. Query สำหรับการ์ดสรุป (KPIs) ---
// ... (KPI 1-6 เหมือนเดิม) ...
// KPI 1: รอบการผลิตที่กำลังดำเนินการ
$query_active = "SELECT COUNT(*) AS active_count FROM production_cycles WHERE status = 'กำลังเพาะปลูก'";
$result_active = mysqli_query($conn, $query_active);
$row_active = mysqli_fetch_assoc($result_active);
$active_cycles = $row_active['active_count'];

// KPI 2: รอบการผลิตที่เสร็จสิ้น
$query_completed = "SELECT COUNT(*) AS completed_count FROM production_cycles WHERE status = 'เก็บเกี่ยวแล้ว'";
$result_completed = mysqli_query($conn, $query_completed);
$row_completed = mysqli_fetch_assoc($result_completed);
$completed_cycles = $row_completed['completed_count'];

// KPI 3: ต้นทุนรวมทั้งหมด
$query_cost = "SELECT SUM(cost_fertilizer + cost_chemicals + cost_labor) AS total_all_cost FROM production_cycles";
$result_cost = mysqli_query($conn, $query_cost);
$row_cost = mysqli_fetch_assoc($result_cost);
$total_cost = $row_cost['total_all_cost'] ?: 0; 

// KPI 4: จำนวนชนิดพืชทั้งหมด
$query_crops = "SELECT COUNT(DISTINCT crop_type) AS crop_variety_count FROM production_cycles";
$result_crops = mysqli_query($conn, $query_crops);
$row_crops = mysqli_fetch_assoc($result_crops);
$total_crops = $row_crops['crop_variety_count'];

// KPI 5: จำนวนผู้ดูแลระบบ (Admin)
$query_admins = "SELECT COUNT(*) AS admin_count FROM user WHERE role = 'admin'";
$result_admins = mysqli_query($conn, $query_admins);
$row_admins = mysqli_fetch_assoc($result_admins);
$total_admins = $row_admins['admin_count'];

// KPI 6: จำนวนเกษตรกร (User)
$query_farmers = "SELECT COUNT(*) AS farmer_count FROM user WHERE role = 'user'";
$result_farmers = mysqli_query($conn, $query_farmers);
$row_farmers = mysqli_fetch_assoc($result_farmers);
$total_farmers = $row_farmers['farmer_count'];


// --- 2. Query สำหรับตาราง "รอบการผลิตล่าสุด" (ดึงมา 5 รายการ) ---
$query_recent = "SELECT pc.crop_type, pc.status, CONCAT(u.name, ' ', u.surname) AS recorder_name
                 FROM production_cycles AS pc
                 LEFT JOIN user AS u ON pc.username = u.username
                 ORDER BY pc.created_at DESC
                 LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);
if (!$result_recent) {
    die("SQL Error (Recent): " . mysqli_error($conn));
}


// --- 3. Query สำหรับข้อมูลแผนภูมิ (จำนวนรอบการผลิตตามชนิดพืช) ---
$query_chart = "SELECT crop_type, COUNT(*) AS cycle_count 
                FROM production_cycles 
                GROUP BY crop_type";
$result_chart = mysqli_query($conn, $query_chart);
$chart_labels = [];
$chart_data = [];
while ($row_chart = mysqli_fetch_assoc($result_chart)) {
    $chart_labels[] = $row_chart['crop_type'];
    $chart_data[] = $row_chart['cycle_count'];
}
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);


//
// ===== จุดที่ 1: แก้ไข Query ให้ดึงเฉพาะ 'user' =====
//
// --- 4. Query สำหรับตารางเกษตรกร ---
$query_all_farmers = "SELECT username, name, surname, status 
                      FROM user 
                      WHERE role = 'user' 
                      ORDER BY name ASC";
$result_all_farmers = mysqli_query($conn, $query_all_farmers);
if (!$result_all_farmers) {
    die("SQL Error (All Farmers): " . mysqli_error($conn));
}
// ===============================================
//

?>
<!DOCTYPE html>
<html lang="th">
<?php require './head.html'; ?>
<style>
    /* ... (style เหมือนเดิม) ... */
    .card-body,
    .table th,
    .table td {
        color: #000 !important;
        vertical-align: middle;
    }
    .stat-widget-one .stat-content .stat-text {
        color: #555 !important; 
    }
    .stat-widget-one .stat-content .stat-digit {
        color: #000 !important;
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
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>แดชบอร์ดภาพรวม</h4>
                            <p class="mb-0">สรุปข้อมูลสำคัญของสมุดบันทึกการเกษตร</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">แดชบอร์ด</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-leaf text-warning border-warning"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">กำลังดำเนินการ</div>
                                        <div class="stat-digit"><?php echo $active_cycles; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-check-circle text-success border-success"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">เสร็จสิ้นแล้ว</div>
                                        <div class="stat-digit"><?php echo $completed_cycles; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-money-bill-wave text-info border-info"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">ต้นทุนรวม (บาท)</div>
                                        <div class="stat-digit"><?php echo number_format($total_cost, 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-seedling text-primary border-primary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">ชนิดพืชที่บันทึก</div>
                                        <div class="stat-digit"><?php echo $total_crops; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-user-shield text-danger border-danger"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">ผู้ดูแลระบบ (Admin)</div>
                                        <div class="stat-digit"><?php echo $total_admins; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-xl-3 col-lg-6 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="stat-widget-one">
                                    <div class="stat-icon dib"><i class="fa fa-users text-secondary border-secondary"></i></div>
                                    <div class="stat-content dib">
                                        <div class="stat-text">เกษตรกร (User)</div>
                                        <div class="stat-digit"><?php echo $total_farmers; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-xl-7 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">จำนวนรอบการผลิตตามชนิดพืช</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="cropChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">รอบการผลิตล่าสุด (5 รายการ)</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ผู้บันทึก</th>
                                                <th>ชนิดพืช</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if (mysqli_num_rows($result_recent) > 0):
                                                while ($row = mysqli_fetch_assoc($result_recent)): 
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['recorder_name']); ?></td>
                                                    <td><strong><?php echo htmlspecialchars($row['crop_type']); ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo ($row['status'] == 'เก็บเกี่ยวแล้ว') ? 'success' : 'warning'; ?>">
                                                            <?php echo htmlspecialchars($row['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">ยังไม่มีข้อมูลรอบการผลิต</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="production_list.php" class="btn btn-primary btn-sm btn-block mt-3">ดูรอบการผลิตทั้งหมด</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">เกษตรกรในระบบ (<?php echo $total_farmers; ?> คน)</h4>
                                <a href="admin-register.php" class="btn btn-primary">＋ เพิ่มผู้ใช้ใหม่</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>ชื่อ - นามสกุล</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // ใช้ $result_all_farmers
                                            if (mysqli_num_rows($result_all_farmers) > 0):
                                                while ($row_user = mysqli_fetch_assoc($result_all_farmers)): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row_user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row_user['name'] . ' ' . $row_user['surname']); ?></td>
                                                
                                                <td>
                                                    <?php if ($row_user['status'] == 1): ?>
                                                        <span class="badge badge-success">เปิดใช้งาน</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light">ปิดใช้งาน</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">ยังไม่มีข้อมูลเกษตรกร</td>
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
         <?php require './footer.html'; ?>
    </div>
    <?php require './script.html'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function($) {
            "use strict";
            const chartLabels = <?php echo $chart_labels_json; ?>;
            const chartData = <?php echo $chart_data_json; ?>;
            const chartColors = ['#28a745', '#ffc107', '#007bff', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14'];

            const ctx = document.getElementById('cropChart');
            if (ctx && chartData.length > 0) {
                ctx.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'จำนวนรอบการผลิต',
                            data: chartData,
                            backgroundColor: chartColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            } else if (ctx) {
                const context = ctx.getContext('2d');
                context.textAlign = 'center';
                context.fillStyle = '#888';
                context.font = '16px Arial';
                context.fillText('ยังไม่มีข้อมูลสำหรับแสดงแผนภูมิ', ctx.width / 2, ctx.height / 2);
            }
        })(jQuery);
    </script>

</body>
</html>