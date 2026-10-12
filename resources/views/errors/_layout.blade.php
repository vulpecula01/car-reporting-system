<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · Mobile Planner</title>
    <script>(function(){try{var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}?v=2">
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;">
    <div class="text-center" style="max-width:460px;">
        <div style="width:84px;height:84px;border-radius:24px;background:var(--brand-50);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:2.2rem;margin:0 auto 1.2rem;">
            <i class="fas @yield('icon')"></i>
        </div>
        <div class="text-muted fw-600">ข้อผิดพลาด @yield('code')</div>
        <h1 style="font-size:1.6rem;margin:.3rem 0 .6rem;">@yield('title')</h1>
        <p class="text-muted">@yield('message')</p>
        <div class="d-flex justify-content-center mt-4" style="gap:.5rem;">
            <a href="javascript:history.back()" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> ย้อนกลับ</a>
            <a href="{{ url('/chart') }}" class="btn btn-primary"><i class="fas fa-home mr-1"></i> หน้าหลัก</a>
        </div>
    </div>
</body>
</html>
