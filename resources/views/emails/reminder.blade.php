@extends('emails.layout')
@php
    $depts = \App\Models\User::DEPARTMENTS;
    $deptLabel = $depts[$department] ?? $department;
@endphp
@section('body')
    <h2 style="margin:0 0 4px;font-size:20px;">งานออกหน่วยวันพรุ่งนี้ — แผนก {{ $deptLabel }}</h2>
    <p style="margin:0 0 16px;color:#475569;">{{ $date->locale('th')->translatedFormat('l j F') }} {{ $date->year + 543 }} · ทั้งหมด {{ $events->count() }} งาน</p>

    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
        <tr style="background:#0f4c81;color:#fff;">
            <th align="left">เวลา</th><th align="left">รถ</th><th align="left">บริษัท</th><th align="left">คน</th>
        </tr>
        @foreach($events as $i => $ev)
            @php $borrowedOut = $ev->department !== $department; $borrowedIn = $ev->car && $ev->car->department !== $ev->department && !$borrowedOut; @endphp
            <tr style="background:{{ $i % 2 ? '#ffffff' : '#f8fafc' }};">
                <td style="white-space:nowrap;"><b>{{ date('H:i', strtotime($ev->start)) }}–{{ date('H:i', strtotime($ev->end)) }}</b></td>
                <td>
                    {{ optional($ev->car)->name }}
                    @if($borrowedOut)<br><span style="color:#d97706;font-size:12px;">ถูกยืมโดย {{ $depts[$ev->department] ?? $ev->department }}</span>@endif
                    @if($borrowedIn)<br><span style="color:#2563eb;font-size:12px;">ยืมจาก {{ $depts[$ev->car->department] ?? '' }}</span>@endif
                </td>
                <td>{{ optional($ev->company)->name ?? '-' }}@if($ev->comment)<br><span style="color:#64748b;font-size:12px;">{{ $ev->comment }}</span>@endif</td>
                <td>{{ $ev->staff ?: '-' }}</td>
            </tr>
        @endforeach
    </table>

    <p style="margin:22px 0 0;">
        <a href="{{ rtrim(config('booking.app_url'), '/') }}/timeline?date={{ $date->toDateString() }}"
           style="background:#0f4c81;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;display:inline-block;">เปิดตารางรถ</a>
    </p>
@endsection
