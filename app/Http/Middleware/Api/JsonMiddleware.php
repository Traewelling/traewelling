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
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
