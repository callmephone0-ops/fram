<?php
session_start();
include "./db.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_username = $_SESSION['username'];
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; 

if ($id <= 0) { echo "<script>alert('ID ไม่ถูกต้อง'); window.history.back();</script>"; exit(); }

// ดึงข้อมูล
$sql = "SELECT *, (cost_fertilizer + cost_chemicals + cost_labor) AS total_cost_calculated 
        FROM production_cycles 
        WHERE id = ? AND (username = ? OR ? = 'admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $id, $current_username, $current_role); 
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) { echo "<script>alert('ไม่พบข้อมูล'); window.history.back();</script>"; exit(); }
$stmt->close();

// --- คำนวณข้อมูลเพิ่มเติม (Analysis) ---
$area = floatval($data['area_rai']);
$total_cost = floatval($data['total_cost_calculated']);
$profit = floatval($data['profit']);
$harvest_kg = floatval($data['harvest_kg']);

// 1. คำนวณวัน
$p_date = new DateTime($data['planting_date']);
$h_date = ($data['status'] == 'เก็บเกี่ยวแล้ว' && $data['harvest_date']) ? new DateTime($data['harvest_date']) : new DateTime();
$days_count = $p_date->diff($h_date)->days;

// 2. คำนวณต่อไร่ (ป้องกันการหารด้วย 0)
$cost_per_rai = ($area > 0) ? $total_cost / $area : 0;
$yield_per_rai = ($area > 0) ? $harvest_kg / $area : 0;
$profit_per_rai = ($area > 0) ? $profit / $area : 0;

function format_num($num) { return number_format(floatval($num), 2); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    :root { --primary: #2ecc71; --dark-green: #27ae60; --bg-soft: #f4f6f9; --text-main: #333; }
    body { font-family: 'Prompt', sans-serif; background-color: var(--bg-soft); color: var(--text-main); }
    .content-body { min-height: 85vh; }

    /* Cards */
    .card-custom { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: white; margin-bottom: 20px; overflow: hidden; }
    .card-header-custom { background: linear-gradient(135deg, #143625, #27ae60); padding: 25px; color: white; }
    
    /* Mini Stats Card */
    .stat-card { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; height: 100%; }
    .stat-label { font-size: 0.85rem; color: #888; font-weight: 500; }
    .stat-value { font-size: 1.5rem; font-weight: 600; color: #333; margin-top: 5px; }
    .stat-icon { float: right; font-size: 2rem; opacity: 0.1; color: #000; }

    /* Detail Rows */
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #eee; font-size: 0.95rem; }
    .detail-row:last-child { border-bottom: none; }
    .dt-label { color: #666; }
    .dt-val { font-weight: 500; color: #222; }

    /* Badges */
    .badge-status { font-size: 0.9rem; padding: 8px 15px; border-radius: 30px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>

    <div id="main-wrapper">
        <?php if($_SESSION['role'] == 'admin') { require './header.html'; require './sidebar.html'; } else { require './header1.html'; require './sidebar1.html'; } ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="javascript:history.back()" class="btn btn-light rounded-pill px-3 shadow-sm"><i class="fa fa-arrow-left mr-2"></i> ย้อนกลับ</a>
                </div>

                <div class="card card-custom">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="text-white-50 mb-1 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">ข้อมูลการเพาะปลูก</h5>
                                <h2 class="text-white font-weight-bold m-0">
                                    <i class="fa <?php echo ($data['crop_type']=='ข้าว'?'fa-seedling':($data['crop_type']=='ยางพารา'?'fa-tree':'fa-lemon')); ?> mr-2"></i>
                                    <?php echo htmlspecialchars($data['crop_type']); ?>
                                </h2>
                                <div class="mt-2 opacity-75"><i class="fa fa-tag mr-1"></i> พันธุ์: <?php echo htmlspecialchars($data['variety']); ?></div>
                            </div>
                            <span class="badge-status">
                                <i class="fa fa-circle mr-1" style="font-size: 8px; color: <?php echo ($data['status']=='เก็บเกี่ยวแล้ว'?'#4caf50':'#ffeb3b'); ?>"></i> 
                                <?php echo htmlspecialchars($data['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <i class="fa fa-clock stat-icon text-primary"></i>
                            <div class="stat-label">ระยะเวลา</div>
                            <div class="stat-value"><?php echo $days_count; ?> <small style="font-size: 0.9rem;">วัน</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="stat-card">
                            <i class="fa fa-map-marked-alt stat-icon text-success"></i>
                            <div class="stat-label">พื้นที่</div>
                            <div class="stat-value"><?php echo $area; ?> <small style="font-size: 0.9rem;">ไร่</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <i class="fa fa-wallet stat-icon text-danger"></i>
                            <div class="stat-label">ต้นทุนรวม</div>
                            <div class="stat-value text-danger"><?php echo format_num($total_cost); ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <i class="fa fa-chart-line stat-icon text-info"></i>
                            <div class="stat-label">ต้นทุนต่อไร่</div>
                            <div class="stat-value text-info"><?php echo format_num($cost_per_rai); ?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="card card-custom">
                            <div class="card-body p-4">
                                <h5 class="font-weight-bold mb-4 text-primary"><i class="fa fa-file-alt mr-2"></i> รายละเอียด</h5>
                                <div class="detail-row"><span class="dt-label">รอบการผลิต</span> <span class="dt-val"><?php echo htmlspecialchars($data['cycle_code'] ?? '-'); ?></span></div>
                                <div class="detail-row"><span class="dt-label">วันที่เริ่มปลูก</span> <span class="dt-val"><?php echo date("d/m/Y", strtotime($data['planting_date'])); ?></span></div>
                                <div class="detail-row"><span class="dt-label">วิธีการปลูก</span> <span class="dt-val"><?php echo htmlspecialchars($data['planting_method'] ?? '-'); ?></span></div>
                                <div class="detail-row"><span class="dt-label">จำนวนต้น</span> <span class="dt-val"><?php echo htmlspecialchars($data['plant_count'] ?? '-'); ?> ต้น</span></div>
                            </div>
                        </div>

                        <?php if ($data['status'] == 'เก็บเกี่ยวแล้ว'): ?>
                        <div class="card card-custom bg-white border-success" style="border: 1px solid #e8f5e9;">
                            <div class="card-body p-4">
                                <h5 class="font-weight-bold mb-4 text-success"><i class="fa fa-hand-holding-usd mr-2"></i> สรุปผลการเก็บเกี่ยว</h5>
                                <div class="row text-center mb-4">
                                    <div class="col-4 border-right">
                                        <div class="text-muted small">ผลผลิตรวม</div>
                                        <div class="font-weight-bold h5 mt-1"><?php echo format_num($harvest_kg); ?> <small>กก.</small></div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="text-muted small">ราคาขาย</div>
                                        <div class="font-weight-bold h5 mt-1"><?php echo format_num($data['price_per_kg']); ?> <small>฿</small></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">รายรับรวม</div>
                                        <div class="font-weight-bold h5 mt-1 text-success"><?php echo format_num($data['total_revenue']); ?> <small>฿</small></div>
                                    </div>
                                </div>

                                <div class="alert <?php echo ($profit>=0)?'alert-success':'alert-danger'; ?> mb-0 d-flex justify-content-between align-items-center">
                                    <strong><i class="fa <?php echo ($profit>=0)?'fa-arrow-up':'fa-arrow-down'; ?> mr-2"></i> กำไรสุทธิ</strong>
                                    <span class="h4 mb-0 font-weight-bold"><?php echo format_num($profit); ?> บาท</span>
                                </div>
                                
                                
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-5">
                        <div class="card card-custom">
                            <div class="card-body p-4">
                                <h5 class="font-weight-bold mb-4 text-dark"><i class="fa fa-chart-pie mr-2"></i> สัดส่วนต้นทุน</h5>
                                <div style="height: 250px; position: relative;">
                                    <canvas id="costChart"></canvas>
                                </div>
                                <div class="mt-4">
                                    <div class="detail-row"><span class="dt-label"><i class="fa fa-circle text-warning mr-2" style="font-size:8px"></i> ค่าปุ๋ย</span> <span class="dt-val"><?php echo format_num($data['cost_fertilizer']); ?></span></div>
                                    <div class="detail-row"><span class="dt-label"><i class="fa fa-circle text-danger mr-2" style="font-size:8px"></i> ค่ายา/เคมี</span> <span class="dt-val"><?php echo format_num($data['cost_chemicals']); ?></span></div>
                                    <div class="detail-row"><span class="dt-label"><i class="fa fa-circle text-info mr-2" style="font-size:8px"></i> ค่าแรง</span> <span class="dt-val"><?php echo format_num($data['cost_labor']); ?></span></div>
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
        // สร้างกราฟวงกลมแสดงต้นทุน
        var ctx = document.getElementById('costChart').getContext('2d');
        var costChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ค่าปุ๋ย', 'ค่ายา/เคมี', 'ค่าแรง'],
                datasets: [{
                    data: [
                        <?php echo $data['cost_fertilizer']; ?>, 
                        <?php echo $data['cost_chemicals']; ?>, 
                        <?php echo $data['cost_labor']; ?>
                    ],
                    backgroundColor: ['#f39c12', '#e74c3c', '#3498db'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false } // ซ่อน Legend ในกราฟ (เราทำแยกไว้ด้านล่างแล้ว)
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>