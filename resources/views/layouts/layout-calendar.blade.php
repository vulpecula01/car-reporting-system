<!DOCTYPE html>
<html lang="th">
<head>
    @include('layouts.partials.head')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('layouts.partials.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        @include('layouts.footer-calendar')
    </div>

    @include('layouts.partials.flash')
    @stack('scripts')
</body>
</html>
