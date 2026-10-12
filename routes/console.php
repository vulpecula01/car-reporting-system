<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ส่งอีเมลสรุปงานวันพรุ่งนี้ทุกวัน (ต้องตั้ง Windows Task Scheduler ให้รัน "php artisan schedule:run" ทุก 1 นาที)
if (config('booking.notify_reminder')) {
    Schedule::command('bookings:remind')->dailyAt(config('booking.reminder_time', '16:00'));
}
