<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;

class SemiScope extends Middleware
{
    /**
     * Unauthenticated users need to be able to access the routes,
     * while authenticated users need to have the correct scope.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed   ...$scopes
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws MissingScopeException
     */
    public function handle($request, Closure $next, ...$scopes): mixed {
        try {
            $this->authenticate($request, ['api']);
        } catch (AuthenticationException) {
            return $next($request);
        }
        return (new CheckForAnyScope())->handle($request, $next, ...$scopes);
    }
}
