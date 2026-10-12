<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * จัดการผู้ใช้ (เฉพาะ admin)
 */
class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('department')->orderBy('name')->get();
        return view('users.index', [
            'users' => $users,
            'departments' => User::DEPARTMENTS,
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'department' => ['required', Rule::in(array_keys(User::DEPARTMENTS))],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ], $this->messages());

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department' => $data['department'],
            'role' => $data['role'],
        ]);

        ActivityLog::record('create', $user, "เพิ่มผู้ใช้ {$user->name} ({$user->department_label})");

        return redirect()->route('users.index')->with('success', "เพิ่มผู้ใช้ {$user->name} เรียบร้อยแล้ว");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'department' => ['required', Rule::in(array_keys(User::DEPARTMENTS))],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ], $this->messages());

        // กันไม่ให้ admin ลดสิทธิ์ตัวเอง (จะไม่มีใครจัดการระบบได้)
        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'ไม่สามารถลดสิทธิ์ผู้ดูแลระบบของตัวเองได้']);
        }

        $before = $user->only(['name', 'email', 'department', 'role']);
        $user->fill(collect($data)->only(['name', 'email', 'department', 'role'])->all());
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $changes = ActivityLog::diff($before, $user->only(['name', 'email', 'department', 'role']));
        if (!empty($data['password'])) $changes['password'] = ['***', 'รีเซ็ตรหัสผ่าน'];
        ActivityLog::record('update', $user, "แก้ไขผู้ใช้ {$user->name}", $changes);

        return redirect()->route('users.index')->with('success', "บันทึกข้อมูล {$user->name} เรียบร้อยแล้ว");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'ไม่สามารถลบบัญชีของตัวเองได้']);
        }

        $name = $user->name;
        ActivityLog::record('delete', $user, "ลบผู้ใช้ {$name} ({$user->department_label})");
        $user->delete();

        return redirect()->route('users.index')->with('success', "ลบผู้ใช้ {$name} แล้ว");
    }

    private function messages(): array
    {
        return [
            'name.unique' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว',
            'email.unique' => 'อีเมลนี้มีอยู่แล้ว',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ];
    }
}
