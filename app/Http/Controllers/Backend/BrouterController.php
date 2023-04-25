<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HafasTrip;
use App\Models\PolyLine;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BrouterController extends Controller
{
    public static function getHttpClient(): PendingRequest {
        return Http::baseUrl(config('trwl.brouter_url'))
                   ->timeout(config('trwl.brouter_timeout'));
    }

    /**
     * @param array  $coordinates Array with Sub-Array containing the coordinates in order lat, lon (use floats!)
     * @param string $profile
     *
     * @return mixed|null
     * @throws \JsonException
     */
    public static function getGeoJSONForRoute(
        array  $coordinates,
        string $profile = 'rail' //Maybe extend this for other travel types later
    ): mixed {
        $coordinateString = 'brouter?lonlats=';
        foreach ($coordinates as $coords) {
            $lonlat           = implode(',', array_reverse($coords)); //brouter needs order lon,lat
            $coordinateString .= $lonlat . '|';
        }
        $coordinateString = rtrim($coordinateString, '|'); // Remove last "|"

        $response = self::getHttpClient()
                        ->get(strtr('brouter?lonlats=:coords&profile=:profile&alternativeidx=0&format=geojson', [
                            ':coords'  => $coordinateString,
                            ':profile' => $profile,
                        ]));
        Log::debug('Brouter URL is ' . $response->effectiveUri());
        if (!$response->ok()) {
            Log::debug('Brouter response was not okay.', ['body' => $response->body()]);
            return null;
        }

        return json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * 1. Fetch route for all stations of a trip at brouter
     * 2. split the route by stations (we need the GeoJSON splitted!)
     * 3. Create features for every station and route between
     *
     * @param HafasTrip $trip
     *
     * @return void
     * @throws \JsonException
     */
    public static function reroutePolyline(HafasTrip $trip) {
        //1. Prepare coordinates from stations
        $coordinates = [];
        foreach ($trip->stopoversNEW as $stopover) {
            $coordinates[] = [
                $stopover->trainStation->latitude,
                $stopover->trainStation->longitude,
            ];
        }

        //2. Request route at brouter
        $brouterGeoJSON = self::getGeoJSONForRoute($coordinates);
        //remove unnecessary data
        unset($brouterGeoJSON->features[0]->properties->messages, $brouterGeoJSON->features[0]->properties->times);

        //3. Create "new" GeoJSON splitted by stations (as features)
        $geoJson = ['type' => 'FeatureCollection', 'features' => []];

        foreach ($brouterGeoJSON?->features[0]?->geometry?->coordinates ?? [] as $coordinate) {
            $geoJson['features'][] = [
                'type'       => 'feature',
                'properties' => [],
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [
                        $coordinate[0],
                        $coordinate[1],
                    ]
                ]
            ];
        }

        //4. Try to map stations to GeoJSON
        $highestMappedKey = null;
        foreach ($trip->stopoversNEW as $stopover) {
            $properties = [
                'id'                => $stopover->trainStation->ibnr,
                'name'              => $stopover->trainStation->name,
                'departure_planned' => $stopover->departure_planned,
                'arrival_planned'   => $stopover->arrival_planned,
            ];

            //Get feature with the lowest distance to station
            $minDistance    = null;
            $closestFeatureKey = null;
            foreach ($geoJson['features'] as $key => $feature) {
                if($highestMappedKey !== null && $key <= $highestMappedKey) {
                    //Don't look again at the same stations.
                    //This is required and very important to prevent bugs for ring lines!
                    continue;
                }
                $distance = GeoController::calculateDistanceBetweenCoordinates(
                    latitudeA:  $feature['geometry']['coordinates'][1],
                    longitudeA: $feature['geometry']['coordinates'][0],
                    latitudeB:  $stopover->trainStation->latitude,
                    longitudeB: $stopover->trainStation->longitude,
                );
                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance    = $distance;
                    $closestFeatureKey = $key;
                }
            }

            $highestMappedKey = $closestFeatureKey;
            $geoJson['features'][$closestFeatureKey]['properties'] = $properties;
        }

        $polyline = PolyLine::create([
                                         'hash'     => DB::raw('UUID()'), //In this case a non required unique key
                                         'polyline' => json_encode($geoJson),
                                     ]);
        $trip->update(['polyline_id' => $polyline->id]);

        //dd(json_encode($geoJson, JSON_PRETTY_PRINT));
    }
}
