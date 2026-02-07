<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $staff = Auth::guard('staff')->user();

        if (!$staff || !$staff->hasRole($role)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
