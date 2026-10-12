@extends('layouts.layout')
@section('title', 'จองรถ')

@section('content')
    <div class="page-head">
        <div>
            <h1>จองรถ</h1>
            <p class="sub">เลือกวันและเวลาก่อน แล้วเลือกรถจากรูป — ระบบจะแสดงเฉพาะคันที่ว่างให้เลือก</p>
        </div>
        <div class="actions">
            <a href="{{ route('timeline.index') }}" class="btn btn-light"><i class="fas fa-stream mr-1"></i> ดูตารางรถ</a>
        </div>
    </div>

    <form action="{{ route('book.xray') }}" method="POST" id="bookingForm" novalidate>
        @csrf
        <input type="hidden" name="car_id" id="car_id" value="{{ old('car_id', $selectedCar) }}">

        <div class="booking-layout">
            <div>
                {{-- 1. วันเวลา --}}
                <div class="card">
                    <div class="card-body">
                        <div class="step-title"><span class="step-num">1</span> วันและเวลาใช้งาน</div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="pickup_date">วันที่ใช้งาน</label>
                                <input type="date" name="pickup_date" id="pickup_date" class="form-control" required value="{{ old('pickup_date', $selectedDate) }}">
                            </div>
                            <div class="form-group col-6 col-md-4">
                                <label for="pickup_start_time">เวลาเริ่ม</label>
                                <input type="time" name="pickup_start_time" id="pickup_start_time" class="form-control" required value="{{ old('pickup_start_time', $selectedStart) }}">
                            </div>
                            <div class="form-group col-6 col-md-4">
                                <label for="pickup_end_time">เวลาสิ้นสุด</label>
                                <input type="time" name="pickup_end_time" id="pickup_end_time" class="form-control" required value="{{ old('pickup_end_time', $selectedEnd) }}">
                            </div>
                        </div>
                        <div class="time-presets" aria-label="ช่วงเวลาที่ใช้บ่อย">
                            <button type="button" data-s="08:00" data-e="12:00">ช่วงเช้า 08:00–12:00</button>
                            <button type="button" data-s="13:00" data-e="16:00">ช่วงบ่าย 13:00–16:00</button>
                            <button type="button" data-s="08:00" data-e="16:00">ทั้งวัน 08:00–16:00</button>
                            <button type="button" data-s="06:00" data-e="12:00">เช้ามืด 06:00–12:00</button>
                        </div>
                    </div>
                </div>

                {{-- 2. เลือกรถ --}}
                <div class="card">
                    <div class="card-body">
                        <div class="step-title"><span class="step-num">2</span> เลือกรถ</div>
                        <button type="button" class="car-picker-btn" id="openPicker" aria-haspopup="dialog">
                            <span class="thumb" id="pickThumb"><i class="fas fa-bus"></i></span>
                            <span>
                                <span class="t1 d-block" id="pickName">แตะเพื่อเลือกรถ</span>
                                <span class="t2 d-block" id="pickInfo">ดูรูปรถและสถานะว่าง/ไม่ว่างของทุกคัน</span>
                            </span>
                            <i class="fas fa-chevron-right chev"></i>
                        </button>
                        <div id="borrowNote" class="alert alert-warning mt-3 mb-0" style="display:none;">
                            <i class="fas fa-exchange-alt mr-1"></i> เป็นการ <b>ยืมรถของแผนก <span id="borrowDept"></span></b> — แผนกเจ้าของรถจะเห็นการจองนี้และได้รับอีเมลแจ้ง
                        </div>
                        <div id="busyNote" class="alert alert-danger mt-3 mb-0" style="display:none;"></div>
                    </div>
                </div>

                {{-- 3. รายละเอียด --}}
                <div class="card">
                    <div class="card-body">
                        <div class="step-title"><span class="step-num">3</span> รายละเอียดงาน</div>
                        <div class="form-group">
                            <label for="comp_id">บริษัท / สถานที่</label>
                            <select name="comp_id" id="comp_id" class="form-control" required>
                                <option value=""></option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" data-color="{{ $company->color }}" {{ old('comp_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint">ไม่พบบริษัท? <a href="{{ route('companies.index') }}">เพิ่มบริษัทใหม่</a></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="staff">จำนวนคนที่ออกหน่วย</label>
                                <div class="input-group">
                                    <input type="number" min="0" name="staff" id="staff" class="form-control" value="{{ old('staff') }}" placeholder="เช่น 5">
                                    <div class="input-group-append"><span class="input-group-text">คน</span></div>
                                </div>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="comment">หมายเหตุ</label>
                                <input type="text" name="comment" id="comment" class="form-control" value="{{ old('comment') }}" placeholder="เช่น ตรวจสุขภาพประจำปี 200 คน">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- สรุป --}}
            <div style="position:sticky;top:76px;">
                <div class="card summary-card">
                    <div class="card-header"><h3 class="card-title">สรุปการจอง</h3></div>
                    <div class="card-body">
                        <div class="row-item"><span>วันที่</span><span id="sumDate">-</span></div>
                        <div class="row-item"><span>เวลา</span><span id="sumTime">-</span></div>
                        <div class="row-item"><span>รถ</span><span id="sumCar">ยังไม่ได้เลือก</span></div>
                        <div class="row-item"><span>บริษัท</span><span id="sumComp">-</span></div>
                        <div class="row-item"><span>แผนกผู้จอง</span><span><span class="dept-badge {{ Auth::user()->department }}">{{ Auth::user()->department_label }}</span></span></div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn"><i class="fas fa-check mr-1"></i> บันทึกการจอง</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Popup เลือกรถ --}}
    <div class="modal fade car-modal" id="carModal" tabindex="-1" role="dialog" aria-labelledby="carModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="carModalTitle">เลือกรถ</h5>
                        <small class="text-muted" id="carModalSub"></small>
                    </div>
                    <div class="ml-auto d-flex align-items-center" style="gap:.5rem;">
                        <div class="segmented" id="availFilter">
                            <button type="button" class="active" data-f="all">ทั้งหมด</button>
                            <button type="button" data-f="free">เฉพาะที่ว่าง</button>
                        </div>
                        <button type="button" class="close ml-2" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
                    </div>
                </div>
                <div class="modal-body" id="carModalBody">
                    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i>กำลังโหลด...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var cars = [];
    var selected = $('#car_id').val() ? parseInt($('#car_id').val(), 10) : null;
    var filter = 'all';
    var availUrl = @json(route('car_booking.availability'));
    var thMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    var thDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

    function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
    function fmtDate(v) {
        if (!v) return '-';
        var d = new Date(v + 'T00:00:00');
        return 'วัน' + thDays[d.getDay()] + ' ' + d.getDate() + ' ' + thMonths[d.getMonth()] + ' ' + (d.getFullYear() + 543);
    }
    function vals() { return { date: $('#pickup_date').val(), start: $('#pickup_start_time').val(), end: $('#pickup_end_time').val() }; }

    // ---- โหลดสถานะรถ ----
    var xhr = null;
    function load() {
        var v = vals();
        if (!v.date) return;
        if (xhr) xhr.abort();
        xhr = $.getJSON(availUrl, v).done(function (res) {
            cars = res.cars || [];
            render();
            refreshSelected();
        });
    }

    function card(c) {
        var state, pill;
        if (!c.active) { state = 'disabled'; pill = '<span class="state-pill secondary">ซ่อมบำรุง</span>'; }
        else if (!c.available) { state = 'disabled'; pill = '<span class="state-pill danger">ไม่ว่าง</span>'; }
        else { state = ''; pill = '<span class="state-pill success">ว่าง</span>'; }
        var isPlaceholder = /bus\.png$/.test(c.image);
        var sched = c.bookings.length
            ? c.bookings.map(function (b) { return '<span title="' + esc(b.company || '') + ' (' + esc(b.department) + ')">' + esc(b.time) + '</span>'; }).join('')
            : '<span>ว่างทั้งวัน</span>';
        var why = '';
        if (c.conflict) why = '<div class="why"><i class="fas fa-ban mr-1"></i>จองแล้ว ' + esc(c.conflict.time) + ' โดย ' + esc(c.conflict.department) + (c.conflict.company ? ' · ' + esc(c.conflict.company) : '') + '</div>';
        else if (!c.active) why = '<div class="why"><i class="fas fa-tools mr-1"></i>อยู่ระหว่างซ่อมบำรุง</div>';

        return '<button type="button" class="pick-card ' + state + (c.id === selected ? ' selected' : '') + '" data-id="' + c.id + '"' + (state ? ' aria-disabled="true"' : '') + '>' +
            '<div class="img"><img src="' + esc(c.image) + '" alt="" class="' + (isPlaceholder ? 'placeholder-img' : '') + '">' + pill +
            '<span class="dept-badge ' + esc(c.department) + '">' + (c.own ? '' : 'ยืม · ') + esc(c.department_label) + '</span>' +
            '<span class="tick"><i class="fas fa-check"></i></span></div>' +
            '<div class="body"><div class="nm">' + esc(c.name) + '</div><div class="pl">' + esc(c.license_plate || '') + '</div>' +
            '<div class="sched">' + sched + '</div>' + why + '</div></button>';
    }

    function render() {
        var v = vals();
        $('#carModalSub').text(fmtDate(v.date) + ' · ' + (v.start || '--:--') + '–' + (v.end || '--:--') + ' น.');
        var list = cars.filter(function (c) { return filter === 'all' || c.available; });
        var own = list.filter(function (c) { return c.own; });
        var other = list.filter(function (c) { return !c.own; });
        var html = '';
        if (own.length) html += '<div class="group-title"><i class="fas fa-bus"></i> รถของแผนก {{ Auth::user()->department_label }}</div><div class="pick-grid">' + own.map(card).join('') + '</div>';
        if (other.length) html += '<div class="group-title"><i class="fas fa-exchange-alt"></i> ยืมรถแผนกอื่น</div><div class="pick-grid">' + other.map(card).join('') + '</div>';
        if (!html) html = '<div class="empty-state"><i class="far fa-sad-tear"></i>ไม่มีรถว่างในช่วงเวลานี้ ลองเปลี่ยนเวลาดูนะครับ</div>';
        $('#carModalBody').html(html);
    }

    function refreshSelected() {
        var c = cars.find(function (x) { return x.id === selected; });
        if (!c) {
            $('#openPicker').removeClass('selected');
            $('#pickThumb').html('<i class="fas fa-bus"></i>');
            $('#pickName').text('แตะเพื่อเลือกรถ');
            $('#pickInfo').text('ดูรูปรถและสถานะว่าง/ไม่ว่างของทุกคัน');
            $('#borrowNote, #busyNote').hide();
            $('#sumCar').text('ยังไม่ได้เลือก');
            return;
        }
        $('#openPicker').addClass('selected');
        $('#pickThumb').html('<img src="' + esc(c.image) + '" alt="">');
        $('#pickName').text(c.name);
        $('#pickInfo').html(esc(c.license_plate || '') + ' · <span class="dept-badge ' + esc(c.department) + '">' + esc(c.department_label) + '</span> · <a href="#" id="changeCar">เปลี่ยนรถ</a>');
        $('#sumCar').text(c.name);
        $('#borrowDept').text(c.department_label);
        $('#borrowNote').toggle(!c.own);
        if (!c.available) {
            $('#busyNote').html('<i class="fas fa-exclamation-triangle mr-1"></i> รถคันนี้ ' + (c.active ? 'ไม่ว่างช่วงเวลาที่เลือก (' + esc(c.conflict ? c.conflict.time + ' โดย ' + c.conflict.department : '') + ')' : 'อยู่ระหว่างซ่อมบำรุง') + ' — กรุณาเปลี่ยนเวลาหรือเลือกรถคันอื่น').show();
        } else {
            $('#busyNote').hide();
        }
    }

    function summary() {
        var v = vals();
        $('#sumDate').text(fmtDate(v.date));
        $('#sumTime').text(v.start && v.end ? v.start + '–' + v.end + ' น.' : '-');
        $('.time-presets button').each(function () {
            $(this).toggleClass('active', $(this).data('s') === v.start && $(this).data('e') === v.end);
        });
    }

    // ---- events ----
    $('#openPicker').on('click', function (e) {
        if ($(e.target).is('#changeCar')) e.preventDefault();
        render();
        $('#carModal').modal('show');
    });
    $('#carModalBody').on('click', '.pick-card', function () {
        if ($(this).hasClass('disabled')) return;
        selected = parseInt($(this).data('id'), 10);
        $('#car_id').val(selected);
        refreshSelected();
        $('#carModal').modal('hide');
    });
    $('#availFilter').on('click', 'button', function () {
        filter = $(this).data('f');
        $('#availFilter button').removeClass('active');
        $(this).addClass('active');
        render();
    });
    $('.time-presets').on('click', 'button', function () {
        $('#pickup_start_time').val($(this).data('s'));
        $('#pickup_end_time').val($(this).data('e')).trigger('change');
    });
    $('#pickup_date, #pickup_start_time, #pickup_end_time').on('change', function () { summary(); load(); });

    $('#comp_id').select2({ placeholder: 'ค้นหาบริษัท...', allowClear: true, width: '100%' })
        .on('change', function () { $('#sumComp').text($(this).find(':selected').text() || '-'); }).trigger('change');

    $('#bookingForm').on('submit', function (e) {
        var v = vals(), msg = null;
        if (!v.date || !v.start || !v.end) msg = 'กรุณาเลือกวันและเวลา';
        else if (v.end <= v.start) msg = 'เวลาสิ้นสุดต้องหลังเวลาเริ่ม';
        else if (!selected) msg = 'กรุณาเลือกรถ';
        else if (!$('#comp_id').val()) msg = 'กรุณาเลือกบริษัท';
        else {
            var c = cars.find(function (x) { return x.id === selected; });
            if (c && !c.available) msg = 'รถที่เลือกไม่ว่างในช่วงเวลานี้';
        }
        if (msg) { e.preventDefault(); Swal.fire({ icon: 'warning', title: msg, confirmButtonText: 'ตกลง' }); return; }
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> กำลังบันทึก...');
    });

    summary();
    load();
});
</script>
@endpush
