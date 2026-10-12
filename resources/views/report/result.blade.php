@extends('layouts.layout')

@section('title', 'รายงาน')

@php
    $thMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $staffTotal = $events->sum(fn ($e) => (int) preg_replace('/\D/', '', (string) $e->staff));
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>รายงานการใช้รถ</h1>
            <p class="sub">{{ $thMonths[$start_month] }} – {{ $thMonths[$end_month] }} {{ $year + 543 }} · แผนก {{ Auth::user()->department_label }}</p>
        </div>
        <div class="actions">
            <form action="{{ route('report.export') }}" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="start_month" value="{{ $start_month }}">
                <input type="hidden" name="end_month" value="{{ $end_month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="btn btn-success"><i class="fas fa-file-excel mr-1"></i> ส่งออก Excel</button>
            </form>
            <button type="button" class="btn btn-light" onclick="window.print()"><i class="fas fa-print mr-1"></i> พิมพ์</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body pb-1">@include('report._form')</div>
    </div>

    <div class="kpi-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));">
        <div class="kpi"><div class="kpi-icon"><i class="fas fa-list"></i></div><div><p class="kpi-label">จำนวนงาน</p><div class="kpi-value">{{ $events->count() }}<small>งาน</small></div></div></div>
        <div class="kpi tone-checkup"><div class="kpi-icon"><i class="fas fa-user-friends"></i></div><div><p class="kpi-label">เจ้าหน้าที่ออกหน่วยรวม</p><div class="kpi-value">{{ number_format($staffTotal) }}<small>คน</small></div></div></div>
        <div class="kpi tone-warn"><div class="kpi-icon"><i class="fas fa-bus"></i></div><div><p class="kpi-label">รถที่ใช้</p><div class="kpi-value">{{ $events->pluck('car_id')->unique()->count() }}<small>คัน</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th class="pl-4">วันที่</th><th>เวลา</th><th>รายละเอียด</th><th class="text-center">คน</th><th>หมายเหตุ</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($events->sortBy('start') as $event)
                            <tr>
                                <td class="pl-4 text-nowrap">{{ \Carbon\Carbon::parse($event->start)->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($event->start)->format('H:i') }}–{{ \Carbon\Carbon::parse($event->end)->format('H:i') }}</td>
                                <td>{{ $event->description }}</td>
                                <td class="text-center">{{ $event->staff ?: '-' }}</td>
                                <td>{{ $event->comment }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><i class="far fa-folder-open"></i>ไม่พบข้อมูลในช่วงที่เลือก</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
