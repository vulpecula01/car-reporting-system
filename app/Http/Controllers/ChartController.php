<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard: การ์ดสรุป, สถานะรถวันนี้, กราฟสถิติ
 */
class ChartController extends Controller
{
    public function index(Request $request, $year = null, $month = null)
    {
        $dept = Auth::user()->department;
        $now = now();

        $selectedMonth = (int) $request->get('month', $month ?? $now->month);
        $selectedYear = (int) $request->get('year', $year ?? $now->year);
        $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // ===== การ์ดตัวเลข =====
        $mine = fn () => Event::department($dept);
        $kpi = [
            'today' => $mine()->whereDate('start', $now->toDateString())->count(),
            'week' => $mine()->whereBetween('start', [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfWeek(Carbon::SUNDAY)])->count(),
            'month' => $mine()->whereBetween('start', [$startDate, $endDate])->count(),
            'staff' => (int) $mine()->whereBetween('start', [$startDate, $endDate])->get()
                ->sum(fn ($e) => (int) preg_replace('/\D/', '', (string) $e->staff)),
        ];

        // ===== สถานะรถวันนี้ =====
        $myCars = Car::department($dept)->orderBy('name')->get();
        $todayEvents = Event::with('company')
            ->whereDate('start', $now->toDateString())
            ->where(function ($q) use ($dept, $myCars) {
                $q->whereIn('car_id', $myCars->pluck('id'))->orWhere('department', $dept);
            })
            ->orderBy('start')->get();

        // รถแผนกอื่นที่เรายืมวันนี้ ให้แสดงในสถานะด้วย
        $borrowedCarIds = $todayEvents->where('department', $dept)->pluck('car_id')->diff($myCars->pluck('id'));
        $statusCars = $myCars->concat(Car::whereIn('id', $borrowedCarIds)->orderBy('name')->get());

        $carStatus = $statusCars->map(function (Car $car) use ($todayEvents, $now, $dept) {
            $evs = $todayEvents->where('car_id', $car->id)->values();
            $current = $evs->first(fn ($e) => $now->between(Carbon::parse($e->start), Carbon::parse($e->end)));
            $next = $evs->first(fn ($e) => Carbon::parse($e->start)->gt($now));

            if (strtolower($car->service_status) !== 'active') {
                $state = ['maintenance', 'ซ่อมบำรุง', 'secondary'];
            } elseif ($current) {
                $state = $current->department !== $dept && $car->department === $dept
                    ? ['lent', 'ถูกยืมโดย ' . (User::DEPARTMENTS[$current->department] ?? ''), 'warning']
                    : ['busy', 'กำลังออกหน่วย', 'danger'];
            } elseif ($next) {
                $state = ['booked', 'มีงาน ' . date('H:i', strtotime($next->start)) . ' น.', 'info'];
            } else {
                $state = ['free', 'ว่าง', 'success'];
            }

            return (object) [
                'car' => $car,
                'state' => $state[0],
                'label' => $state[1],
                'color' => $state[2],
                'current' => $current,
                'events' => $evs,
                'borrowed' => $car->department !== $dept,
            ];
        });

        $carSummary = [
            'total' => $myCars->count(),
            'free' => $carStatus->where('borrowed', false)->whereIn('state', ['free', 'booked'])->count(),
            'busy' => $carStatus->where('borrowed', false)->whereIn('state', ['busy', 'lent'])->count(),
            'maintenance' => $carStatus->where('borrowed', false)->where('state', 'maintenance')->count(),
        ];

        // ===== ยืมข้ามแผนก (เดือนที่เลือก) =====
        $borrowIn = Event::department($dept)->whereBetween('start', [$startDate, $endDate])
            ->whereHas('car', fn ($c) => $c->where('department', '!=', $dept))->count();
        $lentOut = Event::where('department', '!=', $dept)->whereBetween('start', [$startDate, $endDate])
            ->whereHas('car', fn ($c) => $c->where('department', $dept))->count();

        // ===== กราฟ: จำนวนงานต่อรถ (เดือนที่เลือก) =====
        $perCar = Event::with('car')->department($dept)
            ->whereBetween('start', [$startDate, $endDate])
            ->select('car_id', DB::raw('count(*) as total_bookings'))
            ->groupBy('car_id')->orderByDesc('total_bookings')->get();

        // ===== กราฟ: รายเดือนทั้งปี แยกแผนก =====
        $monthlyRaw = Event::whereYear('start', $selectedYear)
            ->select('department', DB::raw('MONTH(start) as m'), DB::raw('count(*) as c'))
            ->groupBy('department', DB::raw('MONTH(start)'))->get();
        $monthlyByDept = [];
        foreach (User::DEPARTMENTS as $key => $label) {
            $row = array_fill(1, 12, 0);
            foreach ($monthlyRaw->where('department', $key) as $r) $row[(int) $r->m] = (int) $r->c;
            $monthlyByDept[$key] = ['label' => $label, 'data' => array_values($row)];
        }

        // ===== กราฟ: บริษัทที่ใช้บริการมากที่สุด (เดือนที่เลือก) =====
        $topCompanies = Event::with('company')->department($dept)
            ->whereBetween('start', [$startDate, $endDate])
            ->select('comp_id', DB::raw('count(*) as c'))
            ->groupBy('comp_id')->orderByDesc('c')->limit(6)->get();

        // ===== งานวันนี้ (ของแผนก + รถเราที่ถูกยืม) =====
        $todayList = $todayEvents->load('car');

        return view('chart', [
            'kpi' => $kpi,
            'carStatus' => $carStatus,
            'carSummary' => $carSummary,
            'borrowIn' => $borrowIn,
            'lentOut' => $lentOut,
            'perCar' => $perCar,
            'monthlyByDept' => $monthlyByDept,
            'topCompanies' => $topCompanies,
            'todayList' => $todayList,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'dept' => $dept,
            'departments' => User::DEPARTMENTS,
        ]);
    }
}
