<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserRoleMiddleware
{
    /**
     * Handle an incoming request for the user, and checks if they have the rights to proceed.
     * Otherwise, we will abort with an 401 error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param   String  $role
     * @return mixed
     */
    public function handle($request, Closure $next, String $role) {
        $u = Auth::user();
        $userrole = $u->role ?? 0; // Guests have a 0-user-role, there's another Auth middleware for guest-access.
        $r = intval($role);

        if ($userrole >= $r) {
            return $next($request);
        }

        abort(401); // Unauthorized
    }
}
