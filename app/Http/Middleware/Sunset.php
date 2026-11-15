<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Announces the shutdown date of an endpoint version (RFC 8594), see ADR-0002.
 * After that date the endpoint answers with 410 Gone.
 */
class Sunset
{
    private const string CHANGELOG_URL = 'https://github.com/Traewelling/traewelling/blob/develop/API_CHANGELOG.md';

    public function handle(Request $request, Closure $next, string $date): Response
    {
        $sunset = Carbon::parse($date, 'UTC')->endOfDay();

        $response = $sunset->isPast()
            ? response()->json(['message' => 'This endpoint version has been switched off.'], Response::HTTP_GONE)
            : $next($request);

        $response->headers->set('Sunset', $sunset->toRfc7231String());
        $response->headers->set('Link', '<' . self::CHANGELOG_URL . '>; rel="sunset"', false);

        return $response;
    }
}
