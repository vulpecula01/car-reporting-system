<?php

namespace App\Services;

use App\Mail\BorrowNotificationMail;
use App\Mail\TomorrowReminderMail;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ส่งอีเมลแจ้งเตือน — ถ้าส่งไม่สำเร็จจะบันทึก log แต่ไม่ทำให้การจองล้มเหลว
 */
class BookingNotifier
{
    /** รายชื่อ email ของแผนก */
    public static function recipients(string $department): array
    {
        $emails = User::where('department', $department)
            ->whereNotNull('email')->pluck('email')->all();

        $extra = config("booking.extra_recipients.$department", '');
        $emails = array_merge($emails, array_map('trim', explode(',', (string) $extra)));

        return array_values(array_unique(array_filter($emails, function ($e) {
            return filter_var($e, FILTER_VALIDATE_EMAIL);
        })));
    }

    /**
     * แจ้งแผนกเจ้าของรถเมื่อมีการยืม (action: create / update / delete)
     */
    public static function borrow(Event $event, string $action = 'create'): void
    {
        if (!config('booking.notify_borrow')) return;

        $event->loadMissing('car', 'company');
        $car = $event->car;
        if (!$car || $car->department === $event->department) return;   // ไม่ใช่การยืม

        $to = self::recipients($car->department);
        if (!$to) return;

        try {
            Mail::to($to)->send(new BorrowNotificationMail($event, $action));
        } catch (\Throwable $e) {
            Log::warning('Borrow notification failed: ' . $e->getMessage());
        }
    }

    /**
     * ส่งสรุปงานวันพรุ่งนี้ให้ทุกแผนก — คืนจำนวนอีเมลที่ส่ง
     */
    public static function sendTomorrowReminders(?Carbon $date = null): int
    {
        $date = $date ?: now()->addDay();
        $sent = 0;

        foreach (array_keys(User::DEPARTMENTS) as $dept) {
            $events = Event::with('car', 'company')
                ->visibleTo($dept)
                ->whereDate('start', $date->toDateString())
                ->orderBy('start')
                ->get();

            if ($events->isEmpty()) continue;

            $to = self::recipients($dept);
            if (!$to) continue;

            try {
                Mail::to($to)->send(new TomorrowReminderMail($dept, $date, $events));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning("Reminder ($dept) failed: " . $e->getMessage());
            }
        }

        return $sent;
    }
}
