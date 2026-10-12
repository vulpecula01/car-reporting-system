<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // หน้าแบ่งหน้า (pagination) ใช้ Bootstrap 4 ให้เข้ากับ AdminLTE
        Paginator::useBootstrapFour();

        // ชื่อวัน/เดือนภาษาไทย
        Carbon::setLocale('th');
    }
}
