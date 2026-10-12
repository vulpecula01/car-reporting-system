<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * เปลี่ยนรหัสผ่านของตัวเอง (ทุก user)
 */
class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.min' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        ActivityLog::record('password', $user, "{$user->name} เปลี่ยนรหัสผ่าน");

        return redirect()->route('profile.password')->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
