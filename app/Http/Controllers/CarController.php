<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;


use App\Models\Car;
use App\Models\XrayMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    public function index()
{
    // แสดงเฉพาะรถของแผนกที่ login (xray / checkup)
    $cars = Car::with('xrayMachines')->department(Auth::user()->department)->get();

    // เครื่อง X-ray ที่ถูกผูกกับรถแล้ว (นับทุกแผนก)
    $assignedXrayMachineIds = DB::table('car_xray_machine')->pluck('xray_machine_id')->toArray();

    $availableXrayMachines = XrayMachine::whereNotIn('id', $assignedXrayMachineIds)->get();

    return view('cars.index', compact('cars', 'availableXrayMachines'));
}

    

public function create()
{
    $xrayMachines = XrayMachine::all();
    return view('cars.create', compact('xrayMachines'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'license_plate' => 'required|string|max:255',
        // เครื่อง X-ray บังคับเลือกเฉพาะแผนก X-ray
        'xray_machines' => Auth::user()->isXray() ? 'required|array' : 'nullable|array',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'service_status' => 'required|string|in:active,maintenance'
    ]);

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('car_images', 'public');
    } else {
        $imagePath = null;
    }

    $car = Car::create([
        'name' => $request->name,
        'license_plate' => $request->license_plate,
        'image' => $imagePath,
        'service_status' => $request->service_status,
        'department' => Auth::user()->department,   // รถใหม่เป็นของแผนกที่ login
    ]);

    $car->xrayMachines()->sync($request->xray_machines ?? []);
    ActivityLog::record('create', $car, "เพิ่มรถ {$car->name} ({$car->license_plate})");

    // ฟอร์มเพิ่มรถส่งแบบ AJAX และรอ JSON {status: 'success'}
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(['status' => 'success', 'message' => 'Car created successfully!']);
    }
  
    return redirect()->route('cars.index')->with('success', 'Car created successfully!');
}

public function update(Request $request, $id)
{
    $car = Car::department(Auth::user()->department)->findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'license_plate' => 'required|string|max:255',
        'service_status' => 'nullable|string|in:active,maintenance',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $before = $car->only(['name', 'license_plate', 'service_status', 'image']);
    $car->name = $request->input('name');
    $car->license_plate = $request->input('license_plate');
    if ($request->filled('service_status')) {
        $car->service_status = $request->input('service_status');
    }

    if ($request->hasFile('image')) {
        // หากมีไฟล์ใหม่ ให้ลบไฟล์เก่า (ถ้ามี)
        if ($car->image) {
            Storage::disk('public')->delete($car->image);
        }
        $car->image = $request->file('image')->store('cars', 'public');
    }

    // ตรวจสอบว่ามีการส่งข้อมูล xray_machines หรือไม่
    if ($request->has('xray_machines')) {
        $car->xrayMachines()->sync($request->input('xray_machines'));
    }

    $car->save();
    ActivityLog::record('update', $car, "แก้ไขรถ {$car->name}",
        ActivityLog::diff($before, $car->only(['name', 'license_plate', 'service_status', 'image'])));

    return redirect()->route('cars.index')->with('success', 'Car updated successfully');
}

public function edit(Car $car)
    {
        $xrayMachines = XrayMachine::all();
        return view('cars.edit', compact('car', 'xrayMachines'));
    }



    public function destroy($id)
    {
        $car = Car::department(Auth::user()->department)->findOrFail($id);
        ActivityLog::record('delete', $car, "ลบรถ {$car->name} ({$car->license_plate})");
        $car->delete();
    
        return response()->json(['status' => 'success']);
    }


    public function updateServiceStatus(Request $request)
{
    
    $car = Car::department(Auth::user()->department)->find($request->id);
    if ($car) {
        $old = $car->service_status;
        $car->service_status = $request->service_status;
        $car->save();
        ActivityLog::record('status', $car, "เปลี่ยนสถานะรถ {$car->name} เป็น {$car->service_status}",
            ['service_status' => [$old, $car->service_status]]);

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Car not found']);
}

    // /get-cars : ถ้า login แล้วคืนเฉพาะรถแผนกตัวเอง, ถ้าไม่ login คืนทั้งหมด
    public function getCars()
    {
        $query = Car::query();
        if (Auth::check()) {
            $query->department(Auth::user()->department);
        }
        return response()->json($query->get());
    }

}
