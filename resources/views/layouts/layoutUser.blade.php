<!DOCTYPE html>
<html lang="th">
<head>
    @include('layouts.partials.head')
</head>
{{-- หน้าแสดงผลสาธารณะ (ไม่ต้อง login) — แถบด้านบนอย่างเดียว ไม่มีเมนูข้าง --}}
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        @include('layouts.navbarUser')

        <div class="content-wrapper" style="margin-left:0;">
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        @include('layouts.footerUser')
    </div>
    @stack('scripts')
</body>
</html>
