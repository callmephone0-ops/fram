<?php
// ===== Session & Auth =====
session_start();
include "./db.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php require './head.html'; ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<style>
    /* ================= THEME STYLES ================= */
    :root {
        --fc-button-bg-color: #27ae60;
        --fc-button-border-color: #27ae60;
        --fc-button-hover-bg-color: #143625;
        --fc-button-hover-border-color: #143625;
        --fc-button-active-bg-color: #143625;
        --fc-button-active-border-color: #143625;
        --fc-event-bg-color: #3788d8;
        --fc-event-border-color: #3788d8;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: #f7f9f7;
    }

    /* การ์ดปฏิทิน */
    .card-calendar {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
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
    }

    .calendar-title h4 {
        margin: 0;
        font-weight: 600;
        color: #143625;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Legend (คำอธิบายสี) */
    .legend-container {
        display: flex;
        gap: 15px;
        background: #f1f8e9;
        padding: 8px 15px;
        border-radius: 50px;
        font-size: 0.85rem;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #333;
        font-weight: 500;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-green { background-color: #28a745; box-shadow: 0 0 5px rgba(40, 167, 69, 0.4); }
    .dot-yellow { background-color: #ffc107; box-shadow: 0 0 5px rgba(255, 193, 7, 0.4); }
    .dot-blue { background-color: #3788d8; box-shadow: 0 0 5px rgba(55, 136, 216, 0.4); }

    /* ปรับแต่ง FullCalendar */
    #calendar {
        padding: 20px;
        background: #fff;
        min-height: 600px;
    }

    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600;
        color: #333;
    }

    .fc-button {
        border-radius: 8px !important;
        font-weight: 500;
        text-transform: capitalize;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    .fc-daygrid-event {
        border-radius: 4px;
        font-size: 0.85rem;
        padding: 2px 4px;
        cursor: pointer; /* เปลี่ยนเมาส์เป็นรูปมือเมื่อชี้ */
        transition: transform 0.1s;
    }
    .fc-daygrid-event:hover {
        transform: scale(1.02);
    }

    /* Modal Customization */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
    .modal-header {
        background: #143625;
        color: #fff;
        border-radius: 15px 15px 0 0;
        padding: 15px 25px;
    }
    .modal-title { font-weight: 600; }
    .close { color: #fff; opacity: 0.8; }
    .close:hover { opacity: 1; }
    
    .form-group label {
        font-weight: 500;
        color: #555;
        font-size: 0.9rem;
    }
    .form-control {
        border-radius: 8px;
        height: 45px;
        border: 1px solid #e0e0e0;
    }
    .form-control:focus {
        border-color: #27ae60;
        box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
    }
    
    /* View Detail Modal Styles */
    .detail-row {
        margin-bottom: 12px;
        display: flex;
        border-bottom: 1px dashed #eee;
        padding-bottom: 8px;
    }
    .detail-label {
        font-weight: 600;
        color: #555;
        width: 120px;
        flex-shrink: 0;
    }
    .detail-value {
        color: #333;
        flex-grow: 1;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .fc-toolbar-title {
            font-size: 1.2rem !important;
        }
        
        .card-header-custom {
            flex-direction: column;
            align-items: stretch;
        }
        
        .legend-container {
            justify-content: center;
            margin-top: 5px;
        }
        
        /* ซ่อนปุ่ม View ที่ไม่จำเป็นในมือถือ */
        .fc-dayGridMonth-button, .fc-timeGridWeek-button {
            display: none; 
        }
        
        #calendar {
            padding: 10px;
            min-height: auto;
        }
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
        require './header.html';
        require './sidebar.html';
        ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>จัดการกิจกรรมการเกษตร (Admin)</h4>
                            <p class="mb-0 text-muted">ดูภาพรวมกิจกรรมทั้งหมดและจัดการตารางเวลา</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-calendar">
                            
                            <div class="card-header-custom">
                                <div class="calendar-title">
                                    <h4><i class="fa fa-calendar-alt text-success"></i> ปฏิทินกิจกรรม</h4>
                                </div>
                                
                                <div class="d-flex flex-column flex-md-row gap-3 align-items-center">
                                    <div class="legend-container">
                                        <div class="legend-item"><span class="dot dot-green"></span> เริ่มปลูก</div>
                                        <div class="legend-item"><span class="dot dot-yellow"></span> เก็บเกี่ยว</div>
                                        <div class="legend-item"><span class="dot dot-blue"></span> กิจกรรมทั่วไป</div>
                                    </div>
                                    
                                    <button class="btn btn-success btn-rounded shadow-sm" data-toggle="modal" data-target="#addEventModal">
                                        <i class="fa fa-plus mr-1"></i> สร้างกิจกรรม
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div id="calendar"></div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php require './footer.html'; ?>
    </div>

    <div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form id="addEventForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit mr-2"></i> เพิ่มกิจกรรมใหม่</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">

                    <div class="form-group">
                        <label><i class="fa fa-tag mr-1 text-muted"></i> ชื่อกิจกรรม</label>
                        <input type="text" name="title" class="form-control" placeholder="เช่น ใส่ปุ๋ย, ประชุมกลุ่ม" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-clock mr-1 text-muted"></i> วันที่เริ่ม</label>
                                <input type="datetime-local" name="start" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-clock mr-1 text-muted"></i> วันที่สิ้นสุด</label>
                                <input type="datetime-local" name="end" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-check">
                        <input type="checkbox" name="all_day" id="all_day" class="form-check-input" checked>
                        <label for="all_day" class="form-check-label">ตลอดทั้งวัน (All-day)</label>
                    </div>
                    
                    <div class="form-group bg-light p-3 rounded border">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_global" name="is_global" value="1">
                            <label class="custom-control-label text-danger font-weight-bold" for="is_global">
                                <i class="fa fa-bullhorn mr-1"></i> ประกาศกิจกรรมส่วนกลาง
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1 ml-4">
                            หากเปิดใช้งาน ผู้ใช้ทุกคนในระบบจะเห็นกิจกรรมนี้
                        </small>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-palette mr-1 text-muted"></i> สีของแถบกิจกรรม</label>
                        <input type="color" name="color" class="form-control" value="#3788d8" style="height: 40px; padding: 2px;">
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success px-4">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewEventModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fa fa-info-circle mr-2"></i> รายละเอียดกิจกรรม</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    
                    <h4 id="viewEventTitle" class="mb-3 text-primary font-weight-bold"></h4>
                    
                    <div class="detail-row">
                        <span class="detail-label"><i class="fa fa-clock text-muted mr-1"></i> เวลา:</span>
                        <span class="detail-value" id="viewEventTime"></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label"><i class="fa fa-user text-muted mr-1"></i> ผู้บันทึก:</span>
                        <span class="detail-value" id="viewEventRecorder"></span>
                    </div>

                    <div id="deleteButtonContainer" class="mt-4 text-right border-top pt-3">
                        </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>

    <?php require './script.html'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            
            // ตรวจสอบขนาดหน้าจอเพื่อปรับ View เริ่มต้น
            const isMobile = window.innerWidth < 768;
            const initialView = isMobile ? 'listWeek' : 'dayGridMonth';

            const calendar = new FullCalendar.Calendar(calendarEl, {
                // Settings พื้นฐาน
                initialView: initialView,
                locale: 'th',
                selectable: true,
                height: 'auto',
                contentHeight: 600,
                dayMaxEvents: true,
                navLinks: true,
                
                // Toolbar (Header)
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: isMobile ? 'listWeek,timeGridDay' : 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    day: 'วัน',
                    list: 'รายการ'
                },

                // Data Source
                events: 'calendar_events.php?scope=all',

                // Event Handlers
                dateClick: function(info) {
                    const form = document.getElementById('addEventForm');
                    form.reset();
                    
                    const startInput = form.querySelector('input[name="start"]');
                    const d = new Date(info.date);
                    // ปรับ Timezone ให้ถูกต้องตอนกดเพิ่ม
                    const localIso = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                    startInput.value = localIso;
                    
                    $('#addEventModal').modal('show');
                },

                eventClick: function(info) {
                    const evt = info.event;
                    const props = evt.extendedProps || {};
                    
                    // 1. ใส่ข้อมูลลงใน Modal View
                    document.getElementById('viewEventTitle').innerText = evt.title;
                    
                    // จัดรูปแบบเวลาให้สวยงาม
                    let timeStr = '';
                    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    if (evt.allDay) {
                        timeStr = evt.start.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' }) + ' (ตลอดวัน)';
                    } else {
                        timeStr = evt.start.toLocaleDateString('th-TH', options);
                        if (evt.end) {
                            timeStr += ' - ' + evt.end.toLocaleDateString('th-TH', options);
                        }
                    }
                    document.getElementById('viewEventTime').innerText = timeStr;

                    // 2. แสดงชื่อผู้บันทึก 
                    // (รับค่าจาก backend key 'recorder_name')
                    const recorderName = props.recorder_name || props.username || 'ไม่ระบุ';
                    document.getElementById('viewEventRecorder').innerText = recorderName;

                    // 3. จัดการปุ่มลบ
                    const deleteContainer = document.getElementById('deleteButtonContainer');
                    deleteContainer.innerHTML = ''; // ล้างปุ่มเก่า

                    if (props.locked) {
                        // ถ้าเป็นกิจกรรมระบบ แสดงข้อความแทนปุ่ม
                        deleteContainer.innerHTML = '<span class="text-muted small"><i class="fa fa-lock"></i> กิจกรรมนี้สร้างโดยระบบอัตโนมัติ ไม่สามารถลบได้</span>';
                    } else {
                        // สร้างปุ่มลบ
                        const btnDelete = document.createElement('button');
                        btnDelete.className = 'btn btn-danger';
                        btnDelete.innerHTML = '<i class="fa fa-trash mr-1"></i> ลบกิจกรรม';
                        btnDelete.onclick = function() {
                            deleteEvent(evt);
                        };
                        deleteContainer.appendChild(btnDelete);
                    }

                    // เปิด Modal
                    $('#viewEventModal').modal('show');
                }
            });

            calendar.render();

            // ฟังก์ชันลบกิจกรรม (แยกออกมาเรียกใช้)
            function deleteEvent(evt) {
                if (confirm(`ยืนยันการลบกิจกรรม "${evt.title}" ?`)) {
                    fetch('delete_event.php?id=' + encodeURIComponent(evt.id), { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                evt.remove(); // ลบจากปฏิทิน
                                $('#viewEventModal').modal('hide'); // ปิด Modal
                                
                                if(typeof Swal !== 'undefined') {
                                    Swal.fire('ลบสำเร็จ', 'กิจกรรมถูกลบแล้ว', 'success');
                                }
                            } else {
                                alert(res.message || 'ลบไม่สำเร็จ');
                            }
                        })
                        .catch(() => alert('เกิดข้อผิดพลาดขณะลบ'));
                }
            }

            // จัดการ Form Submit (เพิ่มกิจกรรม)
            document.getElementById('addEventForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('add_event.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        $('#addEventModal').modal('hide');
                        this.reset();
                        calendar.refetchEvents();
                        
                        if(typeof Swal !== 'undefined') {
                            Swal.fire('สำเร็จ', 'บันทึกกิจกรรมเรียบร้อยแล้ว', 'success');
                        } else {
                            alert('บันทึกสำเร็จ');
                        }
                    } else {
                        alert(res.message || 'บันทึกไม่สำเร็จ');
                    }
                })
                .catch(() => alert('เกิดข้อผิดพลาดขณะบันทึก'));
            });
        });
    </script>
</body>
</html>