<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class JsonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
