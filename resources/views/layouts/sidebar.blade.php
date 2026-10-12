@php $u = Auth::user(); @endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand -->
    <a href="{{ route('chart.index') }}" class="brand-link">
        <img src="{{ asset('BRH.webp') }}" alt="BRH" class="brand-image img-circle">
        <span class="brand-text">Mobile Planner<span class="brand-sub">X-ray · Checkup</span></span>
    </a>

    <div class="sidebar">
        <!-- ผู้ใช้ -->
        <div class="user-panel mt-2 d-flex">
            <div class="image pl-1"><span class="avatar {{ $u->department }}">{{ $u->initials }}</span></div>
            <div class="info">
                <a href="{{ route('profile.password') }}" class="d-block">{{ $u->name }}</a>
                <span class="dept-badge {{ $u->department }}">{{ $u->department_label }}</span>
            </div>
        </div>

        <nav class="mt-1">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">ภาพรวม</li>
                <li class="nav-item">
                    <a href="{{ route('chart.index') }}" class="nav-link" data-match="^/chart">
                        <i class="nav-icon fas fa-chart-pie"></i><p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('timeline.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-stream"></i><p>ตารางรถ (Timeline)</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('calendar.index') }}" class="nav-link">
                        <i class="nav-icon far fa-calendar-alt"></i><p>Planner</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('car_booking.index') }}" class="nav-link" data-match="^/car[-_]booking">
                        <i class="nav-icon fas fa-calendar-plus"></i><p>จองรถ</p>
                    </a>
                </li>

                <li class="nav-header">ข้อมูล</li>
                <li class="nav-item">
                    <a href="{{ route('cars.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-bus"></i><p>รายการรถยนต์</p>
                    </a>
                </li>
                @if($u->isXray())
                <li class="nav-item">
                    <a href="{{ route('xray_machines.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-x-ray"></i><p>เครื่อง X-ray</p>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('companies.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-building"></i><p>บริษัท</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('report.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-file-excel"></i><p>รายงาน</p>
                    </a>
                </li>

                <li class="nav-header">ระบบ</li>
                <li class="nav-item">
                    <a href="{{ route('activity_logs.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-history"></i><p>ประวัติการใช้งาน</p>
                    </a>
                </li>
                @if($u->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-users-cog"></i><p>จัดการผู้ใช้</p>
                    </a>
                </li>
                @endif

                <hr class="sidebar-divider">
                <li class="nav-item">
                    <a href="http://10.121.10.218/vehicle/FormRequest.aspx" class="nav-link" target="_blank" rel="noopener">
                        <i class="nav-icon fas fa-external-link-alt"></i><p>BDMS App Store <small class="d-block" style="opacity:.7">(จองรถ BDMS)</small></p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
