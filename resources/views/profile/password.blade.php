@extends('layouts.layout')
@section('title', 'เปลี่ยนรหัสผ่าน')

@section('content')
    <div class="page-head">
        <div>
            <h1>เปลี่ยนรหัสผ่าน</h1>
            <p class="sub">บัญชี {{ Auth::user()->name }} · <span class="dept-badge {{ Auth::user()->department }}">{{ Auth::user()->department_label }}</span></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-xl-5">
            <div class="card">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">รหัสผ่านปัจจุบัน</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="6" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-hint">อย่างน้อย 6 ตัวอักษร</div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key mr-1"></i> เปลี่ยนรหัสผ่าน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
