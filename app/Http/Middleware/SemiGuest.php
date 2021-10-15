<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class SemiGuest extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed   ...$guards
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards): mixed {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException) {
            return $next($request);
        }

        return $next($request);
    }
}
