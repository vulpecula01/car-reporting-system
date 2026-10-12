<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Car;  // Import Car model if you need to use car data
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;
use App\Services\BookingNotifier;

class EventController extends Controller
{
    // แผนกของ user ที่ login
    private function dept()
    {
        return Auth::user()->department;
    }

    public function index()
    {
        // Get events of the logged-in department only
        $events = Event::visibleTo($this->dept())->get()->map(function($event) {
            return [
                'id' => $event->id,
                'comp_id' => $event->comp_id,
                'start' => $event->start,
                'end' => $event->end,
                'description' => $event->description,
                'car_id' => $event->car_id, 
                'comment' => $event->comment, 
                'staff' => $event->staff, 
            ];
        })->toArray();
    
        // Return events as JSON encoded for the view
        return view('calendar', ['events' => json_encode($events)]);
    }
    // Controller
    public function indexUser()
    {
        // Fetch all companies and their colors
        $companies = Company::all();
        $companyColors = $companies->pluck('color', 'id')->toArray();
    
        // Fetch all cars (you can modify this to only fetch specific cars if needed)
        $cars = Car::all();  // หน้า view สาธารณะ: แสดงรถทุกแผนก
    
        // Fetch all events (ทุกแผนก X-ray + Checkup)
        $events = Event::with('car')->get()->map(function($event) {
            return [
                'id' => $event->id,
                'department' => $event->department,
                'car_name' => optional($event->car)->name,
                'comp_id' => $event->comp_id,
                'start' => $event->start,
                'end' => $event->end,
                'description' => $event->description,
                'car_id' => $event->car_id, 
                'comment' => $event->comment, 
                'staff' => $event->staff, 
            ];
        })->toArray();
    
        // Return the view and pass all required data
        return view('calendar-user', [
            'events' => json_encode($events),
            'companyColors' => $companyColors,
            'cars' => $cars,  // Pass the cars data to the view
            'departments' => \App\Models\User::DEPARTMENTS,
        ]);
    }
    


    public function store(Request $request)
{
    // Validate car_id to ensure it's present and exists in the cars table
    $request->validate([
        'comp_id' => 'required|exists:companies,id',
        'start' => 'required|date',
        'end' => 'required|date',
        'description' => 'required|string',
        'car_id' => 'required|exists:cars,id',   // ยืมรถแผนกอื่นได้
        'comment' => 'nullable|string|max:255', 
        'staff' => 'nullable|string|max:255', 
    ]);

    // กันจองรถซ้อนเวลา
    $start = str_replace('T', ' ', $request->start);
    $end   = str_replace('T', ' ', $request->end);
    if ($conflict = Event::findConflict($request->car_id, $start, $end)) {
        return response()->json(['success' => false, 'message' => $conflict->conflictMessage()], 422);
    }

    // Create a new event
    $event = new Event();
    $event->comp_id = $request->comp_id;
    $event->start = $request->start;
    $event->end = $request->end;
    $event->description = $request->description;
    $event->car_id = $request->car_id; 
    $event->comment = $request->comment;
    $event->staff = $request->staff;  // Save the car_id from the form
    $event->department = $this->dept();  // event เป็นของแผนกที่ login
    $event->save();

    $this->logAndNotify('create', $event);

    // Send the response back
    return response()->json(['success' => true, 'id' => $event->id]);
}

public function update(Request $request, $id)
{
    try {
        $event = Event::department($this->dept())->findOrFail($id);

        $validated = $request->validate([
            'comp_id' => 'required|exists:companies,id',
            'car_id' => 'required|exists:cars,id',   // ยืมรถแผนกอื่นได้
            'date' => 'required|date',
            'start' => 'required|date_format:Y-m-d\TH:i',
            'end' => 'required|date_format:Y-m-d\TH:i|after:start',
            'comment' => 'nullable|string',
            'staff' => 'nullable|string',
        ]);

        // กันจองรถซ้อนเวลา (ไม่นับ event ตัวเอง)
        $conflict = Event::findConflict(
            $validated['car_id'],
            str_replace('T', ' ', $validated['start']),
            str_replace('T', ' ', $validated['end']),
            $event->id
        );
        if ($conflict) {
            return response()->json(['success' => false, 'message' => $conflict->conflictMessage()], 422);
        }

        // แปลงรูปแบบเวลา 2026-10-09T08:00 -> 2026-10-09 08:00:00
        $validated['start'] = str_replace('T', ' ', $validated['start']) . ':00';
        $validated['end'] = str_replace('T', ' ', $validated['end']) . ':00';
        unset($validated['date']);

        $before = $event->only(['car_id', 'comp_id', 'start', 'end', 'comment', 'staff']);
        $event->fill($validated);
        $event->description = $this->buildDescription($event);
        $event->save();

        $changes = ActivityLog::diff($before, $event->only(['car_id', 'comp_id', 'start', 'end', 'comment', 'staff']));
        $this->logAndNotify('update', $event, $changes);

        return response()->json(['success' => true, 'message' => 'Event updated successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
    public function destroy(Event $event)
    {
        // ลบได้เฉพาะ event ของแผนกตัวเอง
        if ($event->department !== $this->dept()) {
            abort(403);
        }
        $this->logAndNotify('delete', $event);
        $event->delete();
        return response()->json(['success' => true]);
    }

    public function showCalendar()
{
    // Get events with company name and other details
    $events = Event::with('car','company') // Assuming 'company' is the relationship in Event model
        ->get()
        ->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->car->name, // Assuming the event has a 'car' relationship
                'car_name' => $event->car->name,
                'color' => $event->company->color, // Get the color from the company relationship
                'name' => $event->company->name,
                'start' => $event->start,
                'end' => $event->end,
                'description' => $event->description,
                'comment' => $event->comment,
                'staff' => $event->staff,
                'car_id' => $event->car_id,
            ];
        });
    
    // Check the contents of the events variable
    dd($events);  // This will dump the mapped data

    return view('calendar', ['events' => $events->toJson()]);
}
public function updateEvent(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|integer',
        'title' => 'required|string|max:255',
        'date' => 'required|date',
        'start' => 'required|date_format:H:i',
        'end' => 'required|date_format:H:i',
    ]);

    $event = Event::find($validated['id']);
    if ($event) {
        $event->title = $validated['title'];
        $event->date = $validated['date'];
        $event->start = $validated['start'];
        $event->end = $validated['end'];
        $event->save();

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Event not found']);
}
public function copy(Request $request)
{
    $event = Event::department($this->dept())->find($request->id);

    if ($event) {
        $newEvent = $event->replicate();
        $newEvent->start = $request->date . ' ' . date('H:i:s', strtotime($event->start));
        $newEvent->end = $request->date . ' ' . date('H:i:s', strtotime($event->end));

        if ($conflict = Event::findConflict($newEvent->car_id, $newEvent->start, $newEvent->end)) {
            return response()->json(['success' => false, 'message' => $conflict->conflictMessage()], 422);
        }

        $newEvent->save();
        $this->logAndNotify('copy', $newEvent);

        return response()->json(['success' => true, 'id' => $newEvent->id]);
    }

    return response()->json(['success' => false], 404);
}
public function getEvents() {
    // Retrieve events from the database
    $query = Event::query();
    if (Auth::check()) {
        $query->visibleTo($this->dept());   // งานแผนกตัวเอง + งานที่ยืมรถแผนกนี้
    }
    $events = $query->get();

    // Return as JSON for the calendar
    return response()->json($events);
}
public function editEvent($eventId)
{
    // Fetch the car and company data
    $cars = Car::orderBy('department')->orderBy('name')->get();
    $companies = Company::all(); // or your logic for fetching companies

    // Fetch the event data for editing
    $event = Event::department($this->dept())->find($eventId);

    // Pass the data to the view
    return view('calendar', compact('cars', 'companies', 'event'));
}
public function getCompanies()
{
    $companies = Company::all();
    return response()->json($companies);
}

public function getCars()
{
    $cars = Car::department($this->dept())->get();
    return response()->json($cars);
}
public function editForm()
{
    // ดึงข้อมูล Company, Car และ Event
    $companies = Company::all();
    $dept = $this->dept();

    // รถทุกคัน (รถแผนกตัวเองขึ้นก่อน) — เลือกรถแผนกอื่นได้กรณียืม
    $cars = Car::orderByRaw('department = ? DESC', [$dept])->orderBy('name')->get();

    // งานแผนกตัวเอง + งานที่แผนกอื่นยืมรถของแผนกนี้
    $events = Event::with('car')->visibleTo($dept)->get()->map(function ($event) use ($dept) {
        $data = $event->toArray();
        unset($data['car']);
        $data['car_name'] = optional($event->car)->name;
        // true = แผนกอื่นยืมรถของเราไป (ดูได้อย่างเดียว แก้/ลบไม่ได้)
        $data['borrowed_by_other'] = $event->department !== $dept;
        $data['borrowed_by'] = \App\Models\User::DEPARTMENTS[$event->department] ?? $event->department;
        // true = เรายืมรถแผนกอื่นมา
        $data['borrowed_car'] = $event->car && $event->car->department !== $event->department;
        return $data;
    });

    // ส่งข้อมูลไปยัง View
    return view('calendar', compact('companies', 'cars', 'events'));
}


    // สร้างข้อความ description ใหม่ตามบริษัท/รถ (กรณีแก้ไขงาน)
    private function buildDescription(Event $event): string
    {
        $event->load('car', 'company');
        $car = $event->car;
        $borrow = $car && $car->department !== $event->department;
        return optional($event->company)->name . ' | ชื่อรถ: ' . optional($car)->name
            . ($borrow ? ' (ยืมจาก ' . $car->department_label . ')' : '');
    }

    // บันทึกประวัติ + แจ้งเตือนแผนกเจ้าของรถถ้าเป็นการยืม
    private function logAndNotify(string $action, Event $event, ?array $changes = null): void
    {
        $event->loadMissing('car', 'company');
        $isBorrow = $event->car && $event->car->department !== $event->department;
        $verb = ['create' => 'เพิ่มงาน', 'update' => 'แก้ไขงาน', 'delete' => 'ลบงาน', 'copy' => 'คัดลอกงาน'][$action] ?? $action;

        ActivityLog::record(
            $action === 'create' && $isBorrow ? 'borrow' : $action,
            $event,
            $verb . ' ' . optional($event->car)->name . ($isBorrow ? ' (ยืม)' : '')
                . ' | ' . optional($event->company)->name
                . ' | ' . date('d/m/Y H:i', strtotime($event->start)) . '–' . date('H:i', strtotime($event->end)),
            $changes
        );

        if ($isBorrow) {
            BookingNotifier::borrow($event, $action === 'copy' ? 'create' : $action);
        }
    }
}
