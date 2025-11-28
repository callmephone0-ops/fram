<?php
session_start();
// ตรวจสอบว่าเป็น Admin หรือไม่ (สมมติว่า role = 'admin')
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
include "./db.php";

// ========== ส่วนที่ 1: ฟังก์ชันดึงข้อมูล (ใช้ชุดเดียวกับ User) ==========
function getVarieties($conn, $cropType) {
    $varieties = [];
    if ($stmt = $conn->prepare("SELECT id, variety_name FROM crop_varieties WHERE crop_type = ? ORDER BY variety_name ASC")) {
        $stmt->bind_param("s", $cropType);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $varieties[] = $row; }
        $stmt->close();
    }
    return $varieties;
}

function getRiceCycles($conn) {
    $cycles = [];
    $sql = "SELECT id, cycle_name FROM rice_production_cycles ORDER BY cycle_name ASC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) { $cycles[] = $row; }
    }
    return $cycles;
}

function getPlantingMethods($conn) {
    $methods = [];
    $check = $conn->query("SHOW TABLES LIKE 'planting_methods'");
    if ($check && $check->num_rows > 0) {
        $sql = "SELECT id, method_name FROM planting_methods ORDER BY method_name ASC";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) { $methods[] = $row; }
        }
    } else {
        $methods = [['id' => 0, 'method_name' => 'หว่าน'], ['id' => 0, 'method_name' => 'ปักดำ']];
    }
    return $methods;
}

function getAreaUnits($conn) {
    $units = [];
    $check = $conn->query("SHOW TABLES LIKE 'area_units'");
    if ($check && $check->num_rows > 0) {
        $sql = "SELECT id, unit_name FROM area_units ORDER BY id ASC";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) { $units[] = $row; }
        }
    } else {
        $units = [['id' => 0, 'unit_name' => 'ไร่'], ['id' => 0, 'unit_name' => 'งาน']];
    }
    return $units;
}

$rice_varieties = getVarieties($conn, 'ข้าว');
$longan_varieties = getVarieties($conn, 'ลำไย');
$rubber_varieties = getVarieties($conn, 'ยางพารา');
$rice_cycles = getRiceCycles($conn);
$planting_methods = getPlantingMethods($conn);
$area_units = getAreaUnits($conn);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<style>
    /* ใช้ CSS ชุดเดียวกับ savedata1.php เป๊ะๆ เพื่อความสวยงาม */
    :root { --primary: #2ecc71; --primary-dark: #27ae60; --text-dark: #333; --bg-color: #f4f6f9; }
    body { font-family: 'Prompt', sans-serif; background-color: var(--bg-color); color: var(--text-dark); }
    .content-body { min-height: 85vh; }
    
    /* Card & Form */
    .card-custom { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); background-color: #fff; overflow: visible; }
    fieldset.form-section { border: 1px solid #e7e7e7; border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    fieldset.form-section legend { width: auto; padding: 0 0.5rem; font-size: 1.1rem; font-weight: 600; color: #2ecc71; }
    
    /* Input Group & Select2 */
    .input-group .select2-container { flex: 1 1 auto; width: auto !important; }
    .input-group .select2-selection--single { border-top-right-radius: 0; border-bottom-right-radius: 0; height: 38px; border: 1px solid #ced4da; }
    .input-group-append .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; border-color: #ced4da; }
    
    /* Animation */
    .variety-list-item .btn-delete-item { opacity: 0.2; transition: opacity 0.2s; }
    .variety-list-item:hover .btn-delete-item { opacity: 1; }
    
    /* Tabs */
    .nav-pills-custom { background-color: #f1f3f5; border-radius: 20px; padding: 6px; margin-bottom: 30px; }
    .nav-pills-custom .nav-item { flex: 1; text-align: center; }
    .nav-pills-custom .nav-link { border-radius: 16px; color: #888; font-weight: 500; padding: 12px; transition: 0.3s; }
    .nav-pills-custom .nav-link.active { background-color: #fff; color: var(--primary-dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-weight: 600; }
    .nav-pills-custom i { font-size: 1.2rem; vertical-align: middle; margin-right: 5px; }
</style>

<body>
    <div id="preloader"><div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div></div>
    <div id="main-wrapper">
    <?php require './header.html'; require './sidebar.html'; ?>

        <div class="content-body">
            <div class="container-fluid">
                
                <div class="row page-titles mx-0 mb-4">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 class="text-primary font-weight-bold">🛠️ จัดการข้อมูลการเกษตร (Admin)</h4>
                            <p class="mb-0 text-muted">คุณสามารถเพิ่ม/ลบ ตัวเลือกต่างๆ ในระบบได้ที่นี่</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-9">
                        <div class="card card-custom">
                            <div class="card-body p-4">
                                
                                <ul class="nav nav-pills nav-pills-custom" id="cropTab" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" id="rice-tab" data-toggle="tab" href="#rice-content"><i class="fa fa-seedling"></i> ข้าว</a></li>
                                    <li class="nav-item"><a class="nav-link" id="longan-tab" data-toggle="tab" href="#longan-content"><i class="fa fa-lemon"></i> ลำไย</a></li>
                                    <li class="nav-item"><a class="nav-link" id="rubber-tab" data-toggle="tab" href="#rubber-content"><i class="fa fa-tree"></i> ยางพารา</a></li>
                                </ul>

                                <div class="tab-content">

                                    <div class="tab-pane fade show active" id="rice-content">
                                        <fieldset class="form-section">
                                            <legend><i class="fa fa-cog"></i> จัดการข้อมูลข้าว</legend>
                                            
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>จัดการสายพันธุ์</label>
                                                    <div class="input-group">
                                                        <select id="variety_input" class="form-control select2">
                                                            <option value="">-- รายการที่มีอยู่ --</option>
                                                            <?php foreach ($rice_varieties as $v) echo "<option value='{$v['variety_name']}' data-id='{$v['id']}'>{$v['variety_name']}</option>"; ?>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" onclick="openManageModal('variety', 'ข้าว')">
                                                                <i class="fa fa-edit"></i> จัดการ
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>จัดการรอบการผลิต</label>
                                                    <div class="input-group">
                                                        <select id="cycle_code_input" class="form-control select2">
                                                            <option value="">-- รายการที่มีอยู่ --</option>
                                                            <?php foreach ($rice_cycles as $c) echo "<option value='{$c['cycle_name']}' data-id='{$c['id']}'>{$c['cycle_name']}</option>"; ?>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" onclick="openManageModal('cycle')">
                                                                <i class="fa fa-edit"></i> จัดการ
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <div class="tab-pane fade" id="longan-content">
                                        <fieldset class="form-section">
                                            <legend><i class="fa fa-cog"></i> จัดการข้อมูลลำไย</legend>
                                            <div class="form-group">
                                                <label>จัดการสายพันธุ์</label>
                                                <div class="input-group">
                                                    <select id="longan_variety_input" class="form-control select2">
                                                        <option value="">-- รายการที่มีอยู่ --</option>
                                                        <?php foreach ($longan_varieties as $v) echo "<option value='{$v['variety_name']}' data-id='{$v['id']}'>{$v['variety_name']}</option>"; ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary" type="button" onclick="openManageModal('variety', 'ลำไย')">
                                                            <i class="fa fa-edit"></i> จัดการ
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <div class="tab-pane fade" id="rubber-content">
                                        <fieldset class="form-section">
                                            <legend><i class="fa fa-cog"></i> จัดการข้อมูลยางพารา</legend>
                                            <div class="form-group">
                                                <label>จัดการสายพันธุ์</label>
                                                <div class="input-group">
                                                    <select id="rubber_variety_input" class="form-control select2">
                                                        <option value="">-- รายการที่มีอยู่ --</option>
                                                        <?php foreach ($rubber_varieties as $v) echo "<option value='{$v['variety_name']}' data-id='{$v['id']}'>{$v['variety_name']}</option>"; ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary" type="button" onclick="openManageModal('variety', 'ยางพารา')">
                                                            <i class="fa fa-edit"></i> จัดการ
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>

                                </div> <fieldset class="form-section mt-4">
                                    <legend><i class="fa fa-globe"></i> จัดการข้อมูลส่วนกลาง</legend>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>จัดการวิธีการปลูก</label>
                                            <div class="input-group">
                                                <select id="method_input" class="form-control select2">
                                                    <option value="">-- รายการที่มีอยู่ --</option>
                                                    <?php foreach ($planting_methods as $m) echo "<option value='{$m['method_name']}' data-id='{$m['id']}'>{$m['method_name']}</option>"; ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-info text-white" type="button" onclick="openManageModal('method')">
                                                        <i class="fa fa-edit"></i> จัดการ
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>จัดการหน่วยนับพื้นที่</label>
                                            <div class="input-group">
                                                <select id="unit_input" class="form-control select2">
                                                    <option value="">-- รายการที่มีอยู่ --</option>
                                                    <?php foreach ($area_units as $u) echo "<option value='{$u['unit_name']}' data-id='{$u['id']}'>{$u['unit_name']}</option>"; ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-info text-white" type="button" onclick="openManageModal('unit')">
                                                        <i class="fa fa-edit"></i> จัดการ
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './footer.html'; ?>
    </div>

    <div class="modal fade" id="manageItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="manageItemModalLabel">จัดการข้อมูล</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="modal_add_title" class="mb-2 text-success font-weight-bold"><i class="fa fa-plus-circle"></i> เพิ่มรายการใหม่</h6>
                    <div class="input-group mb-3">
                        <input type="text" id="new_item_name_input" class="form-control" placeholder="ชื่อรายการ...">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="button" onclick="addItem()">เพิ่ม</button>
                        </div>
                    </div>
                    <div id="add_item_alert" class="alert alert-danger py-2" style="display: none; font-size:0.9rem;"></div>
                    <hr>
                    <h6 id="modal_list_title" class="mb-2 text-muted font-weight-bold"><i class="fa fa-list-ul"></i> รายการที่มีอยู่</h6>
                    <ul class="list-group" id="item_management_list" style="max-height: 250px; overflow-y: auto;"></ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php require './script.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // ข้อมูลจาก PHP
        var allData = {
            'variety_ข้าว': <?php echo json_encode($rice_varieties); ?>,
            'variety_ลำไย': <?php echo json_encode($longan_varieties); ?>,
            'variety_ยางพารา': <?php echo json_encode($rubber_varieties); ?>,
            'cycle': <?php echo json_encode($rice_cycles); ?>,
            'method': <?php echo json_encode($planting_methods); ?>,
            'unit': <?php echo json_encode($area_units); ?>
        };

        var currentConfig = {};

        function openManageModal(type, subType = '') {
            var key = (type === 'variety') ? 'variety_' + subType : type;
            var dataList = allData[key] || [];
            
            if (type === 'variety') {
                setupModalConfig('จัดการสายพันธุ์ (' + subType + ')', 'variety_name', 'ajax_add_variety.php', 'ajax_delete_variety.php', (subType=='ข้าว'?'variety_input':(subType=='ลำไย'?'longan_variety_input':'rubber_variety_input')), subType);
            } else if (type === 'cycle') {
                setupModalConfig('จัดการรอบการผลิต', 'cycle_name', 'ajax_add_cycle.php', 'ajax_delete_cycle.php', 'cycle_code_input');
            } else if (type === 'method') {
                setupModalConfig('จัดการวิธีการปลูก', 'method_name', 'ajax_add_method.php', 'ajax_delete_method.php', 'method_input');
            } else if (type === 'unit') {
                setupModalConfig('จัดการหน่วยนับ', 'unit_name', 'ajax_add_unit.php', 'ajax_delete_unit.php', 'unit_input');
            }

            renderList(dataList);
            $('#manageItemModal').modal('show');
        }

        function setupModalConfig(title, nameKey, addUrl, delUrl, selectId, cropType = null) {
            $('#manageItemModalLabel').text(title);
            currentConfig = { nameKey: nameKey, addUrl: addUrl, delUrl: delUrl, selectId: selectId, cropType: cropType };
        }

        function renderList(data) {
            var listHtml = '';
            if (!data || data.length === 0) listHtml = '<li class="list-group-item text-center text-muted">ไม่มีข้อมูล</li>';
            else {
                data.forEach(function(item) {
                    listHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2" data-id="${item.id}">
                            ${item[currentConfig.nameKey]}
                            <button class="btn btn-light btn-sm text-danger" onclick="deleteItem(${item.id})"><i class="fa fa-trash"></i></button>
                        </li>`;
                });
            }
            $('#item_management_list').html(listHtml);
        }

        function addItem() {
            var val = $('#new_item_name_input').val().trim();
            var alertBox = $('#add_item_alert');
            alertBox.hide();

            if(!val) { alertBox.text('กรุณากรอกชื่อรายการ').show(); return; }

            var postData = { name: val };
            if(currentConfig.cropType) postData.crop_type = currentConfig.cropType;

            $.ajax({
                url: currentConfig.addUrl,
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var li = `<li class="list-group-item d-flex justify-content-between align-items-center py-2" data-id="${response.id}">${response.name}<button class="btn btn-light btn-sm text-danger" onclick="deleteItem(${response.id})"><i class="fa fa-trash"></i></button></li>`;
                        $('#item_management_list li:contains("ไม่มีข้อมูล")').remove();
                        $('#item_management_list').append(li);
                        
                        var newOption = new Option(response.name, response.name, true, true);
                        $(newOption).attr('data-id', response.id);
                        $('#' + currentConfig.selectId).append(newOption).trigger('change');
                        $('#new_item_name_input').val('');
                    } else { alertBox.text('บันทึกไม่สำเร็จ: ' + (response.message || 'Unknown error')).show(); }
                },
                error: function() { alertBox.text('เกิดข้อผิดพลาดในการเชื่อมต่อ').show(); }
            });
        }

        function deleteItem(id) {
            if(!confirm('ยืนยันการลบรายการนี้?')) return;
            $.ajax({
                url: currentConfig.delUrl,
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $(`#item_management_list li[data-id="${id}"]`).remove();
                        var selectElem = $('#' + currentConfig.selectId);
                        var optionToRemove = selectElem.find(`option[data-id='${id}']`);
                        if(optionToRemove.length > 0) optionToRemove.remove();
                        selectElem.trigger('change');
                    } else { alert('ลบไม่สำเร็จ: ' + response.message); }
                },
                error: function() { alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'); }
            });
        }

        $(document).ready(function() {
            $('.select2').select2({ width: '100%', placeholder: 'เลือกข้อมูล' });
        });
        
    </script>
</body>
</html>