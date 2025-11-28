<?php
// (ใส่โค้ด session check และ include db.php ที่นี่)
// (ควรมีการเช็คว่าเป็น Admin หรือไม่)
?>
<!DOCTYPE html>
<html lang="en">
<?php require './head.html'; ?>
<body>
    <div id="main-wrapper">
        <?php require './header.html'; ?>
        <?php require './sidebar.php'; ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0"><h4>จัดการราคาผลผลิต</h4></div>
                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">ฟอร์มอัปเดตราคา</h4></div>
                            <div class="card-body">
                                <form action="save_price.php" method="POST">
                                    <div class="form-group">
                                        <label>ชนิดพืช</label>
                                        <select class="form-control" name="crop_name" required>
                                            <option value="" selected disabled>-- เลือก --</option>
                                            <option value="ข้าว">ข้าว</option>
                                            <option value="ลำไย">ลำไย</option>
                                            <option value="ยางพารา">ยางพารา</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>ราคาต่อหน่วย (บาท)</label>
                                        <input type="number" step="0.01" class="form-control" name="price_per_unit" required>
                                    </div>
                                    <div class="form-group">
                                        <label>หน่วย</label>
                                        <select class="form-control" name="unit" required>
                                            <option value="กิโลกรัม">กิโลกรัม</option>
                                            <option value="ตัน">ตัน</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">บันทึกราคา</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title">ราคาปัจจุบัน</h4></div>
                            <div class="card-body">
                                <table class="table">
                                    <thead><tr><th>ชนิดพืช</th><th>ราคา (บาท)</th><th>ต่อหน่วย</th></tr></thead>
                                    <tbody>
                                    <?php
                                    $sql_prices = "SELECT * FROM crop_prices";
                                    $res_prices = $conn->query($sql_prices);
                                    while($row_price = $res_prices->fetch_assoc()) {
                                        echo "<tr><td>" . $row_price['crop_name'] . "</td><td>" . number_format($row_price['price_per_unit'], 2) . "</td><td>" . $row_price['unit'] . "</td></tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require './script.html'; ?>
</body>
</html>