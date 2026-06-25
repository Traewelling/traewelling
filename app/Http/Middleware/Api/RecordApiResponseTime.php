<?php

declare(strict_types=1);

namespace App\Http\Middleware\Api;

use App\Helpers\CacheKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecordApiResponseTime
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - LARAVEL_START) * 1000);
        $bucket = (int) floor(time() / 60);

        $sumKey = CacheKey::getApiResponseTimeSumKey($bucket);
        $countKey = CacheKey::getApiResponseTimeCountKey($bucket);

        $statusCode = $response->getStatusCode();
        $rcKey = CacheKey::getApiResponseCodeKey($bucket, $statusCode);

        $ttl = now()->addMinutes(5);
        if (!Cache::add($sumKey, $durationMs, $ttl)) {
            Cache::increment($sumKey, $durationMs);
        }
        if (!Cache::add($countKey, 1, $ttl)) {
            Cache::increment($countKey);
        }
        if (!Cache::add($rcKey, 1, $ttl)) {
            Cache::increment($rcKey);
        }

        return $response;
    }
}
