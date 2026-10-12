@extends('layouts.layoutUser')

@section('title', 'ตารางออกหน่วย')

@section('content')
<div class="page-head">
    <div>
        <h1>ตารางออกหน่วย</h1>
        <p class="sub">งานของทุกแผนก (X-ray และ Checkup) · อัปเดตอัตโนมัติทุก 5 นาที</p>
    </div>
    <div class="actions">
        {{-- ตัวกรองแผนก: หน้า view เห็นได้ทั้ง X-ray และ Checkup --}}
        <div class="segmented" role="group" id="deptFilter" aria-label="กรองตามแผนก">
            <button type="button" class="active" data-dept="all">ทั้งหมด</button>
            @foreach($departments as $key => $label)
                <button type="button" data-dept="{{ $key }}"><span class="color-dot" style="background:var(--{{ $key }});border:0;width:9px;height:9px;border-radius:50%;"></span> {{ $label }}</button>
            @endforeach
        </div>
    </div>
</div>

<div id="calendar"></div>

<div class="tl-legend">
    <span><i style="background:var(--xray);width:5px;"></i>แถบซ้ายสีฟ้า = X-ray</span>
    <span><i style="background:var(--checkup);width:5px;"></i>แถบซ้ายสีเขียว = Checkup</span>
    <span><i style="background:var(--surface-3);"></i>สีพื้นหลัง = บริษัท</span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var companyColors = @json($companyColors);
        var departments = @json($departments);
        var allEvents = {!! $events !!};
        var currentDept = 'all';
        var isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: isMobile ? 'listWeek' : 'dayGridMonth',
            headerToolbar: isMobile
                ? { left: 'prev,next', center: 'title', right: 'today' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
            footerToolbar: isMobile ? { center: 'dayGridMonth,listWeek' } : false,
            buttonText: { today: 'วันนี้', month: 'เดือน', week: 'สัปดาห์', day: 'วัน', list: 'รายการ' },
            moreLinkText: function (n) { return '+ อีก ' + n; },
            noEventsText: 'ไม่มีงานในช่วงนี้',
            height: 'auto',
            dayMaxEvents: 4,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            events: allEvents, // Events from the controller (ทุกแผนก)
            editable: false,
            selectable: false,

            eventDidMount: function (info) {
                var p = info.event.extendedProps;
                var color = companyColors[p.comp_id] || '#e2e8f0';
                var dept = p.department || 'xray';
                var deptLabel = departments[dept] || dept;
                var text = '[' + deptLabel + '] ' + (p.description || '') + (p.comment ? ' · ' + p.comment : '');

                if (info.view.type.indexOf('list') === 0) {
                    var dot = info.el.querySelector('.fc-list-event-dot');
                    if (dot) dot.style.borderColor = dept === 'checkup' ? 'var(--checkup)' : 'var(--xray)';
                    var t = info.el.querySelector('.fc-list-event-title a') || info.el.querySelector('.fc-list-event-title');
                    if (t) t.innerHTML = '<span class="dept-badge ' + esc(dept) + ' mr-1">' + esc(deptLabel) + '</span>' + esc(p.description || '')
                        + (p.staff ? ' <small class="text-muted">· ' + esc(p.staff) + ' คน</small>' : '')
                        + (p.comment ? '<br><small class="text-muted">' + esc(p.comment) + '</small>' : '');
                } else {
                    info.el.style.backgroundColor = color;
                    info.el.style.boxShadow = 'inset 5px 0 0 ' + (dept === 'checkup' ? 'var(--checkup)' : 'var(--xray)');
                    info.el.style.paddingLeft = '9px';
                    var titleElement = info.el.querySelector('.fc-event-title');
                    if (titleElement) titleElement.textContent = text;
                }

                $(info.el).tooltip({ title: text, placement: 'top', trigger: 'hover', container: 'body' });
            },
        });

        if (window.fcThaiApply) fcThaiApply(calendar); // ชื่อเดือน/วันภาษาไทย
        calendar.render();

        function applyFilter() {
            calendar.removeAllEvents();
            calendar.addEventSource(currentDept === 'all' ? allEvents : allEvents.filter(function (e) {
                return (e.department || 'xray') === currentDept;
            }));
        }

        // กรองตามแผนก
        $('#deptFilter button').on('click', function () {
            currentDept = $(this).data('dept');
            $('#deptFilter button').removeClass('active');
            $(this).addClass('active');
            applyFilter();
        });

        // รีเฟรชหน้าอัตโนมัติ (สำหรับเปิดค้างไว้บนจอ)
        setInterval(function () { if (!document.fullscreenElement) location.reload(); }, 5 * 60 * 1000);
    });
</script>
<script>
    document.getElementById('fullscreenButton').addEventListener('click', function () {
        const el = document.getElementById('calendar');
        if (document.fullscreenElement) { document.exitFullscreen(); return; }
        (el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen).call(el);
    });
</script>

@endsection
