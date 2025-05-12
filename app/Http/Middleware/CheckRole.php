<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = Auth::guard('api')->user();

        if (!$user || !method_exists($user, 'getAttribute')) {
            return response()->json(['error' => 'User not found or invalid.'], 403);
        }

        $userRole = $user->role;

        foreach ($roles as $role) {
            if ($userRole == $role) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Forbidden. You do not have the required role.'], 403);
    }
}
