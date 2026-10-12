<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Car;
use App\Models\Event;
use App\Models\Company; // นำเข้า Model ของ companies
use App\Services\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CarBookingController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::orderBy('name')->get(); // ดึงข้อมูลบริษัททั้งหมด
        $selectedDate = $request->query('date', date('Y-m-d')); // ดึงวันที่จาก query string หรือใช้วันนี้เป็นค่าเริ่มต้น

        // ส่งข้อมูลไปยัง View (รายการรถโหลดผ่าน AJAX จาก car_booking.availability)
        return view('car_booking.index', [
            'companies' => $companies,
            'selectedDate' => $selectedDate,
            'selectedCar' => $request->query('car_id'),
            'selectedStart' => $request->query('start', '08:00'),
            'selectedEnd' => $request->query('end', '12:00'),
        ]);
    }

    /**
     * รายการรถทั้งหมดพร้อมสถานะว่าง/ไม่ว่าง ตามวันและเวลาที่เลือก (ใช้ใน popup เลือกรถ)
     * GET /car-availability?date=2026-10-09&start=08:00&end=12:00
     */
    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start' => 'nullable|date_format:H:i',
            'end' => 'nullable|date_format:H:i',
        ]);

        $myDept = Auth::user()->department;
        $date = $request->date;
        $start = $request->filled('start') ? "$date {$request->start}:00" : null;
        $end = $request->filled('end') ? "$date {$request->end}:00" : null;

        $cars = Car::orderByRaw('department = ? DESC', [$myDept])->orderBy('name')->get();

        // งานทั้งหมดของวันนั้น (ใช้แสดงตารางของแต่ละคัน)
        $dayEvents = Event::with('company')
            ->whereIn('car_id', $cars->pluck('id'))
            ->whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date)
            ->orderBy('start')
            ->get()
            ->groupBy('car_id');

        $data = $cars->map(function (Car $car) use ($myDept, $start, $end, $dayEvents) {
            $events = $dayEvents->get($car->id, collect());
            $conflict = null;
            if ($start && $end && $start < $end) {
                $conflict = $events->first(fn ($e) => $e->start < $end && $e->end > $start);
            }
            $active = strtolower($car->service_status) === 'active';

            return [
                'id' => $car->id,
                'name' => $car->name,
                'license_plate' => $car->license_plate,
                'image' => $car->image && Storage::disk('public')->exists($car->image)
                    ? asset('storage/' . $car->image) : asset('bus.png'),
                'department' => $car->department,
                'department_label' => $car->department_label,
                'own' => $car->department === $myDept,
                'active' => $active,
                'available' => $active && !$conflict,
                'conflict' => $conflict ? [
                    'department' => \App\Models\User::DEPARTMENTS[$conflict->department] ?? $conflict->department,
                    'company' => optional($conflict->company)->name,
                    'time' => date('H:i', strtotime($conflict->start)) . '–' . date('H:i', strtotime($conflict->end)),
                ] : null,
                'bookings' => $events->map(fn ($e) => [
                    'time' => date('H:i', strtotime($e->start)) . '–' . date('H:i', strtotime($e->end)),
                    'department' => \App\Models\User::DEPARTMENTS[$e->department] ?? $e->department,
                    'company' => optional($e->company)->name,
                ])->values(),
            ];
        });

        return response()->json(['cars' => $data]);
    }

    public function bookXray(Request $request)
    {
        $request->validate([
            // จองได้ทั้งรถของแผนกตัวเอง และยืมรถแผนกอื่น (ต้องเป็นรถที่ active)
            'car_id' => ['required', Rule::exists('cars', 'id')->where('service_status', 'active')],
            'comp_id' => 'required|exists:companies,id', // ตรวจสอบ company_id
            'pickup_date' => 'required|date',
            'pickup_start_time' => 'required|date_format:H:i',
            'pickup_end_time' => 'required|date_format:H:i|after:pickup_start_time',
        ], [
            'car_id.required' => 'กรุณาเลือกรถ',
            'car_id.exists' => 'รถคันนี้ไม่พร้อมใช้งาน',
            'comp_id.required' => 'กรุณาเลือกบริษัท',
            'pickup_end_time.after' => 'เวลาสิ้นสุดต้องหลังเวลาเริ่มต้น',
        ]);

        $car = Car::find($request->car_id);
        $company = Company::find($request->comp_id);
        $myDept = Auth::user()->department;

        $start = $request->pickup_date . ' ' . $request->pickup_start_time;
        $end   = $request->pickup_date . ' ' . $request->pickup_end_time;

        // กันจองรถซ้อนเวลา
        if ($conflict = Event::findConflict($car->id, $start, $end)) {
            return back()->withErrors(['car_id' => $conflict->conflictMessage()])->withInput();
        }

        $isBorrow = $car->department !== $myDept;

        $event = Event::create([
            'car_id' => $car->id,
            'comp_id' => $request->comp_id, // ใช้ชื่อบริษัท
            'start' => $start,
            'end' => $end,
            'comment' => $request->comment,
            'staff' => $request->staff,
            'description' => $company->name . ' | ชื่อรถ: ' . $car->name
                . ($isBorrow ? ' (ยืมจาก ' . $car->department_label . ')' : ''),
            // งานเป็นของแผนกผู้จอง (ผู้ยืม) — แผนกเจ้าของรถจะเห็นใน Planner ว่ารถถูกยืม
            'department' => $myDept,
        ]);

        ActivityLog::record($isBorrow ? 'borrow' : 'create', $event,
            ($isBorrow ? 'ยืมรถ ' : 'จองรถ ') . $car->name . ' | ' . $company->name
            . ' | ' . date('d/m/Y H:i', strtotime($start)) . '–' . date('H:i', strtotime($end)));

        if ($isBorrow) {
            BookingNotifier::borrow($event, 'create');
        }

        return redirect()->route('calendar.index')->with('success', 'บันทึกการจองเรียบร้อยแล้ว');
    }
}
