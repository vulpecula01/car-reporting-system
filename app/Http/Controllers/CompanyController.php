<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // Show list of companies (already existing method)
    public function index()
    {
        $companies = Company::all();
        return view('companies.index', compact('companies'));
    }

    // Store new company (already existing method)
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/', // ตรวจสอบรูปแบบสี Hex
    ]);

    $company = Company::create([
        'name' => $request->name,
        'color' => $request->color,
    ]);
    ActivityLog::record('create', $company, "เพิ่มบริษัท {$company->name}");

    return response()->json(['success' => 'Company added successfully!']);
}
    // Update existing company
    public function update(Request $request, $id)
{
    $company = Company::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/', // ตรวจสอบรูปแบบสี Hex
    ]);

    $before = $company->only(['name', 'color']);
    $company->update([
        'name' => $request->name,
        'color' => $request->color,
    ]);
    ActivityLog::record('update', $company, "แก้ไขบริษัท {$company->name}",
        ActivityLog::diff($before, $company->only(['name', 'color'])));

    return response()->json(['success' => 'Company updated successfully!']);
}

    // Delete company
    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // Delete the company
        ActivityLog::record('delete', $company, "ลบบริษัท {$company->name}");
        $company->delete();

        return response()->json(['success' => 'Company deleted successfully!']);
    }
}
