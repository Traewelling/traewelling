<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request for the user, and checks if they have the rights to proceed.
     * Otherwise, we will abort with an 401 error.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $requiredRoleLevel
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $requiredRoleLevel): mixed {
        // Guests have a 0-user-role, there's another Auth middleware for guest-access.
        $userRole = auth()->check() ? auth()->user()->role : 0;

        if ($userRole < (int) $requiredRoleLevel) {
            abort(401); // Unauthorized
        }

        return $next($request);
    }
}
