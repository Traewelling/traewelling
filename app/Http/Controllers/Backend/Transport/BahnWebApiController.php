<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class BahnWebApiController extends Controller
{

    public static function searchStation(string $query, int $limit = 10): Collection {
        $url      = "https://www.bahn.de/web/api/reiseloesung/orte?suchbegriff=" . urlencode($query) . "&typ=ALL&limit=" . $limit;
        $response = Http::get($url);
        $json     = $response->json();
        $extIds   = [];
        foreach ($json as $rawStation) {
            if (!isset($rawStation['extId'])) {
                continue;
            }
            $extIds[] = $rawStation['extId'];
        }
        $stationCache = Station::whereIn('ibnr', $extIds)->get();

        $stations = collect();
        foreach ($json as $rawStation) {
            if (!isset($rawStation['extId'])) {
                continue;
            }
            $station = $stationCache->where('ibnr', $rawStation['extId'])->first();
            if ($station === null) {
                $station = Station::create([
                                               'name'      => $rawStation['name'],
                                               'latitude'  => $rawStation['lat'],
                                               'longitude' => $rawStation['lon'],
                                               'ibnr'      => $rawStation['extId'],
                                               'source'    => 'bahn-web-api',
                                           ]);
            }
            $stations->push($station);
        }

        return $stations;
    }
}
