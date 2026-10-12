<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;  // นำเข้า Model User
use App\Models\ActivityLog;

class LoginController extends Controller
{
    // ฟังก์ชันการแสดงฟอร์ม Login
    public function showLoginForm()
    {
        return view('auth.login');  // แสดง view ฟอร์ม Login
    }

    // ฟังก์ชันตรวจสอบข้อมูลผู้ใช้
    public function login(Request $request)
    {
        // กำหนด validation rule
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        // ตรวจสอบผู้ใช้และรหัสผ่าน
        $credentials = $request->only('name', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            ActivityLog::record('login', Auth::user(), Auth::user()->name . ' เข้าสู่ระบบ');
            // ถ้าผ่านการตรวจสอบ ให้เข้าสู่ระบบ
            return redirect()->intended('/chart');  // เปลี่ยนไปที่หน้า Dashboard
        } else {
            // ถ้าไม่พบข้อมูลผู้ใช้
            return back()->withErrors(['name' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'])->withInput();
        }
    }

    // ฟังก์ชันสำหรับออกจากระบบ
    public function logout()
{
    if (Auth::check()) {
        ActivityLog::record('logout', Auth::user(), Auth::user()->name . ' ออกจากระบบ');
    }
    Auth::logout();  // Logs the user out
    session()->flush();  // Clears all session data

    // Redirect to the login page
    return redirect('/login');
}

}