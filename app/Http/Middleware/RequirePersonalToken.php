<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts an endpoint to the user themselves: only personal access tokens
 * and session-authenticated requests are allowed. Third-party OAuth application
 * tokens are rejected, regardless of their scopes.
 */
class RequirePersonalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->token();

        // TransientToken (session-based API auth) and missing client both indicate first-party access
        if ($token !== null && isset($token->client) && !$token->client->personal_access_client) {
            abort(403);
        }

        return $next($request);
    }
}
