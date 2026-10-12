<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\XrayMachineController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CarBookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;

Route::get('/calendar-user', [EventController::class, 'indexUser'])->name('calendar.indexUser');
Route::get('/calendar-user', [EventController::class, 'indexUser']);



Route::resource('companies', CompanyController::class);
Route::get('/get-events', [EventController::class, 'getEvents']);
Route::get('/get-companies', [CompanyController::class, 'getCompanies']);
Route::get('/get-cars', [CarController::class, 'getCars']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');  // แสดงฟอร์ม Login
Route::post('/login', [LoginController::class, 'login']);  // ตรวจสอบการ Login

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');  // การออกจากระบบ
    Route::post('/cars/update-service-status', [CarController::class, 'updateServiceStatus'])->name('cars.updateServiceStatus');
// Car Routes
Route::get('/chart/{year?}/{month?}', [ChartController::class, 'index'])->name('chart.index');
Route::get('/chart', [ChartController::class, 'index']);
Route::get('cars', [CarController::class, 'index'])->name('cars.index');
Route::get('cars/create', [CarController::class, 'create'])->name('cars.create');
Route::post('cars', [CarController::class, 'store'])->name('cars.store');
Route::get('cars/{id}/edit', [CarController::class, 'edit'])->name('cars.edit');
Route::put('cars/{id}', [CarController::class, 'update'])->name('cars.update');
Route::delete('cars/{id}', [CarController::class, 'destroy'])->name('cars.destroy');
Route::delete('/cars/{id}', [CarController::class, 'destroy'])->name('cars.destroy');
Route::get('/cars/data', [CarController::class, 'getData'])->name('cars.data');
// X-ray Machines Routes (เฉพาะแผนก X-ray)
Route::middleware(\App\Http\Middleware\XrayOnly::class)->group(function () {
    Route::get('machines', [XrayMachineController::class, 'index'])->name('xray_machines.index');
    Route::get('machines/create', [XrayMachineController::class, 'create'])->name('xray_machines.create');
    Route::post('machines', [XrayMachineController::class, 'store'])->name('xray_machines.store');
    Route::get('machines/{id}/edit', [XrayMachineController::class, 'edit'])->name('xray_machines.edit');
    Route::put('machines/{id}', [XrayMachineController::class, 'update'])->name('xray_machines.update');
    Route::delete('machines/{id}', [XrayMachineController::class, 'destroy'])->name('xray_machines.destroy');
});
Route::post('/cars/update-service-status', [CarController::class, 'updateServiceStatus'])->name('cars.updateServiceStatus');
Route::post('/update-car-status/{id}', [CarController::class, 'updateStatus']);

Route::get('/car-booking', [CarBookingController::class, 'index'])->name('car_booking.index');
Route::post('/book/xray', [CarBookingController::class, 'bookXray'])->name('book.xray');
Route::get('/car-bookings', [CarBookingController::class, 'showBookings'])->name('car.bookings');

Route::get('car_booking', [CarBookingController::class, 'index'])->name('car_booking.index');
Route::post('book_xray', [CarBookingController::class, 'bookXray'])->name('book.xray');

Route::get('/calendar', [EventController::class, 'index'])->name('calendar.index');
Route::post('/events', [EventController::class, 'store'])->name('events.store');
Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

Route::post('/report/export', [ReportController::class, 'export'])->name('report.export');
Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
Route::delete('/events/{event}', [EventController::class, 'destroy']);
Route::get('/report', [ReportController::class, 'index'])->name('report.index');
Route::post('/report', [ReportController::class, 'generateReport'])->name('report.generate');
Route::get('/report/export', [ReportController::class, 'exportToExcel'])->name('report.export');

Route::get('/calendar', [EventController::class, 'index'])->name('calendar.index');
Route::post('/events/copy', [EventController::class, 'copy']);


Route::resource('companies', CompanyController::class);
Route::get('/get-events', [EventController::class, 'getEvents']);
Route::post('/calendar', [EventController::class, 'updateEvent']);
Route::get('/get-companies', [CompanyController::class, 'getCompanies']);
Route::get('/get-cars', [CarController::class, 'getCars']);
Route::get('/calendar', [EventController::class, 'editForm'])->name('calendar.index');
Route::get('/editEvent/{id}', [EventController::class, 'editEvent']);
Route::put('/events/{eventId}', [EventController::class, 'update'])->name('events.update');
Route::put('/events/{id}', [EventController::class, 'update']);


// ===== V2 =====
// ตารางรถ (Timeline รายคัน)
Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');
// รถว่าง/ไม่ว่าง สำหรับ popup เลือกรถในหน้าจอง
Route::get('/car-availability', [CarBookingController::class, 'availability'])->name('car_booking.availability');
// ประวัติการใช้งาน
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
// เปลี่ยนรหัสผ่านตัวเอง
Route::get('/profile/password', [ProfileController::class, 'edit'])->name('profile.password');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
// จัดการผู้ใช้ (admin เท่านั้น)
Route::middleware(\App\Http\Middleware\AdminOnly::class)->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

});