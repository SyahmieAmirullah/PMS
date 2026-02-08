<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $staff = Auth::guard('staff')->user();

        $roleList = array_filter(array_map('trim', explode('|', $roles)));
        $hasAnyRole = false;
        foreach ($roleList as $role) {
            if ($staff && $staff->hasRole($role)) {
                $hasAnyRole = true;
                break;
            }
        }

        if (!$hasAnyRole) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
