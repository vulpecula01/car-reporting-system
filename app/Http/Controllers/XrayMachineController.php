<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

use App\Models\XrayMachine;
use Illuminate\Http\Request;

class XrayMachineController extends Controller
{
    public function index()
    {
        $machines = XrayMachine::all();
        return view('xray_machines.index', compact('machines'));
    }

    public function create()
    {
        return view('xray_machines.create');
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'machine_id' => 'required|string|max:255',
        'machine_name' => 'required|string|max:255',
        'SNu' => 'required|string|max:255',
        'status' => 'required|in:active,inactive',
    ]);

    $machine = new XrayMachine();
    $machine->machine_id = $request->input('machine_id');
    $machine->machine_name = $request->input('machine_name');
    $machine->SNu = $request->input('SNu');
    $machine->status = $request->input('status');
    $machine->save();
    ActivityLog::record('create', $machine, "เพิ่มเครื่อง X-ray {$machine->machine_id} {$machine->machine_name}");

    return response()->json(['success' => 'X-ray machine added successfully']);
}

    public function edit(XrayMachine $xrayMachine)
    {
        return view('xray_machines.edit', compact('xrayMachine'));
    }

    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'machine_id' => 'required|string|max:255',
        'machine_name' => 'required|string|max:255',
        'SNu' => 'required|string|max:255',
        'status' => 'required|in:active,inactive',
    ]);

    $machine = XrayMachine::findOrFail($id);
    $before = $machine->only(['machine_id', 'machine_name', 'SNu', 'status']);
    $machine->machine_id = $request->input('machine_id');
    $machine->machine_name = $request->input('machine_name');
    $machine->SNu = $request->input('SNu');
    $machine->status = $request->input('status');
    $machine->save();
    ActivityLog::record('update', $machine, "แก้ไขเครื่อง X-ray {$machine->machine_id}",
        ActivityLog::diff($before, $machine->only(['machine_id', 'machine_name', 'SNu', 'status'])));

    return response()->json(['success' => 'X-ray machine updated successfully']);
}

public function destroy($id)
{
    $xrayMachine = XrayMachine::findOrFail($id);
    ActivityLog::record('delete', $xrayMachine, "ลบเครื่อง X-ray {$xrayMachine->machine_id} {$xrayMachine->machine_name}");
    $xrayMachine->delete();
    return redirect()->route('xray_machines.index')->with('success', 'X-ray machine deleted successfully.');
}
}
