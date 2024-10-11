<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\Wikidata\FetchException;
use App\Models\Station;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

/**
 * undocumented, unstable, experimental endpoints. don't use in external applications!
 */
class ExperimentalController extends Controller
{

    public function fetchWikidata(int $stationId): JsonResponse {
        if (!self::checkGeneralRateLimit()) {
            return response()->json(['error' => 'You are requesting too fast. Please try again later.'], 429);
        }

        if (!self::checkStationRateLimit($stationId)) {
            return response()->json(['error' => 'This station was already requested recently. Please try again later.'], 429);
        }

        $allowUpdate = request()->boolean('allowUpdate');
        // TODO: there will be an error when updating non-exsiting stations.
        $station = Station::findOrFail($stationId);
        if ($station->wikidata_id && !$allowUpdate) {
            return response()->json(['error' => 'This station already has a wikidata id.'], 400);
        }

        try {
            WikidataImportService::searchStation($station);
            return response()->json(['message' => 'Wikidata information fetched successfully']);
        } catch (FetchException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    private static function checkGeneralRateLimit(): bool {
        $key = "fetch-wikidata-user:" . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return false;
        }
        RateLimiter::increment($key);
        return true;
    }

    private static function checkStationRateLimit(int $stationId): bool {
        // request a station 1 time per 5 minutes

        $key = "fetch-wikidata-station:$stationId";
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }
        RateLimiter::increment($key, 5 * 60);
        return true;
    }

}
