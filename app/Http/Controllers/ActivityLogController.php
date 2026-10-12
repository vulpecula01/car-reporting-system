<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ประวัติการใช้งาน: admin เห็นทุกแผนก, user ทั่วไปเห็นเฉพาะแผนกตัวเอง
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $query = ActivityLog::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->latest('id');

        if (!$user->isAdmin()) {
            $query->where('department', $user->department);
        } elseif ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // ค่าเริ่มต้นไม่แสดงการเข้า/ออกจากระบบ (เลือกได้จากตัวกรอง "การกระทำ")
        if (!$request->filled('action')) {
            $query->whereNotIn('action', ['login', 'logout']);
        }

        foreach (['action', 'subject_type', 'user_id'] as $f) {
            if ($request->filled($f)) $query->where($f, $request->input($f));
        }
        if ($request->filled('q')) {
            $query->where('description', 'like', '%' . $request->q . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('activity_logs.index', [
            'logs' => $logs,
            'from' => $from,
            'to' => $to,
            'actions' => ActivityLog::ACTIONS,
            'subjects' => ActivityLog::SUBJECTS,
            'departments' => User::DEPARTMENTS,
            'users' => $user->isAdmin() ? User::orderBy('name')->get() : User::where('department', $user->department)->orderBy('name')->get(),
        ]);
    }
}
