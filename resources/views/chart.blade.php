@extends('layouts.layout')

@section('title', 'Dashboard')

@php
    $thMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $thShort = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $deptLabel = $departments[$dept] ?? $dept;
    $isCurrentMonth = $selectedMonth == now()->month && $selectedYear == now()->year;
@endphp

@section('content')
    {{-- หัวหน้า + ตัวเลือกเดือน --}}
    <div class="page-head">
        <div>
            <h1>สวัสดี, {{ Auth::user()->name }}</h1>
            <p class="sub">
                <span class="dept-badge {{ $dept }}">{{ $deptLabel }}</span>
                &nbsp;ภาพรวมวันที่ {{ now()->locale('th')->translatedFormat('j F') }} {{ now()->year + 543 }}
            </p>
        </div>
        <form method="GET" action="{{ url('chart') }}" class="actions">
            <select name="month" class="custom-select" style="width:auto;" onchange="this.form.submit()" aria-label="เดือน">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $i == $selectedMonth ? 'selected' : '' }}>{{ $thMonths[$i] }}</option>
                @endfor
            </select>
            <select name="year" class="custom-select" style="width:auto;" onchange="this.form.submit()" aria-label="ปี">
                @for ($i = now()->year - 3; $i <= now()->year + 1; $i++)
                    <option value="{{ $i }}" {{ $i == $selectedYear ? 'selected' : '' }}>{{ $i + 543 }}</option>
                @endfor
            </select>
            @unless($isCurrentMonth)
                <a href="{{ url('chart') }}" class="btn btn-light">เดือนนี้</a>
            @endunless
        </form>
    </div>

    {{-- การ์ดตัวเลข --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-icon"><i class="fas fa-calendar-day"></i></div>
            <div>
                <p class="kpi-label">งานวันนี้</p>
                <div class="kpi-value">{{ number_format($kpi['today']) }}<small>งาน</small></div>
                <div class="kpi-foot">สัปดาห์นี้ {{ number_format($kpi['week']) }} งาน</div>
            </div>
        </div>
        <div class="kpi tone-checkup">
            <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
                <p class="kpi-label">งานเดือน{{ $thMonths[$selectedMonth] }}</p>
                <div class="kpi-value">{{ number_format($kpi['month']) }}<small>งาน</small></div>
                <div class="kpi-foot">เจ้าหน้าที่ออกหน่วยรวม {{ number_format($kpi['staff']) }} คน</div>
            </div>
        </div>
        <div class="kpi tone-ok">
            <div class="kpi-icon"><i class="fas fa-bus"></i></div>
            <div>
                <p class="kpi-label">รถว่างตอนนี้</p>
                <div class="kpi-value">{{ $carSummary['free'] }}<small>/ {{ $carSummary['total'] }} คัน</small></div>
                <div class="kpi-foot">ออกหน่วย {{ $carSummary['busy'] }} · ซ่อมบำรุง {{ $carSummary['maintenance'] }}</div>
            </div>
        </div>
        <div class="kpi tone-warn">
            <div class="kpi-icon"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <p class="kpi-label">ยืมข้ามแผนก (เดือนนี้)</p>
                <div class="kpi-value">{{ $borrowIn + $lentOut }}<small>ครั้ง</small></div>
                <div class="kpi-foot">เรายืม {{ $borrowIn }} · ให้ยืม {{ $lentOut }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- สถานะรถวันนี้ --}}
        <div class="col-xl-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-traffic-light mr-2 text-muted"></i>สถานะรถวันนี้</h3>
                    <span class="small-muted">อัปเดต {{ now()->format('H:i') }} น.</span>
                    <div class="card-tools"><a href="{{ route('timeline.index') }}" class="btn btn-soft btn-sm"><i class="fas fa-stream mr-1"></i> ดูตารางรถ</a></div>
                </div>
                <div class="card-body">
                    @if($carStatus->isEmpty())
                        <div class="empty-state"><i class="fas fa-bus"></i>ยังไม่มีรถในแผนก — <a href="{{ route('cars.index') }}">เพิ่มรถ</a></div>
                    @else
                        <div class="car-grid">
                            @foreach($carStatus as $cs)
                                <div class="car-tile">
                                    <div class="car-img">
                                        @if($cs->car->image)
                                            <img src="{{ asset('storage/' . $cs->car->image) }}" alt="{{ $cs->car->name }}" onerror="this.src='{{ asset('bus.png') }}';this.classList.add('placeholder-img')">
                                        @else
                                            <img src="{{ asset('bus.png') }}" class="placeholder-img" alt="">
                                        @endif
                                        <span class="car-state"><span class="state-pill {{ $cs->color }}">{{ $cs->label }}</span></span>
                                    </div>
                                    <div class="car-body">
                                        <p class="car-name">{{ $cs->car->name }}</p>
                                        <div class="car-plate">
                                            {{ $cs->car->license_plate }}
                                            @if($cs->borrowed)<span class="dept-badge {{ $cs->car->department }} ml-1">ยืมจาก {{ $cs->car->department_label }}</span>@endif
                                        </div>
                                        @if($cs->current)
                                            <div class="car-job"><i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ optional($cs->current->company)->name }} · ถึง {{ date('H:i', strtotime($cs->current->end)) }}</div>
                                        @elseif($cs->events->count())
                                            <div class="car-job"><i class="far fa-clock mr-1"></i>วันนี้ {{ $cs->events->count() }} งาน</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- งานวันนี้ --}}
        <div class="col-xl-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title"><i class="far fa-clock mr-2 text-muted"></i>งานวันนี้</h3>
                    <div class="card-tools"><a href="{{ route('car_booking.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> จองรถ</a></div>
                </div>
                <div class="card-body" style="max-height:420px;overflow:auto;">
                    @forelse($todayList as $ev)
                        @php $lent = $ev->department !== $dept; @endphp
                        @if($loop->first)<ul class="agenda">@endif
                        <li>
                            <div class="time">{{ date('H:i', strtotime($ev->start)) }}–{{ date('H:i', strtotime($ev->end)) }}</div>
                            <div class="bar {{ $ev->department }}"></div>
                            <div>
                                <div class="what">{{ optional($ev->company)->name ?? '-' }}</div>
                                <div class="meta">
                                    <i class="fas fa-bus mr-1"></i>{{ optional($ev->car)->name }}
                                    @if($ev->staff) · <i class="fas fa-user-friends mx-1"></i>{{ $ev->staff }} คน @endif
                                </div>
                                @if($lent)<span class="badge badge-soft-warning mt-1">ถูกยืมโดย {{ $departments[$ev->department] ?? $ev->department }}</span>@endif
                                @if(!$lent && $ev->car && $ev->car->department !== $dept)<span class="badge badge-soft-info mt-1">ยืมจาก {{ $ev->car->department_label }}</span>@endif
                            </div>
                        </li>
                        @if($loop->last)</ul>@endif
                    @empty
                        <div class="empty-state"><i class="far fa-calendar"></i>วันนี้ไม่มีงานออกหน่วย</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- กราฟรายเดือน แยกแผนก --}}
        <div class="col-xl-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title">จำนวนงานรายเดือน ปี {{ $selectedYear + 543 }}</h3>
                    <div class="legend-inline ml-auto">
                        @foreach($monthlyByDept as $key => $s)
                            <span><i style="background:var(--{{ $key }});"></i>{{ $s['label'] }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="card-body"><div class="chart-box"><canvas id="monthlyChart" aria-label="กราฟจำนวนงานรายเดือนแยกแผนก" role="img"></canvas></div></div>
            </div>
        </div>

        {{-- ยืมข้ามแผนก --}}
        <div class="col-xl-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header"><h3 class="card-title">ยืมรถข้ามแผนก · {{ $thMonths[$selectedMonth] }}</h3></div>
                <div class="card-body">
                    <div class="stat-split mb-3">
                        <div><div class="l"><i class="fas fa-sign-in-alt mr-1"></i>เรายืมแผนกอื่น</div><div class="v">{{ $borrowIn }}</div></div>
                        <div><div class="l"><i class="fas fa-sign-out-alt mr-1"></i>แผนกอื่นยืมเรา</div><div class="v">{{ $lentOut }}</div></div>
                    </div>
                    <p class="small-muted mb-2">บริษัทที่ใช้บริการมากที่สุด</p>
                    @forelse($topCompanies as $tc)
                        @php $max = max(1, $topCompanies->max('c')); @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small"><span class="text-truncate pr-2">{{ optional($tc->company)->name ?? 'ไม่ระบุ' }}</span><b>{{ $tc->c }}</b></div>
                            <div style="height:8px;background:var(--surface-3);border-radius:4px;overflow:hidden;">
                                <div style="height:100%;width:{{ round($tc->c / $max * 100) }}%;background:var(--{{ $dept }});border-radius:4px;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-3">ยังไม่มีข้อมูล</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- จำนวนงานต่อรถ --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">จำนวนงานต่อรถ · {{ $thMonths[$selectedMonth] }} {{ $selectedYear + 543 }}</h3>
            <span class="small-muted">{{ $startDate->format('d/m') }} – {{ $endDate->format('d/m') }}</span>
        </div>
        <div class="card-body">
            @if($perCar->isEmpty())
                <div class="empty-state"><i class="fas fa-chart-bar"></i>ยังไม่มีงานในเดือนนี้</div>
            @else
                <div class="chart-box sm"><canvas id="perCarChart" aria-label="กราฟจำนวนงานต่อรถ" role="img"></canvas></div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.js') }}"></script>
<script>
(function () {
    var months = @json($thShort);
    var monthly = @json($monthlyByDept);
    var perCarLabels = @json($perCar->map(fn ($r) => optional($r->car)->name ?? 'ไม่ระบุ')->values());
    var perCarData = @json($perCar->pluck('total_bookings')->values());
    var myDept = @json($dept);
    var charts = [];

    function css(v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); }

    function build() {
        charts.forEach(function (c) { c.destroy(); });
        charts = [];
        var text2 = css('--text-2'), text3 = css('--text-3'), grid = css('--border'), surface = css('--surface');
        Chart.defaults.font.family = "'Sarabun', sans-serif";
        Chart.defaults.color = text3;

        var tooltip = { backgroundColor: css('--surface'), titleColor: css('--text'), bodyColor: text2, borderColor: grid, borderWidth: 1, padding: 10, cornerRadius: 10, displayColors: true, boxPadding: 4 };

        var el = document.getElementById('monthlyChart');
        if (el) {
            charts.push(new Chart(el, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: Object.keys(monthly).map(function (k) {
                        var c = css('--' + k);
                        return {
                            label: monthly[k].label, data: monthly[k].data, borderColor: c, backgroundColor: c,
                            borderWidth: 2, tension: .3, cubicInterpolationMode: 'monotone', pointRadius: 4, pointHoverRadius: 6,
                            pointBackgroundColor: c, pointBorderColor: surface, pointBorderWidth: 2
                        };
                    })
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: Object.assign({}, tooltip, { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + c.raw + ' งาน'; } } }) },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid }, border: { display: false } },
                        x: { grid: { display: false }, border: { color: grid } }
                    }
                }
            }));
        }

        var el2 = document.getElementById('perCarChart');
        if (el2) {
            var c2 = css('--' + myDept);
            charts.push(new Chart(el2, {
                type: 'bar',
                data: { labels: perCarLabels, datasets: [{ label: 'จำนวนงาน', data: perCarData, backgroundColor: c2, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 48 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: Object.assign({}, tooltip, { displayColors: false, callbacks: { label: function (c) { return c.raw + ' งาน'; } } }) },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid }, border: { display: false } },
                        x: { grid: { display: false }, border: { color: grid } }
                    }
                }
            }));
        }
    }

    document.addEventListener('DOMContentLoaded', build);
    document.addEventListener('themechange', function () { setTimeout(build, 30); });
})();
</script>
@endpush
