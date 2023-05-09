<?php

namespace App\Http\Controllers\Backend;

use App\Dto\Coordinate;
use App\Enum\BrouterProfile;
use App\Exceptions\DistanceDeviationException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Jobs\RefreshPolyline;
use App\Models\HafasTrip;
use App\Models\PolyLine;
use App\Models\TrainCheckin;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use stdClass;
use Str;

abstract class BrouterController extends Controller
{
    public static function getHttpClient(): PendingRequest {
        return Http::baseUrl(config('trwl.brouter_url'))
                   ->timeout(config('trwl.brouter_timeout'));
    }

    /**
     * @param array          $coordinates Array of App\Dto\Coordinate objects
     * @param BrouterProfile $profile
     *
     * @return null|stdClass
     * @throws JsonException
     */
    public static function getGeoJSONForRoute(
        array          $coordinates,
        BrouterProfile $profile = BrouterProfile::RAIL //Maybe extend this for other travel types later
    ): ?stdClass {
        $lonlats = [];
        foreach ($coordinates as $coord) {
            $lonlats[] = $coord->longitude . ',' . $coord->latitude; //brouter needs order lon,lat
        }
        $coordinateString = implode('|', $lonlats);

        $response = self::getHttpClient()
                        ->get(strtr('brouter?lonlats=:coords&profile=:profile&alternativeidx=0&format=geojson', [
                            ':coords'  => $coordinateString,
                            ':profile' => $profile->value,
                        ]));
        Log::debug('Brouter URL is ' . $response->effectiveUri());
        if (!$response->ok()) {
            Log::debug('Brouter response was not okay.', ['body' => $response->body()]);
            return null;
        }

        $geoJson = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
        //remove unnecessary data
        unset($geoJson->features[0]->properties->messages, $geoJson->features[0]->properties->times);

        if (!isset($geoJson->features[0]->geometry->coordinates)) {
            throw new InvalidArgumentException('required data is missing');
        }

        return $geoJson;
    }

    /**
     * 1. Fetch route for all stations of a trip at brouter
     * 2. split the route by stations (we need the GeoJSON splitted!)
     * 3. Create features for every station and route between
     *
     * @param HafasTrip $trip
     *
     * @return void
     * @throws JsonException
     */
    public static function reroutePolyline(HafasTrip $trip): void {
        if (App::runningUnitTests()) {
            return;
        }
        //1. Prepare coordinates from stations
        $coordinates = [];
        foreach ($trip->stopoversNEW as $stopover) {
            $coordinates[] = new Coordinate($stopover->trainStation->latitude, $stopover->trainStation->longitude);
        }

        try {
            //2. Request route at brouter
            $brouterGeoJSON = self::getGeoJSONForRoute($coordinates);
        } catch (InvalidArgumentException) {
            Log::error('Error while getting Polyline for HafasTrip#' . $trip->trip_id . ' (Required data is missing in Brouter response)');
            return;
        }
        //3. Create "new" GeoJSON splitted by stations (as features)
        $geoJson = ['type' => 'FeatureCollection', 'features' => []];

        foreach ($brouterGeoJSON?->features[0]?->geometry?->coordinates ?? [] as $coordinate) {
            $geoJson['features'][] = [
                'type'       => 'Feature',
                'properties' => new stdClass(),
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
            $minDistance       = null;
            $closestFeatureKey = null;
            foreach ($geoJson['features'] as $key => $feature) {
                if ($highestMappedKey !== null && $key <= $highestMappedKey) {
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
                    $minDistance       = $distance;
                    $closestFeatureKey = $key;
                }
            }

            $highestMappedKey                                      = $closestFeatureKey;
            $geoJson['features'][$closestFeatureKey]['properties'] = $properties;
        }

        $polyline    = PolyLine::create([
                                            'hash'     => Str::uuid(), //In this case a non required unique key
                                            'polyline' => json_encode($geoJson),
                                            'source'   => 'brouter',
                                        ]);
        $oldPolyLine = $trip->polyline_id;
        $trip->update(['polyline_id' => $polyline->id]);

        //Refresh distance and points of trips
        $trainCheckinToRecalc = TrainCheckin::with(['status'])->where('trip_id', $trip->trip_id)->get();
        try {
            foreach ($trainCheckinToRecalc as $trainCheckin) {
                TrainCheckinController::refreshDistanceAndPoints($trainCheckin->status);
            }
        } catch (DistanceDeviationException) {
            $trip->update(['polyline_id' => $oldPolyLine]);
        }
    }

    /**
     * Check if Polyline has missing parts. If yes: Automatically schedule a job to get a real route via brouter
     *
     * @param HafasTrip $hafasTrip
     *
     * @return void
     */
    public static function checkPolyline(HafasTrip $hafasTrip): void {
        if (!$hafasTrip->category?->onRails()) {
            return;
        }

        if (!self::checkIfPolylineHasMissingParts($hafasTrip)) {
            Log::debug('no parts missing');
            //Nothing to do here.
            return;
        }
        Log::debug('parts missing: dispatch');
        RefreshPolyline::dispatch($hafasTrip);
    }

    private static function checkIfPolylineHasMissingParts(HafasTrip $hafasTrip): bool {
        $geoJson      = json_decode($hafasTrip->polyline->polyline);
        $features     = $geoJson->features;
        $lastStopOver = null; // To detect whether as the crow flies or real routing
        foreach ($features as $key => $data) {
            if (!isset($data->properties->id)) {
                $lastStopOver = null;
            } else {
                if (!is_null($lastStopOver) && $hafasTrip?->category?->onRails()) { // A real route is missing -> request route via Brouter
                    Log::debug('Missing route found between ' . ($lastStopOver->properties->name ?? 'unknown') . ' and ' . ($data->properties->name ?? 'unknown'));
                    return true;
                }

                $lastStopOver = $data;
            }
        }
        return false;
    }
}
