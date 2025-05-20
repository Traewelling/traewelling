<?php

namespace Tests\TestHelpers;

use App\DataProviders\Repositories\StationRepository;
use App\Exceptions\HafasException;
use App\Models\Station;
use Illuminate\Support\Facades\Http;

class HafasHelpers
{

    /**
     * Get the Stopover Model from Database
     *
     * @throws HafasException
     */
    public static function getStationById(int $ibnr): Station {
        $dbStation = Station::where('ibnr', $ibnr)->first();
        return $dbStation ?? self::fetchStation($ibnr);
    }

    /**
     * Fetch from HAFAS
     *
     * @throws HafasException
     */
    public static function fetchStation(int $ibnr): Station {
        $httpClient = Http::baseUrl(config('trwl.db_rest'))
                          ->timeout(config('trwl.db_rest_timeout'));
        $response   = $httpClient->get("/stops/$ibnr");

        if (!$response->ok()) {
            throw new HafasException($response->reason());
        }

        $data = json_decode($response->body());
        return StationRepository::parseHafasStopObject($data);
    }
}
