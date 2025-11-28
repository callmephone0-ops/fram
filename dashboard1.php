<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
include "./db.php";

$current_username = $_SESSION['username'];

// ================== DATA FETCHING ==================

// 1. KPI Data (ข้อมูลสรุปยอด)
$sql_kpi = "SELECT SUM(profit) as total_profit, SUM(total_revenue) as total_revenue, SUM(total_cost) as total_cost, COUNT(id) as cycle_count FROM production_cycles WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ?";
$stmt_kpi = $conn->prepare($sql_kpi);
$stmt_kpi->bind_param("s", $current_username);
$stmt_kpi->execute();
$kpi_data = $stmt_kpi->get_result()->fetch_assoc();
$stmt_kpi->close();

// 2. Bar Chart Data (กำไรตามพืช)
$sql_profit = "SELECT crop_type, SUM(profit) as total_profit FROM production_cycles WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ? GROUP BY crop_type";
$stmt_profit = $conn->prepare($sql_profit);
$stmt_profit->bind_param("s", $current_username);
$stmt_profit->execute();
$profit_res = $stmt_profit->get_result();
$crop_labels = []; $profit_data = [];
while ($row = $profit_res->fetch_assoc()) { $crop_labels[] = $row['crop_type']; $profit_data[] = $row['total_profit']; }
$stmt_profit->close();

// 3. Doughnut Chart Data (สัดส่วนรายรับ)
$sql_revenue = "SELECT crop_type, SUM(total_revenue) as total_revenue FROM production_cycles WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ? GROUP BY crop_type";
$stmt_rev = $conn->prepare($sql_revenue);
$stmt_rev->bind_param("s", $current_username);
$stmt_rev->execute();
$rev_res = $stmt_rev->get_result();
$rev_labels = []; $rev_data = [];
while ($row = $rev_res->fetch_assoc()) { $rev_labels[] = $row['crop_type']; $rev_data[] = $row['total_revenue']; }
$stmt_rev->close();

// 4. Recent List Data (รายการล่าสุด 5 รายการ)
$sql_recent = "SELECT * FROM production_cycles WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ? ORDER BY harvest_date DESC LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("s", $current_username);
$stmt_recent->execute();
$recent_result = $stmt_recent->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    :root {
        --primary-green: #2ecc71;
        --dark-green: #27ae60;
        --soft-bg: #f3f6f9;
        --text-dark: #2c3e50;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--soft-bg);
        color: var(--text-dark);
    }

    .content-body {
        min-height: 90vh;
        padding-bottom: 50px;
    }

    /* === Dashboard Cards === */
    .dashboard-card {
        background: #fff;
        border: none;
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--card-shadow);
        transition: transform 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
    }

    .card-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }

    /* Themes */
    .theme-profit .card-icon-wrapper { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
    .theme-revenue .card-icon-wrapper { background: rgba(52, 152, 219, 0.15); color: #2980b9; }
    .theme-cost .card-icon-wrapper { background: rgba(231, 76, 60, 0.15); color: #c0392b; }
    .theme-cycle .card-icon-wrapper { background: rgba(241, 196, 15, 0.15); color: #f39c12; }

    .card-label { font-size: 0.95rem; color: #7f8c8d; font-weight: 500; }
    .card-value { font-size: 1.8rem; font-weight: 600; color: #2c3e50; margin-top: 5px; }

    /* === Chart Cards === */
    .chart-card {
        background: #fff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        height: 100%;
    }
    .chart-title { font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 20px; }

    /* === Recent List (Mobile Friendly) === */
    .list-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .list-item-custom {
        transition: background-color 0.2s;
    }
    .list-item-custom:hover {
        background-color: #fcfcfc;
    }
    .crop-icon-circle {
        width: 45px; 
        height: 45px; 
        background-color: #f1f8e9; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-size: 1.2rem;
        color: #2e7d32;
    }

    /* === Responsive Fixes === */
    @media (max-width: 768px) {
        .card-value { font-size: 1.5rem; }
        .dashboard-card { padding: 20px; }
        .chart-container { height: 300px !important; }
    }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>

    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row mb-4 align-items-center">
                    <div class="col-12">
                        <h3 class="font-weight-bold">📊 ภาพรวมฟาร์ม</h3>
                        <p class="text-muted">สรุปข้อมูลผลผลิตของคุณ: <?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></p>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-6 col-md-6 col-xl-3">
                        <div class="dashboard-card theme-profit">
                            <div class="card-icon-wrapper"><i class="fa fa-wallet"></i></div>
                            <div class="card-label">กำไรสุทธิ</div>
                            <div class="card-value">฿<?php echo number_format($kpi_data['total_profit'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-xl-3">
                        <div class="dashboard-card theme-revenue">
                            <div class="card-icon-wrapper"><i class="fa fa-coins"></i></div>
                            <div class="card-label">รายรับรวม</div>
                            <div class="card-value">฿<?php echo number_format($kpi_data['total_revenue'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-xl-3">
                        <div class="dashboard-card theme-cost">
                            <div class="card-icon-wrapper"><i class="fa fa-file-invoice-dollar"></i></div>
                            <div class="card-label">ต้นทุนรวม</div>
                            <div class="card-value">฿<?php echo number_format($kpi_data['total_cost'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-xl-3">
                        <div class="dashboard-card theme-cycle">
                            <div class="card-icon-wrapper"><i class="fa fa-check-circle"></i></div>
                            <div class="card-label">รอบที่สำเร็จ</div>
                            <div class="card-value"><?php echo number_format($kpi_data['cycle_count'] ?? 0); ?> รอบ</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="chart-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="chart-title m-0">กำไร/ขาดทุน แยกตามพืช</h5>
                                <i class="fa fa-chart-bar text-muted"></i>
                            </div>
                            <div class="chart-container" style="position: relative; height:350px;">
                                <canvas id="profitByCropChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="chart-title m-0">สัดส่วนรายรับ</h5>
                                <i class="fa fa-chart-pie text-muted"></i>
                            </div>
                            <div class="chart-container" style="position: relative; height:350px;">
                                <canvas id="revenueByCropChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                            <h4 class="mb-2 mb-md-0 font-weight-bold" style="font-size: 1.2rem; color: #34495e;">
                                <i class="fa fa-history mr-2 text-muted"></i> รายการล่าสุด
                            </h4>
                            <div class="btn-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                                <a href="export_csv.php" class="btn btn-sm btn-white text-success font-weight-bold border px-3">CSV</a>
                                <a href="export_excel.php" class="btn btn-sm btn-white text-success font-weight-bold border px-3">Excel</a>
                                <a href="export_pdf.php" class="btn btn-sm btn-white text-success font-weight-bold border px-3">PDF</a>
                            </div>
                        </div>

                        <div class="card list-card border-0">
                            <div class="list-group list-group-flush">
                                <?php if ($recent_result->num_rows > 0): ?>
                                    <?php while ($row = $recent_result->fetch_assoc()): ?>
                                        <div class="list-group-item p-3 border-0 border-bottom list-item-custom">
                                            <div class="d-flex align-items-center justify-content-between">
                                                
                                                <div class="d-flex align-items-center">
                                                    <div class="crop-icon-circle mr-3">
                                                        <i class="fa <?php echo ($row['crop_type']=='ข้าว'?'fa-seedling': ($row['crop_type']=='ยางพารา'?'fa-tree':'fa-apple-alt')); ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark"><?php echo htmlspecialchars($row['crop_type']); ?></h6>
                                                        <small class="text-muted" style="font-size: 0.85rem;">
                                                            <?php echo htmlspecialchars($row['variety']); ?> 
                                                            <span class="text-light mx-1">|</span> 
                                                            <?php echo date("d/m/y", strtotime($row['harvest_date'])); ?>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="text-right">
                                                    <h6 class="mb-0 font-weight-bold <?php echo ($row['profit'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo ($row['profit'] >= 0 ? '+' : '') . number_format($row['profit'], 0); ?>
                                                    </h6>
                                                    <small class="text-muted" style="font-size: 0.75rem;">กำไรสุทธิ</small>
                                                </div>

                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa fa-inbox fa-3x mb-3 text-light"></i><br>ยังไม่มีข้อมูลการเก็บเกี่ยว
                                    </div>
                                <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.font.family = "'Prompt', sans-serif";
            Chart.defaults.color = '#7f8c8d';

            // Profit Chart
            new Chart(document.getElementById('profitByCropChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($crop_labels); ?>,
                    datasets: [{
                        label: 'กำไร (บาท)',
                        data: <?php echo json_encode($profit_data); ?>,
                        backgroundColor: <?php echo json_encode(array_map(function($p) { return $p >= 0 ? '#2ecc71' : '#e74c3c'; }, $profit_data)); ?>,
                        borderRadius: 6,
                        barThickness: 25,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
                }
            });

            // Revenue Chart
            new Chart(document.getElementById('revenueByCropChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($rev_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($rev_data); ?>,
                        backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#9b59b6', '#e67e22'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
                }
            });
        });
    </script>
</body>
</html>