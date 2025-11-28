<?php
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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --text-main: #2c3e50;
            --bg-gray: #f4f6f9;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-main);
            padding-bottom: 80px; /* เผื่อที่ให้ปุ่ม FAB */
        }

        /* ===== Calendar Container ===== */
        .card-calendar {
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow: hidden;
            background: white;
        }

        /* ===== PC View Styles ===== */
        .fc-toolbar-title { font-size: 1.5rem !important; font-weight: 600; }
        .fc-button {
            border-radius: 50px !important;
            font-weight: 500;
            box-shadow: none !important;
        }
        .fc-button-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }
        .fc-daygrid-day-number { color: #444; font-weight: 500; }
        .fc-col-header-cell-cushion { color: #888; padding: 15px 0 !important; }

        /* ===== Mobile Styling (หัวใจสำคัญ) ===== */
        @media (max-width: 768px) {
            /* 1. ปรับแต่ง List View ให้เหมือน App */
            .fc-list-event {
                cursor: pointer;
                background: white !important;
            }
            .fc-list-event:hover td { background: #f9f9f9 !important; }
            
            /* ซ่อนเส้นตารางรกๆ */
            .fc-theme-standard .fc-list-day-cushion { background-color: #f8f9fa !important; }
            .fc-list-day-text, .fc-list-day-side-text { 
                font-size: 1.1rem; font-weight: 600; color: var(--text-main); 
            }

            /* จุดสีหน้ากิจกรรม */
            .fc-list-event-dot { border-width: 6px !important; }

            /* 2. Bottom Sheet Modal (ถาดเลื่อนขึ้นจากล่าง) */
            .modal.fade .modal-dialog {
                transition: transform 0.3s ease-out;
                transform: translate(0, 50px);
            }
            .modal.show .modal-dialog {
                transform: none;
            }
            
            .modal-dialog-bottom {
                position: fixed;
                bottom: 0;
                margin: 0;
                width: 100%;
                max-width: 100%;
            }
            
            .modal-content-bottom {
                border-radius: 25px 25px 0 0;
                border: none;
                padding-bottom: 20px; /* เผื่อ iPhone Home Bar */
            }
            
            /* แถบจับด้านบน (Handle) */
            .modal-handle {
                width: 50px;
                height: 5px;
                background: #e0e0e0;
                border-radius: 10px;
                margin: 15px auto 10px;
            }
        }

        /* ===== Floating Action Button (FAB) ===== */
        .fab-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
            border: none;
            z-index: 1050;
            transition: transform 0.2s;
        }
        .fab-btn:active { transform: scale(0.9); }
        
        /* ซ่อนปุ่ม FAB ในจอใหญ่ */
        @media (min-width: 769px) { .fab-btn { display: none; } }

    </style>
</head>

<body>
    <div id="main-wrapper">
        <?php require './header1.html'; require './sidebar1.html'; ?>

        <div class="content-body">
            <div class="container-fluid px-md-4">
                
                <div class="row mb-3">
                    <div class="col-12">
                        <h4 class="font-weight-bold text-dark">📅 ปฏิทินกิจกรรม</h4>
                    </div>
                </div>

                <div class="card card-calendar">
                    <div class="card-body p-0 p-md-4">
                        <div id="calendar"></div>
                    </div>
                </div>

            </div>
        </div>

        <?php require './footer.html'; ?>
    </div>

    <button class="fab-btn" data-toggle="modal" data-target="#addEventModal">
        <i class="fa fa-plus"></i>
    </button>

    <div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-bottom">
            <form id="addEventForm" class="modal-content modal-content-bottom">
                <div class="d-block d-md-none modal-handle"></div>
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">สร้างกิจกรรมใหม่</h5>
                    <button type="button" class="close d-none d-md-block" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">ชื่อกิจกรรม</label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0" placeholder="เช่น ใส่ปุ๋ย, เก็บเกี่ยว" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">เริ่ม</label>
                                <input type="datetime-local" name="start" class="form-control bg-light border-0" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">สิ้นสุด</label>
                                <input type="datetime-local" name="end" class="form-control bg-light border-0">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-3 rounded">
                        <label class="m-0 font-weight-bold text-dark" for="all_day">กิจกรรมทั้งวัน</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="all_day" name="all_day" checked>
                            <label class="custom-control-label" for="all_day"></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-muted small font-weight-bold">สีป้ายกำกับ</label>
                        <div class="d-flex gap-2" style="overflow-x: auto; padding-bottom: 5px;">
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="color1" name="color" value="#3788d8" class="custom-control-input" checked>
                                <label class="custom-control-label" for="color1"><span class="badge badge-primary p-2">&nbsp;&nbsp;</span> ทั่วไป</label>
                            </div>
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="color2" name="color" value="#e74c3c" class="custom-control-input">
                                <label class="custom-control-label" for="color2"><span class="badge badge-danger p-2">&nbsp;&nbsp;</span> สำคัญ</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="color3" name="color" value="#f39c12" class="custom-control-input">
                                <label class="custom-control-label" for="color3"><span class="badge badge-warning p-2">&nbsp;&nbsp;</span> เตือนความจำ</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4">
                    <button type="button" class="btn btn-light btn-lg flex-grow-1 d-md-none" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1" style="border-radius: 15px;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-bottom">
            <div class="modal-content modal-content-bottom">
                <div class="d-block d-md-none modal-handle"></div>
                
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div id="detail-icon-bg" style="width: 60px; height: 60px; border-radius: 50%; background: #e8f5e9; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fa fa-calendar-check text-success" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>

                    <h4 id="detail-title" class="font-weight-bold mb-1"></h4>
                    <p class="text-muted mb-4"><span id="detail-time"></span></p>

                    <div id="detail-locked-warning" class="alert alert-light border text-left mb-4" style="display:none; border-radius: 15px;">
                        <div class="d-flex">
                            <i class="fa fa-lock text-warning mr-3 mt-1"></i>
                            <small class="text-muted">กิจกรรมนี้ถูกสร้างโดยระบบ (เช่น วันเพาะปลูก) ไม่สามารถลบได้</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-light btn-block btn-lg rounded-pill" data-dismiss="modal">ปิด</button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="deleteEventBtn" class="btn btn-danger btn-block btn-lg rounded-pill">
                                <i class="fa fa-trash mr-2"></i> ลบ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require './script.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const isMobile = window.innerWidth < 768;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: isMobile ? 'listMonth' : 'dayGridMonth', // มือถือใช้แบบรายการ
                locale: 'th',
                height: isMobile ? 'auto' : 750,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: isMobile ? '' : 'dayGridMonth,timeGridWeek,listWeek' // ซ่อนปุ่มเปลี่ยนมุมมองในมือถือ (รก)
                },
                buttonText: { today: 'วันนี้', month: 'เดือน', list: 'รายการ' },
                events: 'calendar_events.php',
                
                // ปรับแต่งหน้าตา List View
                eventDidMount: function(info) {
                    if (info.view.type === 'listMonth') {
                        // เพิ่มสไตล์ให้ดูดีขึ้นถ้าต้องการ
                    }
                },

                // เพิ่มกิจกรรม (คลิกวันที่)
                dateClick: function(info) {
                    const form = document.getElementById('addEventForm');
                    form.reset();
                    const startInput = form.querySelector('input[name="start"]');
                    const d = new Date(info.date);
                    const now = new Date();
                    d.setHours(now.getHours(), now.getMinutes());
                    const tzOffset = d.getTimezoneOffset() * 60000;
                    startInput.value = new Date(d.getTime() - tzOffset).toISOString().slice(0,16);
                    $('#addEventModal').modal('show');
                },

                // ดูกิจกรรม (คลิกรายการ)
                eventClick: function(info) {
                    const evt = info.event;
                    const modal = $('#eventDetailModal');
                    
                    // Set Data
                    modal.find('#detail-title').text(evt.title);
                    
                    // Format Time Show
                    const options = { dateStyle: 'long', timeStyle: 'short', locale: 'th-TH' };
                    let timeStr = new Date(evt.start).toLocaleString('th-TH', options);
                    if(evt.allDay) {
                        timeStr = new Date(evt.start).toLocaleString('th-TH', { dateStyle: 'long', locale: 'th-TH' }) + ' (ทั้งวัน)';
                    }
                    modal.find('#detail-time').text(timeStr);

                    // Handle Buttons
                    const deleteBtn = modal.find('#deleteEventBtn');
                    const lockedWarning = modal.find('#detail-locked-warning');

                    if (evt.extendedProps && evt.extendedProps.locked) {
                        lockedWarning.show();
                        deleteBtn.hide();
                        deleteBtn.data('eventId', null);
                    } else {
                        lockedWarning.hide();
                        deleteBtn.show();
                        deleteBtn.data('eventId', evt.id);
                        modal.data('eventObject', info.event);
                    }
                    modal.modal('show');
                },

                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('listMonth');
                        calendar.setOption('headerToolbar', { left: 'prev,next today', center: 'title', right: '' });
                    } else {
                        calendar.changeView('dayGridMonth');
                        calendar.setOption('headerToolbar', { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' });
                    }
                }
            });

            calendar.render();

            // === Logic การลบและเพิ่ม เหมือนเดิม ===
            $('#deleteEventBtn').on('click', function() {
                const eventId = $(this).data('eventId');
                const eventObject = $('#eventDetailModal').data('eventObject');
                if (!eventId) return;
                if (confirm('ต้องการลบกิจกรรมนี้?')) {
                    fetch('delete_event.php?id=' + encodeURIComponent(eventId), { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) { eventObject.remove(); $('#eventDetailModal').modal('hide'); } 
                            else { alert('ลบไม่สำเร็จ'); }
                        });
                }
            });

            document.getElementById('addEventForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('add_event.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        $('#addEventModal').modal('hide');
                        this.reset();
                        calendar.refetchEvents();
                    } else { alert('บันทึกไม่สำเร็จ'); }
                });
            });
        });
    </script>
</body>
</html>