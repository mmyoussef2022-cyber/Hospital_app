<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // المدير العام له صلاحية الوصول لكل شيء
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // التحقق من الأدوار المطلوبة
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة');
        }

        return $next($request);
    }
}