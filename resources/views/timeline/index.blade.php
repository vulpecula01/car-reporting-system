@extends('layouts.layout')

@section('title', 'ตารางรถ')

@php
    $span = max(1, $hourEnd - $hourStart);
    $dayStartTs = $date->copy()->setTime($hourStart, 0)->timestamp;
    $spanSec = $span * 3600;
    $isToday = $date->isToday();
    $nowPct = $isToday ? (now()->timestamp - $dayStartTs) / $spanSec * 100 : null;
    $prev = $view === 'week' ? $date->copy()->subWeek() : $date->copy()->subDay();
    $next = $view === 'week' ? $date->copy()->addWeek() : $date->copy()->addDay();
    $q = fn ($d, $extra = []) => route('timeline.index', array_merge(['date' => $d->toDateString(), 'view' => $view, 'scope' => $scope], $extra));
    $thDays = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
    $groups = $cars->groupBy('department');
    // แผนกตัวเองขึ้นก่อน
    $groups = $groups->sortBy(fn ($v, $k) => $k === $myDept ? 0 : 1);
    $carImg = fn ($car) => $car->image ? asset('storage/' . $car->image) : asset('bus.png');
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>ตารางรถ</h1>
            <p class="sub">
                @if($view === 'week')
                    สัปดาห์ {{ $from->locale('th')->translatedFormat('j M') }} – {{ $to->locale('th')->translatedFormat('j M') }} {{ $to->year + 543 }}
                @else
                    {{ $date->locale('th')->translatedFormat('l j F') }} {{ $date->year + 543 }}
                    @if($isToday)<span class="badge badge-soft-info ml-1">วันนี้</span>@endif
                @endif
                · คลิกช่องว่างเพื่อจองรถ
            </p>
        </div>
        <div class="actions">
            <div class="segmented" role="tablist" aria-label="มุมมอง">
                <a href="{{ route('timeline.index', ['date' => $date->toDateString(), 'view' => 'day', 'scope' => $scope]) }}" class="{{ $view === 'day' ? 'active' : '' }}">รายวัน</a>
                <a href="{{ route('timeline.index', ['date' => $date->toDateString(), 'view' => 'week', 'scope' => $scope]) }}" class="{{ $view === 'week' ? 'active' : '' }}">รายสัปดาห์</a>
            </div>
            <div class="segmented" aria-label="รถที่แสดง">
                <a href="{{ $q($date, ['scope' => 'all']) }}" class="{{ $scope === 'all' ? 'active' : '' }}">ทุกแผนก</a>
                <a href="{{ $q($date, ['scope' => 'mine']) }}" class="{{ $scope === 'mine' ? 'active' : '' }}">เฉพาะแผนกฉัน</a>
            </div>
            <div class="btn-group">
                <a href="{{ $q($prev) }}" class="btn btn-light" aria-label="ก่อนหน้า"><i class="fas fa-chevron-left"></i></a>
                <a href="{{ $q(now()) }}" class="btn btn-light">วันนี้</a>
                <a href="{{ $q($next) }}" class="btn btn-light" aria-label="ถัดไป"><i class="fas fa-chevron-right"></i></a>
            </div>
            <input type="date" class="form-control" style="width:auto;" value="{{ $date->toDateString() }}"
                   onchange="location.href='{{ route('timeline.index') }}?view={{ $view }}&scope={{ $scope }}&date='+this.value" aria-label="เลือกวันที่">
        </div>
    </div>

    @if($cars->isEmpty())
        <div class="card"><div class="card-body empty-state"><i class="fas fa-bus"></i>ยังไม่มีรถ</div></div>
    @else
    <div class="tl-wrap">
        <div class="tl">
            {{-- หัวตาราง --}}
            <div class="tl-row tl-head">
                <div class="tl-car">รถ ({{ $cars->count() }} คัน)</div>
                <div class="tl-track">
                    @if($view === 'day')
                        <div class="tl-hours">
                            @for($h = $hourStart; $h < $hourEnd; $h++)
                                <div>{{ sprintf('%02d:00', $h) }}</div>
                            @endfor
                        </div>
                    @else
                        <div class="tl-week">
                            @foreach($days as $d)
                                <div class="tl-day-head {{ $d->isToday() ? 'today' : '' }}">
                                    {{ $thDays[$d->dayOfWeek] }} {{ $d->format('j') }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @foreach($groups as $dept => $deptCars)
                <div class="tl-group">
                    <span class="dept-badge {{ $dept }}">{{ $departments[$dept] ?? $dept }}</span>
                    {{ $dept === $myDept ? 'รถของแผนกคุณ' : 'รถแผนกอื่น — จองได้ (เป็นการยืม)' }}
                </div>

                @foreach($deptCars as $car)
                    @php
                        $active = strtolower($car->service_status) === 'active';
                        $carEvents = $events->get($car->id, collect());
                    @endphp
                    <div class="tl-row {{ $active ? '' : 'maint' }}">
                        <div class="tl-car">
                            <img src="{{ $carImg($car) }}" alt="" onerror="this.src='{{ asset('bus.png') }}'">
                            <div style="min-width:0;">
                                <div class="n text-truncate">{{ $car->name }}</div>
                                <div class="p">{{ $car->license_plate }}
                                    @unless($active)<span class="badge badge-soft-secondary ml-1">ซ่อมบำรุง</span>@endunless
                                </div>
                            </div>
                        </div>

                        <div class="tl-track">
                            @if($view === 'day')
                                <div class="tl-grid">@for($h = $hourStart; $h < $hourEnd; $h++)<div></div>@endfor</div>
                                @if($active)
                                    <a class="tl-free" href="{{ route('car_booking.index', ['date' => $date->toDateString(), 'car_id' => $car->id]) }}"
                                       data-tip="จอง {{ $car->name }}" aria-label="จอง {{ $car->name }}"></a>
                                @endif
                                @foreach($carEvents as $ev)
                                    @php
                                        $s = max($dayStartTs, strtotime($ev->start));
                                        $e = min($dayStartTs + $spanSec, strtotime($ev->end));
                                        $left = ($s - $dayStartTs) / $spanSec * 100;
                                        $width = max(1.5, ($e - $s) / $spanSec * 100);
                                        $borrow = $ev->department !== $car->department;
                                        $tip = date('H:i', strtotime($ev->start)) . '–' . date('H:i', strtotime($ev->end)) . ' · ' . (optional($ev->company)->name ?? '-')
                                            . ' · แผนก ' . ($departments[$ev->department] ?? $ev->department)
                                            . ($ev->staff ? ' · ' . $ev->staff . ' คน' : '') . ($ev->comment ? ' · ' . $ev->comment : '');
                                    @endphp
                                    <div class="tl-block {{ $ev->department }} {{ $borrow ? 'borrow' : '' }}"
                                         style="left:{{ $left }}%;width:{{ $width }}%;" data-tip="{{ $tip }}">
                                        <b>{{ date('H:i', strtotime($ev->start)) }}–{{ date('H:i', strtotime($ev->end)) }}</b>
                                        {{ optional($ev->company)->name }}
                                        @if($borrow) · ยืมโดย {{ $departments[$ev->department] ?? $ev->department }}@endif
                                    </div>
                                @endforeach
                                @if($nowPct !== null && $nowPct >= 0 && $nowPct <= 100)
                                    <div class="tl-now" style="left:{{ $nowPct }}%;"></div>
                                @endif
                            @else
                                <div class="tl-week">
                                    @foreach($days as $d)
                                        @php
                                            $dayEvents = $carEvents->filter(fn ($ev) => date('Y-m-d', strtotime($ev->start)) <= $d->toDateString() && date('Y-m-d', strtotime($ev->end)) >= $d->toDateString());
                                        @endphp
                                        <div class="{{ $d->isToday() ? 'today' : '' }} {{ $d->isWeekend() ? 'weekend' : '' }}">
                                            @foreach($dayEvents as $ev)
                                                @php $borrow = $ev->department !== $car->department; @endphp
                                                <div class="tl-chip {{ $ev->department }} {{ $borrow ? 'borrow' : '' }}"
                                                     data-tip="{{ optional($ev->company)->name }} · แผนก {{ $departments[$ev->department] ?? $ev->department }}{{ $ev->comment ? ' · ' . $ev->comment : '' }}">
                                                    <b>{{ date('H:i', strtotime($ev->start)) }}–{{ date('H:i', strtotime($ev->end)) }}</b><br>
                                                    <span class="d-inline-block text-truncate" style="max-width:100%;">{{ optional($ev->company)->name }}</span>
                                                </div>
                                            @endforeach
                                            @if($active)
                                                <a class="tl-add" href="{{ route('car_booking.index', ['date' => $d->toDateString(), 'car_id' => $car->id]) }}">+ จอง</a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="tl-legend">
        <span><i style="background:var(--xray-soft);border-left:4px solid var(--xray);"></i>งานแผนก X-ray</span>
        <span><i style="background:var(--checkup-soft);border-left:4px solid var(--checkup);"></i>งานแผนก Checkup</span>
        <span><i style="outline:1px dashed var(--border-strong);background:repeating-linear-gradient(135deg,transparent 0 3px,rgba(0,0,0,.12) 3px 6px);"></i>ยืมข้ามแผนก</span>
        <span><i style="background:repeating-linear-gradient(45deg,var(--surface-2) 0 4px,var(--surface-3) 4px 8px);"></i>ซ่อมบำรุง</span>
        @if($view === 'day' && $isToday)<span><i style="background:var(--danger);width:3px;"></i>เวลาปัจจุบัน</span>@endif
    </div>
    @endif
@endsection
