<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * อนุญาตเฉพาะ user แผนก X-ray (ใช้กับหน้าเครื่อง X-ray)
 */
class XrayOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isXray()) {
            abort(403, 'เมนูนี้สำหรับแผนก X-ray เท่านั้น');
        }

        return $next($request);
    }
}
