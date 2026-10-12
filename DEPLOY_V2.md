# Mobile Planner V2 — วิธีขึ้น Server

## สิ่งที่เพิ่มใน V2
- **หน้าตาใหม่ทั้งระบบ**: ฟอนต์ไทย Sarabun/Prompt (อยู่ในเครื่อง ไม่ต้องใช้อินเทอร์เน็ต), เมนูใหม่, Dark mode, รองรับมือถือ
- **หน้า Login ใหม่**: แสดง/ซ่อนรหัสผ่าน, จดจำการเข้าสู่ระบบ, ลิงก์ไปหน้าดูตารางงาน
- **Dashboard ใหม่**: การ์ดสรุป, สถานะรถวันนี้ (มีรูปรถ), งานวันนี้, กราฟรายเดือนแยกแผนก, สถิติการยืมรถ, บริษัทที่ใช้บริการบ่อย
- **ตารางรถ (Timeline)**: แถวละ 1 คัน ดูได้ทั้งรายวันและรายสัปดาห์ คลิกช่องว่างเพื่อจองรถคันนั้นได้ทันที
- **หน้าจองรถใหม่**: เลือกวันและเวลาก่อน แล้วเลือกรถจาก **popup ที่มีรูปรถ** ซึ่งแสดงว่าคันไหนว่างหรือไม่ว่าง
- **แจ้งเตือนทางอีเมล**: แจ้งแผนกเจ้าของรถเมื่อมีการยืม และส่งสรุปงานวันพรุ่งนี้ทุกวัน
- **ประวัติการใช้งาน**: บันทึกว่าใคร ทำอะไร เมื่อไหร่ พร้อมค่าก่อนและหลังแก้ไข
- **จัดการผู้ใช้** (เฉพาะ admin) และ **เปลี่ยนรหัสผ่าน** (ทุกคน)
- **หน้า View**: กรองแผนกได้, บนมือถือแสดงแบบรายการ, รีเฟรชเองทุก 5 นาที

## ขั้นตอน

### 1. สำรองไฟล์บน server ก่อน

### 2. วางไฟล์
แตก `v2_update.zip` ที่ root ของโปรเจกต์ (โฟลเดอร์ที่มี `artisan`) แล้วเลือกเขียนทับไฟล์เดิม

### 3. อัปเดตฐานข้อมูล (รันครั้งเดียว)
รัน `database/sql/upgrade_v2.sql` ใน MySQL Workbench (ฐานข้อมูล `xray`)
- เพิ่มคอลัมน์ `users.role` และตั้ง **user id 1 (Anurak) เป็น admin**
- สร้างตาราง `activity_logs`

> ถ้าต้องการให้ user อื่นเป็น admin ให้รัน `UPDATE users SET role='admin' WHERE id = ?;`

### 4. ตั้งค่าอีเมลใน `.env` (ถ้าต้องการใช้แจ้งเตือน)
```
APP_URL=http://10.121.111.233:8820

MAIL_MAILER=smtp
MAIL_HOST=smtp.ของโรงพยาบาล
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="mobileplanner@bangkokhospital.com"
MAIL_FROM_NAME="Mobile Planner"

# (ไม่บังคับ)
NOTIFY_BORROW=true
NOTIFY_REMINDER=true
NOTIFY_REMINDER_TIME=16:00
NOTIFY_XRAY_EMAILS="a@bangkokhospital.com,b@bangkokhospital.com"
NOTIFY_CHECKUP_EMAILS="c@bangkokhospital.com"
```
- ผู้รับอีเมลคือ **user ของแผนกนั้นที่มีอีเมล** รวมกับรายชื่อใน `NOTIFY_*_EMAILS`
- ถ้ายังไม่ตั้ง SMTP (`MAIL_MAILER=log`) ระบบยังทำงานได้ปกติ อีเมลจะถูกเขียนลง `storage/logs/laravel.log` แทน

### 5. ตั้งให้ส่งสรุปงานพรุ่งนี้อัตโนมัติ (Windows Task Scheduler)
เปิด Command Prompt แบบ Administrator แล้วรันคำสั่งด้านล่าง (แก้ path ให้ตรงกับ server)
```
schtasks /create /tn "MobilePlanner Reminder" /sc daily /st 16:00 /tr "cmd /c cd /d D:\path\car-reporting-system && php artisan bookings:remind"
```
ทดสอบส่งทันที: `php artisan bookings:remind` (หรือระบุวันที่ เช่น `php artisan bookings:remind 2026-10-10`)

### 6. ล้าง cache
```
php artisan optimize:clear
```

### 7. ทดสอบ
- เข้าระบบด้วย Anurak → เมนู **จัดการผู้ใช้** ต้องแสดง
- เปิดหน้าจองรถ → กดเลือกรถ → ต้องมี popup รูปรถพร้อมสถานะว่างหรือไม่ว่าง
- เปิดเมนู **ตารางรถ** และ **ประวัติการใช้งาน**

## หมายเหตุ
- แก้บั๊กเดิมที่ Event เชื่อมกับ Company ผิดคอลัมน์ (`company_id` → `comp_id`) ทำให้ชื่อบริษัทแสดงได้ถูกต้อง
- Planner: การลบงานมีหน้าต่างยืนยันก่อนลบ และแก้บั๊กที่กดคัดลอกแล้วได้งานซ้ำหลายรายการ
- หน้า Planner และหน้า View ไม่โหลด jQuery ซ้ำหลายรอบแล้ว (เดิมโหลด 3 รอบ)
- ไฟล์ใหม่ที่ต้องมี: `public/css/modern.css`, `public/css/fonts.css`, `public/js/modern.js`, `public/fonts/*`, `public/vendor/fontawesome/*`
