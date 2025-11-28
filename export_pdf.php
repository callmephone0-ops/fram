<?php
// export_production_cycles_pdf.php
session_start();
require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ---------- 1) ตั้งค่าการเชื่อมต่อ/ภาษาข้อมูล ----------
$conn->set_charset("utf8");

// ---------- 2) ดึงข้อมูล เฉพาะของผู้ใช้คนนั้น ----------
if (!isset($_SESSION['username'])) {
    die('ไม่พบ session ผู้ใช้งาน');
}
$currentUser = $_SESSION['username'];

// ใช้ prepared statement ป้องกัน SQL injection
$sql = "SELECT crop_type, cycle_code, variety, harvest_date, total_revenue, total_cost, profit
        FROM production_cycles
        WHERE status = 'เก็บเกี่ยวแล้ว' AND username = ?
        ORDER BY harvest_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $currentUser);
$stmt->execute();
$result = $stmt->get_result();


// ---------- 3) ตั้งค่า Dompdf ให้รองรับฟอนต์ไทย ----------
$projectRoot = __DIR__;               // โฟลเดอร์โปรเจกต์ปัจจุบัน
$fontDir = $projectRoot . '/fonts';
$fontCache = $projectRoot . '/fonts_cache';
if (!is_dir($fontCache)) {
    @mkdir($fontCache, 0777, true);
}

$options = new Options();
// อนุญาตให้โหลดไฟล์โลคอล (ฟอนต์) และกำหนดพื้นที่ chroot ให้ชี้มาที่ root โปรเจกต์
$options->set('isRemoteEnabled', true);
$options->set('chroot', $projectRoot);
$options->set('fontDir', $fontDir);
$options->set('fontCache', $fontCache);
// ตั้ง Default Font เป็น Sarabun (จะใช้เมื่อ CSS ระบุ 'Sarabun')
$options->set('defaultFont', 'Sarabun');

$dompdf = new Dompdf($options);

// ---------- 4) สร้าง HTML ----------
ob_start();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานรอบการผลิตที่เก็บเกี่ยวแล้ว</title>
    <style>
        /* ฝังฟอนต์ Sarabun จากโฟลเดอร์ /fonts (ภายใต้ chroot) */
        @font-face {
            font-family: 'Sarabun';
            src: url('/fonts/Sarabun-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Sarabun';
            src: url('/fonts/Sarabun-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        /* (ถ้ามีไฟล์) */
        @font-face {
            font-family: 'Sarabun';
            src: url('/fonts/Sarabun-Italic.ttf') format('truetype');
            font-weight: normal;
            font-style: italic;
        }

        @font-face {
            font-family: 'Sarabun';
            src: url('/fonts/Sarabun-BoldItalic.ttf') format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        html,
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 12pt;
            color: #222;
        }

        h1 {
            font-size: 18pt;
            margin: 0 0 6px 0;
        }

        .sub {
            font-size: 10pt;
            color: #666;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #efefef;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        td.num {
            text-align: right;
            white-space: nowrap;
        }

        .muted {
            color: #666;
            font-size: 10pt;
        }

        .summary {
            margin-top: 10px;
            font-size: 11pt;
        }

        @page {
            margin: 20mm 14mm 18mm 14mm;
        }

        header {
            position: fixed;
            top: -14mm;
            left: 0;
            right: 0;
            text-align: center;
        }

        footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10pt;
            color: #666;
        }
    </style>
</head>

<body>
    <header>
        <!-- ใส่หัวกระดาษถ้าต้องการโลโก้ สามารถใช้ <img src="/path/to/logo.png"> ได้ -->
    </header>

    <h1>รายงานรอบการผลิต (สถานะ: เก็บเกี่ยวแล้ว)</h1>
    <div class="sub">พิมพ์เมื่อ: <?php echo date('d/m/Y H:i'); ?></div>

    <table>
        <thead>
            <tr>
                <th>ชนิดพืช</th>
                <th>รหัสรอบการผลิต</th>
                <th>พันธุ์</th>
                <th>วันเก็บเกี่ยว</th>
                <th>รายได้รวม (บาท)</th>
                <th>ต้นทุนรวม (บาท)</th>
                <th>กำไร (บาท)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sum_rev = 0;
            $sum_cost = 0;
            $sum_profit = 0;

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $harvest = $row['harvest_date'] ? date('d/m/Y', strtotime($row['harvest_date'])) : '-';
                    $rev = is_null($row['total_revenue']) ? 0 : (float) $row['total_revenue'];
                    $cost = is_null($row['total_cost']) ? 0 : (float) $row['total_cost'];
                    $profit = is_null($row['profit']) ? ($rev - $cost) : (float) $row['profit'];

                    $sum_rev += $rev;
                    $sum_cost += $cost;
                    $sum_profit += $profit;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['crop_type'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['cycle_code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['variety'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $harvest; ?></td>
                        <td class="num"><?php echo number_format($rev, 2); ?></td>
                        <td class="num"><?php echo number_format($cost, 2); ?></td>
                        <td class="num"><?php echo number_format($profit, 2); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align:center;">ไม่มีข้อมูลที่เก็บเกี่ยวแล้ว</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">รวม</th>
                <th class="num"><?php echo number_format($sum_rev, 2); ?></th>
                <th class="num"><?php echo number_format($sum_cost, 2); ?></th>
                <th class="num"><?php echo number_format($sum_profit, 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <span class="muted">หมายเหตุ: ตัวเลขเป็นไปตามข้อมูลล่าสุดจากฐานข้อมูล production_cycles</span>
    </div>

    <footer>
        หน้าที่ {PAGE_NUM} / {PAGE_COUNT}
    </footer>
</body>

</html>
<?php
$html = ob_get_clean();

// ---------- 5) เรนเดอร์ PDF ----------
$dompdf->loadHtml($html, 'UTF-8');
// แนวตั้ง A4 (ถ้าอยากแนวนอนใช้ 'landscape')
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ---------- 6) ส่งไฟล์ให้ดาวน์โหลด ----------
$filename = 'รายงานรอบการผลิต_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
