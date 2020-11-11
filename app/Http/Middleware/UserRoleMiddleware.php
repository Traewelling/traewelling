<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request for the user, and checks if they have the rights to proceed.
     * Otherwise, we will abort with an 401 error.
     *
     * @param Request $request
     * @param Closure $next
     * @param String $roleString
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roleString) {
        $userRole = Auth::user()->role ?? 0; // Guests have a 0-user-role, there's another Auth middleware for guest-access.
        $role     = intval($roleString);

        if ($userRole >= $role) {
            return $next($request);
        }

        abort(401); // Unauthorized
    }
}
