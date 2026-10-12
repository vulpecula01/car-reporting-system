{{-- แถบด้านบน (ผู้ใช้ที่ login แล้ว) --}}
@php $u = Auth::user(); @endphp
<header class="main-header">
    <nav class="navbar navbar-expand">
        <a class="icon-btn" data-widget="pushmenu" href="#" role="button" aria-label="เมนู"><i class="fas fa-bars"></i></a>
        <span class="topbar-title d-none d-sm-inline">@yield('title', 'Mobile Planner')</span>

        <div class="topbar-actions">
            @hasSection('fullscreen')
                <button id="fullscreenButton" type="button" class="icon-btn" data-tip="เต็มจอ" aria-label="เต็มจอ"><i class="fas fa-expand"></i></button>
            @endif
            <a href="{{ route('car_booking.index') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex align-items-center" style="gap:.4rem;height:40px;padding:0 .9rem;">
                <i class="fas fa-plus"></i> จองรถ
            </a>
            <button type="button" class="icon-btn" data-toggle-theme data-tip="สลับโหมดมืด/สว่าง" aria-label="สลับธีม"><i data-theme-icon class="fas fa-moon"></i></button>

            <div class="dropdown">
                <button class="user-chip" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="avatar {{ $u->department }}">{{ $u->initials }}</span>
                    <span class="name d-none d-md-inline">{{ $u->name }}</span>
                    <i class="fas fa-chevron-down text-muted d-none d-md-inline" style="font-size:.7rem;"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" style="min-width:240px;">
                    <div class="d-flex align-items-center px-2 py-2" style="gap:.6rem;">
                        <span class="avatar lg {{ $u->department }}">{{ $u->initials }}</span>
                        <div>
                            <div class="fw-600">{{ $u->name }}</div>
                            <span class="dept-badge {{ $u->department }}">{{ $u->department_label }}</span>
                            @if($u->isAdmin())<span class="badge badge-soft-dark ml-1">Admin</span>@endif
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('profile.password') }}"><i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน</a>
                    @if($u->isAdmin())
                        <a class="dropdown-item" href="{{ route('users.index') }}"><i class="fas fa-users-cog"></i> จัดการผู้ใช้</a>
                    @endif
                    <a class="dropdown-item" href="{{ url('/calendar-user') }}" target="_blank"><i class="fas fa-tv"></i> หน้าแสดงผล (View)</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt text-danger"></i> ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</header>
