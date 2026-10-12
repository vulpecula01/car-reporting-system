<header class="main-header" style="margin-left:0;">
    <nav class="navbar navbar-expand">
        <a href="{{ url('/calendar-user') }}" class="d-flex align-items-center text-decoration-none" style="gap:.6rem;">
            <img src="{{ asset('BRH.webp') }}" alt="BRH" style="height:36px;width:36px;border-radius:50%;background:#fff;padding:2px;border:1px solid var(--border);">
            <span>
                <span class="topbar-title d-block m-0" style="line-height:1.1;">Mobile Planner</span>
                <small class="text-muted d-none d-sm-block">ตารางออกหน่วย X-ray · Checkup</small>
            </span>
        </a>
        <div class="topbar-actions">
            <button id="fullscreenButton" type="button" class="icon-btn" data-tip="เต็มจอ" aria-label="เต็มจอ"><i class="fas fa-expand"></i></button>
            <button type="button" class="icon-btn" data-toggle-theme data-tip="สลับโหมดมืด/สว่าง" aria-label="สลับธีม"><i data-theme-icon class="fas fa-moon"></i></button>
            @auth
                <a href="{{ route('chart.index') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center" style="gap:.4rem;height:40px;padding:0 .9rem;"><i class="fas fa-th-large"></i> <span class="d-none d-sm-inline">เข้าระบบจัดการ</span></a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center" style="gap:.4rem;height:40px;padding:0 .9rem;"><i class="fas fa-sign-in-alt"></i> <span class="d-none d-sm-inline">เข้าสู่ระบบ</span></a>
            @endauth
        </div>
    </nav>
</header>
