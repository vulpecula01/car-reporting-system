@extends('emails.layout')
@php
    $depts = \App\Models\User::DEPARTMENTS;
    $by = $depts[$event->department] ?? $event->department;
    $owner = $depts[optional($event->car)->department] ?? '';
    $title = ['create' => 'มีการยืมรถของแผนกคุณ', 'update' => 'มีการแก้ไขการยืมรถของแผนกคุณ', 'delete' => 'การยืมรถถูกยกเลิก'][$action] ?? 'มีการยืมรถ';
    $color = ['create' => '#2563eb', 'update' => '#d97706', 'delete' => '#dc2626'][$action] ?? '#2563eb';
@endphp
@section('body')
    <div style="display:inline-block;background:{{ $color }};color:#fff;border-radius:999px;padding:4px 12px;font-size:13px;">{{ $title }}</div>
    <h2 style="margin:14px 0 4px;font-size:20px;">{{ optional($event->car)->name }}</h2>
    <p style="margin:0 0 16px;color:#475569;">แผนก <b>{{ $by }}</b> ยืมรถของแผนก <b>{{ $owner }}</b></p>

    <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:15px;">
        <tr style="background:#f8fafc;"><td width="35%" style="color:#64748b;">วันที่</td><td><b>{{ \Carbon\Carbon::parse($event->start)->locale('th')->translatedFormat('l j F') }} {{ \Carbon\Carbon::parse($event->start)->year + 543 }}</b></td></tr>
        <tr><td style="color:#64748b;">เวลา</td><td><b>{{ date('H:i', strtotime($event->start)) }} – {{ date('H:i', strtotime($event->end)) }} น.</b></td></tr>
        <tr style="background:#f8fafc;"><td style="color:#64748b;">บริษัท</td><td>{{ optional($event->company)->name ?? '-' }}</td></tr>
        <tr><td style="color:#64748b;">จำนวนคนออกหน่วย</td><td>{{ $event->staff ?: '-' }}</td></tr>
        <tr style="background:#f8fafc;"><td style="color:#64748b;">หมายเหตุ</td><td>{{ $event->comment ?: '-' }}</td></tr>
    </table>

    <p style="margin:22px 0 0;">
        <a href="{{ rtrim(config('booking.app_url'), '/') }}/timeline?date={{ date('Y-m-d', strtotime($event->start)) }}"
           style="background:#0f4c81;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;display:inline-block;">ดูตารางรถ</a>
    </p>
@endsection
