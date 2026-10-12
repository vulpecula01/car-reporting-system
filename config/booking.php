<?php

/*
|--------------------------------------------------------------------------
| ตั้งค่าการแจ้งเตือนทาง Email
|--------------------------------------------------------------------------
| ต้องตั้งค่า SMTP ใน .env ก่อน (MAIL_MAILER=smtp, MAIL_HOST, MAIL_PORT, ...)
| ผู้รับ = user ของแผนกนั้นที่มี email + รายชื่อเพิ่มเติมจาก .env
*/

return [
    // แจ้งแผนกเจ้าของรถ เมื่อแผนกอื่นยืม / แก้ไข / ยกเลิกการยืม
    'notify_borrow' => env('NOTIFY_BORROW', true),

    // ส่งสรุปงานวันพรุ่งนี้ทุกวัน (ต้องตั้ง Task Scheduler ให้รัน php artisan schedule:run)
    'notify_reminder' => env('NOTIFY_REMINDER', true),
    'reminder_time'   => env('NOTIFY_REMINDER_TIME', '16:00'),

    // email เพิ่มเติมของแต่ละแผนก (คั่นด้วย ,) เช่น NOTIFY_XRAY_EMAILS="a@brh.co.th,b@brh.co.th"
    'extra_recipients' => [
        'xray'    => env('NOTIFY_XRAY_EMAILS', ''),
        'checkup' => env('NOTIFY_CHECKUP_EMAILS', ''),
    ],

    // ลิงก์ระบบที่ใส่ในอีเมล
    'app_url' => env('APP_URL', 'http://localhost'),
];
