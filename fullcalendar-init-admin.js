(function($) {
    "use strict";

    // ตรวจสอบว่ามีตัวแปร db_events ที่ส่งมาจาก PHP หรือไม่
    if (typeof db_events === 'undefined') {
        var db_events = []; // ถ้าไม่มี ให้ใช้ array ว่าง
    }

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        themeSystem: 'bootstrap4',
        locale: 'th', // ใช้ภาษาไทย
        events: db_events, // <--- ใช้ข้อมูลจาก PHP

        // --- ส่วนที่เพิ่มเข้ามาสำหรับ Admin ---
        
        selectable: true,  // <-- อนุญาตให้คลิกเลือกวันที่ได้
        selectHelper: true,
        editable: false,     // <-- ปิดการลากและยืดหด (ถ้าอยากให้ทำได้ ค่อยเปิด)

        // ฟังก์ชันที่จะทำงานเมื่อผู้ใช้ "คลิกเลือก" วันที่
        select: function(start, end) {
            var title = prompt('กรอกชื่อกิจกรรม:');
            
            if (title) {
                // แปลงเวลาให้เป็น format ที่ PHP อ่านได้
                var startTime = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                var endTime = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");

                // ส่งข้อมูลไปบันทึกที่ save-event.php
                $.ajax({
                    url: 'save-event.php',
                    type: 'POST',
                    data: {
                        title: title,
                        start: startTime,
                        end: endTime
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            // ถ้าบันทึกสำเร็จ ให้แสดง Event นั้นบนปฏิทินทันที
                            $('#calendar').fullCalendar('renderEvent', {
                                title: title,
                                start: start,
                                end: end,
                                backgroundColor: '#546BFA', // สีเดียวกับที่เซฟ
                                borderColor: '#546BFA'
                            }, true); // 'true' = ทำให้ event "ติด" อยู่บนปฏิทิน
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + error);
                    }
                });
            }
            $('#calendar').fullCalendar('unselect'); // ยกเลิกการเลือก
        }

        // --- จบส่วนที่เพิ่มเข้ามา ---
    });

})(jQuery);