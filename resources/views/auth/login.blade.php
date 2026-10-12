<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f4c81">
    <title>เข้าสู่ระบบ · Mobile Planner</title>
    <link rel="icon" href="{{ asset('bus.png') }}" type="image/png">
    <script>(function(){try{var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}?v=2">
    <style>
        body { min-height: 100vh; margin: 0; display: grid; grid-template-columns: 1.1fr 1fr; background: var(--bg) !important; }
        .hero {
            position: relative; overflow: hidden; color: #fff; padding: 3rem;
            display: flex; flex-direction: column; justify-content: space-between;
            background:
                radial-gradient(1200px 500px at -10% 110%, rgba(27, 175, 122, .45), transparent 60%),
                radial-gradient(900px 500px at 110% -10%, rgba(42, 120, 214, .55), transparent 60%),
                linear-gradient(160deg, #0b2540 0%, #0f4c81 100%);
        }
        .hero::after {
            content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .08;
            background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;
        }
        .hero .brand { display: flex; align-items: center; gap: .8rem; position: relative; z-index: 1; }
        .hero .brand img { width: 52px; height: 52px; border-radius: 50%; background: #fff; padding: 3px; }
        .hero .brand b { font-family: var(--font-head); font-size: 1.15rem; display: block; }
        .hero .brand small { opacity: .75; }
        .hero h1 { color: #fff; font-size: 2.6rem; line-height: 1.2; margin: 0 0 1rem; position: relative; z-index: 1; }
        .hero p.lead { color: rgba(255, 255, 255, .8); font-size: 1.1rem; max-width: 460px; position: relative; z-index: 1; }
        .hero .features { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1.6rem; position: relative; z-index: 1; }
        .hero .features span { background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .18); padding: .45rem .85rem; border-radius: 999px; font-size: .9rem; backdrop-filter: blur(6px); }
        .hero .depts { display: flex; gap: .8rem; position: relative; z-index: 1; }
        .hero .dept { background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .16); border-radius: 16px; padding: .9rem 1.1rem; flex: 1; max-width: 220px; }
        .hero .dept i { font-size: 1.4rem; margin-bottom: .4rem; display: block; }
        .hero .dept b { display: block; font-family: var(--font-head); }
        .hero .dept small { opacity: .75; }

        .panel { display: flex; align-items: center; justify-content: center; padding: 2rem 1.25rem; position: relative; }
        .login-card { width: 100%; max-width: 400px; }
        .login-card h2 { font-size: 1.7rem; margin-bottom: .3rem; }
        .login-card .sub { color: var(--text-3); margin-bottom: 1.8rem; }
        .field { position: relative; }
        .field > i.lead-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-3); }
        .field .form-control { height: 50px; padding-left: 42px; border-radius: 12px !important; font-size: 1rem; }
        .field .toggle-pass { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: var(--text-3); width: 40px; height: 40px; border-radius: 10px; }
        .field .toggle-pass:hover { color: var(--brand); background: var(--surface-3); }
        .btn-login { height: 50px; border-radius: 12px; font-size: 1.05rem; font-weight: 600; }
        .theme-fab { position: absolute; top: 1rem; right: 1rem; }
        .foot { color: var(--text-3); font-size: .85rem; text-align: center; margin-top: 2rem; }
        .view-link { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border: 1px solid var(--border); border-radius: 12px; color: var(--text-2); text-decoration: none !important; margin-top: 1.25rem; background: var(--surface); }
        .view-link:hover { border-color: var(--brand); color: var(--brand); }
        .view-link i.fa-tv { width: 36px; height: 36px; border-radius: 10px; background: var(--brand-50); color: var(--brand); display: flex; align-items: center; justify-content: center; }

        @media (max-width: 899.98px) {
            body { grid-template-columns: 1fr; }
            .hero { padding: 1.5rem 1.25rem; }
            .hero h1 { font-size: 1.6rem; margin-top: 1.2rem; }
            .hero .features, .hero .depts, .hero p.lead { display: none; }
        }
    </style>
</head>
<body>
    <section class="hero">
        <div class="brand">
            <img src="{{ asset('BRH.webp') }}" alt="BRH">
            <div><b>Bangkok Hospital Rayong</b><small>Occupational Health</small></div>
        </div>

        <div>
            <h1>Mobile Planner<br><span style="opacity:.85;font-weight:500;">ระบบจัดการรถออกหน่วย</span></h1>
            <p class="lead">วางแผน จองรถ และติดตามสถานะรถออกหน่วยตรวจสุขภาพนอกสถานที่ ได้ในที่เดียว</p>
            <div class="features">
                <span><i class="fas fa-stream mr-1"></i> ตารางรถรายคัน</span>
                <span><i class="fas fa-exchange-alt mr-1"></i> ยืมรถข้ามแผนก</span>
                <span><i class="fas fa-chart-line mr-1"></i> Dashboard</span>
                <span><i class="fas fa-envelope mr-1"></i> แจ้งเตือนอีเมล</span>
            </div>
        </div>

        <div class="depts">
            <div class="dept"><i class="fas fa-x-ray"></i><b>X-ray</b><small>รถเอกซเรย์เคลื่อนที่</small></div>
            <div class="dept"><i class="fas fa-heartbeat"></i><b>Checkup</b><small>รถตรวจสุขภาพเคลื่อนที่</small></div>
        </div>
    </section>

    <section class="panel">
        <button type="button" class="icon-btn theme-fab" id="themeBtn" aria-label="สลับธีม"><i id="themeIcon" class="fas fa-moon"></i></button>

        <div class="login-card">
            <h2>เข้าสู่ระบบ</h2>
            <p class="sub">ใช้บัญชีของแผนก — ระบบจะแสดงรถของแผนกคุณโดยอัตโนมัติ</p>

            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center" style="gap:.6rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="on">
                @csrf
                <div class="form-group">
                    <label for="name">ชื่อผู้ใช้</label>
                    <div class="field">
                        <i class="fas fa-user lead-icon"></i>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Username" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <div class="field">
                        <i class="fas fa-lock lead-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="toggle-pass" id="togglePass" aria-label="แสดงรหัสผ่าน"><i class="far fa-eye"></i></button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" value="1">
                        <label class="custom-control-label font-weight-normal" for="remember" style="color:var(--text-2);">จดจำการเข้าสู่ระบบ</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-login">
                    เข้าสู่ระบบ <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </form>

            <a href="{{ url('/calendar-user') }}" class="view-link">
                <i class="fas fa-tv"></i>
                <div><b class="d-block" style="color:var(--text);">ดูตารางออกหน่วย</b><small>ไม่ต้องเข้าสู่ระบบ · เห็นทั้ง X-ray และ Checkup</small></div>
                <i class="fas fa-chevron-right ml-auto"></i>
            </a>

            <div class="foot">© {{ date('Y') }} BRH Rayong · Mobile Planner V.2.0</div>
        </div>
    </section>

    <script>
        document.getElementById('togglePass').addEventListener('click', function () {
            var p = document.getElementById('password');
            var show = p.type === 'password';
            p.type = show ? 'text' : 'password';
            this.innerHTML = show ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
        });
        (function () {
            function cur() { try { return localStorage.getItem('theme') || 'light'; } catch (e) { return 'light'; } }
            function apply(t) { document.documentElement.setAttribute('data-theme', t); document.getElementById('themeIcon').className = t === 'dark' ? 'fas fa-sun' : 'fas fa-moon'; }
            apply(cur());
            document.getElementById('themeBtn').addEventListener('click', function () {
                var t = cur() === 'dark' ? 'light' : 'dark';
                try { localStorage.setItem('theme', t); } catch (e) {}
                apply(t);
            });
        })();
    </script>
</body>
</html>
