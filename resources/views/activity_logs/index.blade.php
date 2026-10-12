@extends('layouts.layout')
@section('title', 'ประวัติการใช้งาน')

@php
    $fieldNames = [
        'car_id' => 'รถ', 'comp_id' => 'บริษัท', 'start' => 'เริ่ม', 'end' => 'สิ้นสุด', 'comment' => 'หมายเหตุ',
        'staff' => 'จำนวนคน', 'name' => 'ชื่อ', 'license_plate' => 'ทะเบียน', 'service_status' => 'สถานะ',
        'image' => 'รูปภาพ', 'color' => 'สี', 'email' => 'อีเมล', 'department' => 'แผนก', 'role' => 'สิทธิ์',
        'password' => 'รหัสผ่าน', 'machine_id' => 'รหัสเครื่อง', 'machine_name' => 'ชื่อเครื่อง', 'SNu' => 'S/N', 'status' => 'สถานะ',
    ];
    $cars = \App\Models\Car::pluck('name', 'id');
    $companies = \App\Models\Company::pluck('name', 'id');
    $fmt = function ($field, $v) use ($cars, $companies) {
        if ($v === null || $v === '') return '–';
        if ($field === 'car_id') return $cars[$v] ?? "#$v";
        if ($field === 'comp_id') return $companies[$v] ?? "#$v";
        if (in_array($field, ['start', 'end']) && strtotime($v)) return date('d/m/Y H:i', strtotime($v));
        if ($field === 'image') return 'รูปภาพ';
        return \Illuminate\Support\Str::limit((string) $v, 60);
    };
    $icons = ['event' => 'fa-calendar-alt', 'car' => 'fa-bus', 'company' => 'fa-building', 'machine' => 'fa-x-ray', 'user' => 'fa-user'];
    $isAdmin = Auth::user()->isAdmin();
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>ประวัติการใช้งาน</h1>
            <p class="sub">
                ใคร ทำอะไร เมื่อไหร่ ·
                {{ $isAdmin ? 'แสดงทุกแผนก (ผู้ดูแลระบบ)' : 'แสดงเฉพาะแผนก ' . Auth::user()->department_label }}
                · พบ {{ number_format($logs->total()) }} รายการ
            </p>
        </div>
    </div>

    {{-- ตัวกรอง --}}
    <div class="card">
        <div class="card-body pb-1">
            <form method="GET" class="form-row align-items-end">
                <div class="form-group col-6 col-md-2">
                    <label for="from">ตั้งแต่</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="form-group col-6 col-md-2">
                    <label for="to">ถึง</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="form-group col-6 col-md-2">
                    <label for="action">การกระทำ</label>
                    <select id="action" name="action" class="custom-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($actions as $k => $a)<option value="{{ $k }}" {{ request('action') === $k ? 'selected' : '' }}>{{ $a[0] }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-6 col-md-2">
                    <label for="subject_type">ประเภท</label>
                    <select id="subject_type" name="subject_type" class="custom-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($subjects as $k => $l)<option value="{{ $k }}" {{ request('subject_type') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-6 col-md-2">
                    <label for="user_id">ผู้ใช้</label>
                    <select id="user_id" name="user_id" class="custom-select">
                        <option value="">ทุกคน</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                @if($isAdmin)
                <div class="form-group col-6 col-md-2">
                    <label for="department">แผนก</label>
                    <select id="department" name="department" class="custom-select">
                        <option value="">ทุกแผนก</option>
                        @foreach($departments as $k => $l)<option value="{{ $k }}" {{ request('department') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                    </select>
                </div>
                @endif
                <div class="form-group col-md-8">
                    <label for="q">ค้นหา</label>
                    <input type="search" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="เช่น ชื่อรถ, บริษัท, ชื่อผู้ใช้">
                </div>
                <div class="form-group col-md-4 d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter mr-1"></i> กรอง</button>
                    <a href="{{ route('activity_logs.index') }}" class="btn btn-light">ล้าง</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body py-1">
            @forelse($logs as $log)
                <div class="log-item">
                    <div class="when">
                        <b>{{ $log->created_at->format('H:i') }}</b>
                        {{ $log->created_at->locale('th')->translatedFormat('j M') }} {{ $log->created_at->year + 543 }}
                    </div>
                    <span class="avatar {{ $log->department }}" title="{{ $log->user_name }}">{{ mb_strtoupper(mb_substr($log->user_name ?? '?', 0, 1)) }}</span>
                    <div style="min-width:0;flex:1;">
                        <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                            <b>{{ $log->user_name ?? 'ระบบ' }}</b>
                            <span class="badge badge-soft-{{ $log->action_color }}">{{ $log->action_label }}</span>
                            @if($log->subject_type)
                                <span class="small-muted"><i class="fas {{ $icons[$log->subject_type] ?? 'fa-circle' }} mr-1"></i>{{ $log->subject_label }}</span>
                            @endif
                            @if($isAdmin && $log->department)
                                <span class="dept-badge {{ $log->department }}">{{ $departments[$log->department] ?? $log->department }}</span>
                            @endif
                        </div>
                        <div class="mt-1">{{ $log->description }}</div>
                        @if($log->changes)
                            <div class="log-changes">
                                @foreach($log->changes as $field => $pair)
                                    <div>
                                        <span class="text-muted">{{ $fieldNames[$field] ?? $field }}:</span>
                                        <span class="old">{{ $fmt($field, $pair[0] ?? null) }}</span>
                                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.7rem;"></i>
                                        <span class="new">{{ $fmt($field, $pair[1] ?? null) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="small-muted d-none d-md-block text-right" style="min-width:110px;">{{ $log->ip_address }}</div>
                </div>
            @empty
                <div class="empty-state"><i class="fas fa-history"></i>ไม่พบประวัติในช่วงที่เลือก</div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-center">{{ $logs->links() }}</div>
@endsection
