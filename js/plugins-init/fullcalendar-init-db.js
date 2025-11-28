(function($) {
    "use strict";

    // ตัวแปรสำหรับเก็บวันที่ที่ถูกคลิก
    var clickedDate = null;

    // --- 1. เริ่มต้น jQuery UI Dialog ---
    var eventDialog = $("#event-dialog").dialog({
        autoOpen: false, // ไม่ต้องเปิดอัตโนมัติ
        modal: true,    // แสดงเป็น modal
        width: 400,
        buttons: {
            // ปุ่ม "บันทึก"
            "บันทึก": function() {
                var title = $("#event-title").val(); // ดึงชื่อกิจกรรมจาก input

                // ตรวจสอบว่ามีชื่อกิจกรรมและวันที่
                if (title && clickedDate) {
                    var newEvent = {
                        title: title,
                        start: clickedDate.format('YYYY-MM-DD') // จัดรูปแบบวันที่
                    };

                    // --- 2. ส่งข้อมูลไปบันทึกผ่าน AJAX ---
                    $.ajax({
                        url: 'add_event.php', // ไฟล์ PHP ที่จะรับข้อมูล
                        type: 'POST',
                        data: {
                            title: newEvent.title,
                            start_date: newEvent.start
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                // --- 3. เพิ่ม Event ลงในปฏิทินทันที ---
                                newEvent.id = response.id; // ใช้ id จาก database
                                newEvent.backgroundColor = '#007bff'; // สีน้ำเงิน
                                newEvent.borderColor = '#007bff';
                                
                                $('#calendar').fullCalendar('renderEvent', newEvent, true);
                                eventDialog.dialog("close"); // ปิด Dialog
                            } else {
                                console.error('Error saving event: ' + response.message);
                                alert('เกิดข้อผิดพลาดในการบันทึก: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error: ' + error);
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error);
                        }
                    });
                } else if (!title) {
                    alert('กรุณากรอกชื่อกิจกรรม');
                }
            },
            // ปุ่ม "ยกเลิก"
            "ยกเลิก": function() {
                $(this).dialog("close");
            }
        },
        close: function() {
            // ล้างค่าใน input เมื่อปิด Dialog
            $("#event-title").val("");
        }
    });

    // --- 4. เริ่มต้น FullCalendar ---
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        locale: 'th',       // ตั้งค่าภาษาไทย
        events: db_events,  // โหลด events จากตัวแปรที่ส่งมาจาก PHP
        editable: false,    // ไม่อนุญาตให้ลากย้าย
        selectable: true,
        selectHelper: true,

        // --- 5. เพิ่ม Event Handler เมื่อคลิกที่วัน (สำคัญ) ---
        dayClick: function(date, jsEvent, view) {
            
            // เก็บวันที่ที่คลิกไว้ในตัวแปร
            clickedDate = date;
            
            // เปิด Dialog
            eventDialog.dialog("option", "title", "เพิ่มกิจกรรมวันที่ " + date.format('DD/MM/YYYY'));
            eventDialog.dialog("open");
        }
    });

})(jQuery);
