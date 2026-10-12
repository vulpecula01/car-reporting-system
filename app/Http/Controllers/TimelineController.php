<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ตารางรถ (Timeline) — แถวละ 1 คัน เห็นว่าคันไหนว่าง/ไม่ว่างช่วงไหน
 * view=day  : แถบเวลา 06:00–20:00 ของวันที่เลือก
 * view=week : ตาราง 7 วัน (จันทร์–อาทิตย์)
 */
class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $dept = Auth::user()->department;
        $view = $request->input('view', 'day') === 'week' ? 'week' : 'day';
        $scope = $request->input('scope', 'all') === 'mine' ? 'mine' : 'all';

        try {
            $date = Carbon::parse($request->input('date', now()->toDateString()))->startOfDay();
        } catch (\Throwable $e) {
            $date = now()->startOfDay();
        }

        if ($view === 'week') {
            $from = $date->copy()->startOfWeek(Carbon::MONDAY);
            $to = $from->copy()->addDays(6)->endOfDay();
        } else {
            $from = $date->copy();
            $to = $date->copy()->endOfDay();
        }

        // รถแผนกตัวเองขึ้นก่อน แล้วตามด้วยรถแผนกอื่น
        $cars = Car::query()
            ->when($scope === 'mine', fn ($q) => $q->where('department', $dept))
            ->orderByRaw('department = ? DESC', [$dept])
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        $events = Event::with('company')
            ->whereIn('car_id', $cars->pluck('id'))
            ->where('start', '<=', $to)
            ->where('end', '>=', $from)
            ->orderBy('start')
            ->get()
            ->groupBy('car_id');

        // ช่วงชั่วโมงของ day view (ขยายอัตโนมัติถ้ามีงานนอกช่วง)
        $hourStart = 6;
        $hourEnd = 20;
        if ($view === 'day') {
            foreach ($events->flatten() as $ev) {
                $hourStart = min($hourStart, (int) date('G', strtotime($ev->start)));
                $endH = (int) ceil((strtotime($ev->end) - strtotime(date('Y-m-d', strtotime($ev->end)))) / 3600);
                $hourEnd = max($hourEnd, min(24, $endH));
            }
        }

        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $days[] = $d->copy();
        }

        return view('timeline.index', [
            'view' => $view,
            'scope' => $scope,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'cars' => $cars,
            'events' => $events,
            'hourStart' => $hourStart,
            'hourEnd' => $hourEnd,
            'myDept' => $dept,
            'departments' => User::DEPARTMENTS,
        ]);
    }
}
