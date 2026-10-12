@extends('layouts.layout')

@section('title', 'รายงาน')

@section('content')
    <div class="page-head">
        <div>
            <h1>รายงานการใช้รถ</h1>
            <p class="sub">เลือกช่วงเดือนเพื่อดูรายการ และส่งออกเป็น Excel · ข้อมูลเฉพาะแผนก {{ Auth::user()->department_label }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body pb-1">
            @include('report._form')
        </div>
    </div>
@endsection
