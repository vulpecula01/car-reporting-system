@extends('layouts.layout-calendar')
@section('title', 'Planner')
@section('fullscreen', '1')

@section('content')
<div class="page-head">
    <div>
        <h1>Planner</h1>
        <p class="sub">ปฏิทินงานของแผนก {{ Auth::user()->department_label }} · คลิกวันที่เพื่อจองรถ · คลิกงานเพื่อแก้ไข/คัดลอก/ลบ</p>
    </div>
    <div class="actions">
        <div class="legend-inline mr-2">
            <span><i style="border:2px dashed var(--text-3);background:transparent;"></i>รถเราถูกแผนกอื่นยืม</span>
        </div>
        <a href="{{ route('timeline.index') }}" class="btn btn-light"><i class="fas fa-stream mr-1"></i> ตารางรถ</a>
        <a href="{{ route('car_booking.index') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> จองรถ</a>
    </div>
</div>
<div id="calendar"></div>
<!-- Copy Event Modal -->
<div class="modal fade" id="copyEventModal" tabindex="-1" aria-labelledby="copyEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="copyEventModalLabel">คัดลอกงานไปวันอื่น</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="copyEventForm">
                    <div class="mb-3">
                        <label for="copyDate" class="form-label">วันที่ใหม่</label>
                        <input type="date" class="form-control" id="copyDate" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="startTime" class="form-label">เวลาเริ่ม</label>
                        <input type="time" class="form-control" id="startTime" name="start" required>
                    </div>
                    <div class="mb-3">
                        <label for="endTime" class="form-label">เวลาสิ้นสุด</label>
                        <input type="time" class="form-control" id="endTime" name="end" required>
                    </div>
                    <input type="hidden" id="copyEventId" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmCopyEvent"><i class="far fa-copy mr-1"></i> คัดลอก</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">แก้ไขงาน</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm">
                    @csrf
    @method('PUT')
                    <!-- Dropdown for Company -->
                    <div class="mb-3">
                        <label for="editCompany" class="form-label">บริษัท</label>
                        <select class="form-control" id="editCompany" name="company_id" required>
            <option value="">Select Company</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}">{{ $company->name }}</option>
            @endforeach
        </select>
                    </div>
                    
                    <!-- Dropdown for Car -->
                    <div class="mb-3">
                        <label for="editCar" class="form-label">รถ</label>
                        <select class="form-control" id="editCar" name="car_id" required>
            <option value="">Select Car</option>
            @foreach ($cars as $car)
                <option value="{{ $car->id }}">{{ $car->name }}{{ $car->department !== Auth::user()->department ? ' (ยืมจาก ' . $car->department_label . ')' : '' }}</option>
            @endforeach
        </select>
                    </div>
                    
                    <!-- Other form fields for date, start time, end time, etc. -->
                    <div class="mb-3">
                        <label for="editDate" class="form-label">วันที่</label>
                        <input type="date" class="form-control" id="editDate" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStartTime" class="form-label">เวลาเริ่ม</label>
                        <input type="time" class="form-control" id="editStartTime" name="start" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndTime" class="form-label">เวลาสิ้นสุด</label>
                        <input type="time" class="form-control" id="editEndTime" name="end" required>
                    </div>
                    <div class="mb-3">
                        <label for="editComment" class="form-label">หมายเหตุ</label>
                        <input type="text" class="form-control" id="editComment" name="comment" required>
                    </div>

                    <div class="mb-3">
                        <label for="editStaff" class="form-label">จำนวนคนที่ออกหน่วย</label>
                        <input type="text" class="form-control" id="editStaff" name="staff" required>
                    </div>
                    
                    <input type="hidden" id="editEventId" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="confirmEditEvent"><i class="fas fa-check mr-1"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        
        // แปลงข้อมูลบริษัทจาก Blade view เป็น JavaScript object
        var companyColors = @json($companies->pluck('color', 'id')); // ส่ง id เป็น key, color เป็น value
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
        customButtons: {
            zoom: {
                text: '🔍', // ใช้ไอคอนแว่นขยาย
                click: function () {
                    showZoomOptions();
                }
            }
        },
            
            height: 'auto',
            dayMaxEvents: 4,
            buttonText: { today: 'วันนี้', month: 'เดือน', week: 'สัปดาห์', day: 'วัน', list: 'รายการ' },
            moreLinkText: function (n) { return '+ อีก ' + n + ' งาน'; },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            events: {!! $events !!}, // ข้อมูล events จาก Controller
            editable: true,
            selectable: true,
            dateClick: function (info) {
                window.location.href = "/car_booking?date=" + info.dateStr;
            },
            eventDidMount: function (info) {
                var compId = info.event.extendedProps.comp_id;
                var color = companyColors[compId]; // Default color if no match
                info.el.style.backgroundColor = color;

                var description = info.event.extendedProps.description || '';
                var comment = info.event.extendedProps.comment || '';
                var staff = info.event.extendedProps.staff || '';
                var companyName = info.event.extendedProps.name || ''; // Assuming 'company_name' is in event data
                var updatedTitle = description + (comment ? ' · ' + comment : '');

                // รถของเราถูกแผนกอื่นยืมไป: แสดงป้าย + เส้นประ
                if (info.event.extendedProps.borrowed_by_other) {
                    updatedTitle = `[ยืมโดย ${info.event.extendedProps.borrowed_by}] ` + updatedTitle;
                    info.el.style.border = '2px dashed #6c757d';
                    info.el.style.opacity = '0.75';
                }

                // Set the event title and change the title text color to red
                var titleElement = info.el.querySelector('.fc-event-title') || info.el.querySelector('.fc-list-event-title a') || info.el.querySelector('.fc-list-event-title');
                if (titleElement) {
                    titleElement.textContent = updatedTitle;
                    titleElement.style.color = 'black';
                }

                // Tooltip with full event details
                $(info.el).tooltip({
                    title: updatedTitle,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },

            eventDrop: function (info) {
                $('#startTime').val(info.event.start.toISOString().slice(0, 16));
                $('#endTime').val(info.event.end ? info.event.end.toISOString().slice(0, 16) : '');
                $('#eventId').val(info.event.id);
                $('#updateEventModal').modal('show');
                info.revert(); // Revert until saved
            },

            eventClick: function (info) {
                // งานที่แผนกอื่นยืมรถเรา: ดูรายละเอียดได้อย่างเดียว
                if (info.event.extendedProps.borrowed_by_other) {
                    Swal.fire({
                        icon: 'info',
                        title: 'รถถูกยืมโดยแผนก ' + info.event.extendedProps.borrowed_by,
                        html: (info.event.extendedProps.description || '') + '<br>'
                            + moment(info.event.start).format('DD/MM/YYYY HH:mm') + ' - '
                            + (info.event.end ? moment(info.event.end).format('HH:mm') : '')
                            + '<br><small>แก้ไข/ลบได้เฉพาะแผนกที่ยืม</small>'
                    });
                    return;
                }
                Swal.fire({
                    title: info.event.extendedProps.description || 'งาน',
                    html: '<i class="far fa-clock mr-1"></i>' + moment(info.event.start).format('DD/MM/YYYY HH:mm') + (info.event.end ? ' – ' + moment(info.event.end).format('HH:mm') : '')
                        + (info.event.extendedProps.staff ? ' · <i class="fas fa-user-friends mx-1"></i>' + $('<div>').text(info.event.extendedProps.staff).html() + ' คน' : '')
                        + (info.event.extendedProps.comment ? '<br><small class="text-muted">' + $('<div>').text(info.event.extendedProps.comment).html() + '</small>' : ''),
                    showDenyButton: true,
                    showCancelButton: true,
                    showCloseButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> ลบ',
                    confirmButtonColor: '#dc2626',
                    denyButtonText: '<i class="far fa-copy mr-1"></i> คัดลอก',
                    denyButtonColor: '#475569',
                    cancelButtonText: '<i class="fas fa-pen mr-1"></i> แก้ไข',
                    cancelButtonColor: '#0f4c81',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ยืนยันก่อนลบ
                        Swal.fire({ icon: 'warning', title: 'ยืนยันการลบงานนี้?', text: info.event.extendedProps.description || '', showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#dc2626' })
                        .then(function (c) { if (!c.isConfirmed) return;
                        $.ajax({
                            url: '/events/' + info.event.id,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function () {
                                notify('ลบงานเรียบร้อยแล้ว');
                                info.event.remove();
                            },
                            error: function (xhr) {
                                Swal.fire('ไม่สำเร็จ', (xhr.responseJSON && xhr.responseJSON.message) || 'ไม่สามารถลบงานได้', 'error');
                            }
                        });
                        });
                    } else if (result.isDenied) {
     // Copy event logic
     
     var startTime = moment.utc(info.event.start).local().format('HH:mm');
     var endTime = info.event.end ? moment.utc(info.event.end).local().format('HH:mm') : '';  // เวลาสิ้นสุด
     var localStartDate = moment(info.event.start).local().format('YYYY-MM-DD');  // แปลงเป็นเวลาท้องถิ่นและดึงวันที่


$('#copyDate').val(localStartDate); // วันที่
     $('#copyEventId').val(info.event.id);  // event ID
     $('#startTime').val(startTime);  // ตั้งค่าเวลาเริ่ม
     $('#endTime').val(endTime);  // ตั้งค่าเวลาสิ้นสุด

     // ตั้งค่าแสดงวันที่เริ่มต้นและสิ้นสุด
     $('#startDate').val(localStartDate);  // วันที่เริ่มต้น
if (info.event.end) {
    var localEndDate = moment(info.event.end).local().format('YYYY-MM-DD');  // วันที่สิ้นสุด
    $('#endDate').val(localEndDate);  // ตั้งค่าในฟอร์ม
}

     $('#copyEventModal').modal('show');  // แสดง Modal
     $('#confirmCopyEvent').off('click').on('click', function () {
         var newDate = $('#copyDate').val();  // วันที่ใหม่
         var newStart = newDate + 'T' + $('#startTime').val().slice(0, 5);  // เวลาสเริ่มใหม่
         var newEnd = newDate + 'T' + ($('#endTime').val() ? $('#endTime').val().slice(0, 5) : '');  // เวลาสิ้นสุดใหม่

         if (!newStart || !newEnd) {
             Swal.fire('กรุณาเลือกเวลา', 'ระบุเวลาเริ่มและเวลาสิ้นสุด', 'warning');
             return;
         }

         $.ajax({
             url: '/events',
             method: 'POST',
             data: {
                 _token: $('meta[name="csrf-token"]').attr('content'),
                 title: info.event.title,
                 start: newStart,
                 end: newEnd,
                 car_id: info.event.extendedProps.car_id,
                 comp_id: info.event.extendedProps.comp_id,
                 description: info.event.extendedProps.description,
                 comment: info.event.extendedProps.comment,
                 staff: info.event.extendedProps.staff
                 
             },
             success: function() {
                 notify('คัดลอกงานเรียบร้อยแล้ว');
                 calendar.refetchEvents();  // รีเฟรชข้อมูล
                 $('#copyEventModal').modal('hide');  // ซ่อน Modal
                 location.reload();  // โหลดหน้าใหม่
             },
             error: function(xhr, status, error) {
                 Swal.fire('Error!', (xhr.responseJSON && xhr.responseJSON.message) || 'ไม่สามารถคัดลอกงานได้', 'error');
                 console.error(xhr.responseText);
             }
         });
     });

    } else if (result.dismiss === Swal.DismissReason.cancel) {
    // แปลงเวลา start และ end จาก UTC เป็นเวลาท้องถิ่น
    var startTime = moment.utc(info.event.start).local().format('HH:mm');
    var endTime = info.event.end ? moment.utc(info.event.end).local().format('HH:mm') : '';
    var localStartDate = moment(info.event.start).local().format('YYYY-MM-DD');  // แปลงวันที่เป็นเวลาท้องถิ่น

    // การตั้งค่าฟอร์มแก้ไขเหตุการณ์
    $('#editEventId').val(info.event.id);
    $('#editCompany').val(info.event.extendedProps.comp_id); // Company ID
    $('#editCar').val(info.event.extendedProps.car_id); // Car ID
    $('#editDate').val(localStartDate); // วันที่ (แปลงจาก UTC เป็นเวลาท้องถิ่น)
    $('#editStartTime').val(startTime); // ตั้งค่าเวลาเริ่มต้น
    $('#editEndTime').val(endTime); // ตั้งค่าเวลาสิ้นสุด
    $('#editComment').val(info.event.extendedProps.comment || '');
    $('#editStaff').val(info.event.extendedProps.staff || '');  // ตั้งค่าคอมเมนต์

    $('#editEventModal').modal('show');

    $('#confirmEditEvent').off('click').on('click', function () {
        var eventId = $('#editEventId').val();
        var updatedCompany = $('#editCompany').val();
        var updatedCar = $('#editCar').val();
        var updatedDate = $('#editDate').val();
        var updatedStartTime = updatedDate + 'T' + $('#editStartTime').val();
        var updatedEndTime = updatedDate + 'T' + $('#editEndTime').val();
        var updatedComment = $('#editComment').val();
        var updatedStaff = $('#editStaff').val();

        if (!updatedCompany || !updatedCar || !updatedDate || !updatedStartTime || !updatedEndTime) {
            Swal.fire('ข้อมูลไม่ครบ', 'กรุณากรอกข้อมูลให้ครบ', 'warning');
            return;
        }

        // ส่งข้อมูลแก้ไขไปยังเซิร์ฟเวอร์
        $.ajax({
            url: '/events/' + eventId,
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                comp_id: updatedCompany,
                car_id: updatedCar,
                date: updatedDate,
                start: updatedStartTime,
                end: updatedEndTime,
                comment: updatedComment,
                staff: updatedStaff,
            },
            success: function() {
                notify('บันทึกการแก้ไขเรียบร้อยแล้ว');
                calendar.refetchEvents(); // รีเฟรชปฏิทิน
                $('#editEventModal').modal('hide');
                location.reload(); // โหลดหน้าใหม่เพื่อแสดงข้อมูลที่อัปเดต
            },
            error: function(xhr, status, error) {
                Swal.fire('Error!', (xhr.responseJSON && xhr.responseJSON.message) || 'ไม่สามารถบันทึกได้', 'error');
                console.error(xhr.responseText);
            }
        });
    });
                    }
                });
            }
        });

        if (window.fcThaiApply) fcThaiApply(calendar); // ชื่อเดือน/วันภาษาไทย
        calendar.render();
        function showZoomOptions() {
        var zoomLevels = [50, 75, 100, 125];
        var zoomMenu = document.createElement('div');
        zoomMenu.style.position = 'absolute';
        zoomMenu.style.top = '50px';
        zoomMenu.style.left = '10px';
        zoomMenu.style.backgroundColor = 'white';
        zoomMenu.style.border = '1px solid #ccc';
        zoomMenu.style.padding = '5px';

        zoomLevels.forEach(function(level) {
            var zoomOption = document.createElement('button');
            zoomOption.textContent = level + '%';
            zoomOption.style.margin = '5px';
            zoomOption.addEventListener('click', function() {
                changeZoom(level);
            });
            zoomMenu.appendChild(zoomOption);
        });

        document.body.appendChild(zoomMenu);
    }

    // ฟังก์ชันเปลี่ยนขนาดปฏิทิน
    function changeZoom(level) {
        var newHeight = level + '%';  // เปลี่ยนความสูงของปฏิทินตามระดับซูม
        calendar.setOption('height', newHeight);
        document.body.querySelector('div').remove(); // ลบเมนูซูมออกหลังการเลือก
    }
        
    });
</script>
<script>
    document.addEventListener('fullscreenchange', function () {
        const calendarElement = document.getElementById('calendar');
        if (document.fullscreenElement === calendarElement) {
            // เมื่ออยู่ใน fullscreen mode
            calendarElement.style.height = "50vh"; // ปรับขนาดเป็น 75% ของหน้าจอ
        } else {
            // เมื่อออกจาก fullscreen mode
            calendarElement.style.height = "calc(75vh - 75px)"; // คืนค่าดั้งเดิม
        }
    });

    document.getElementById('fullscreenButton').addEventListener('click', function () {
        const calendarElement = document.getElementById('calendar');
        if (calendarElement.requestFullscreen) {
            calendarElement.requestFullscreen(); // เปิดโหมดเต็มจอ
        } else if (calendarElement.mozRequestFullScreen) { // Firefox
            calendarElement.mozRequestFullScreen();
        } else if (calendarElement.webkitRequestFullscreen) { // Chrome, Safari, Edge
            calendarElement.webkitRequestFullscreen();
        } else if (calendarElement.msRequestFullscreen) { // IE/Edge
            calendarElement.msRequestFullscreen();
        }
    });
</script>


@endsection
