<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * อนุญาตเฉพาะผู้ดูแลระบบ (role = admin)
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'เมนูนี้สำหรับผู้ดูแลระบบเท่านั้น');
        }

        return $next($request);
    }
}
