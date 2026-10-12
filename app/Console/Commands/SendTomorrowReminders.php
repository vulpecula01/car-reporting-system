<?php

namespace App\Console\Commands;

use App\Services\BookingNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTomorrowReminders extends Command
{
    protected $signature = 'bookings:remind {date? : วันที่ของงาน (Y-m-d) ค่าเริ่มต้น = พรุ่งนี้}';
    protected $description = 'ส่งอีเมลสรุปงานออกหน่วยของวันพรุ่งนี้ให้แต่ละแผนก';

    public function handle(): int
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now()->addDay();
        $sent = BookingNotifier::sendTomorrowReminders($date);
        $this->info("ส่งอีเมลแล้ว {$sent} ฉบับ (งานวันที่ {$date->format('d/m/Y')})");
        return self::SUCCESS;
    }
}
