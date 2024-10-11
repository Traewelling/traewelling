<?php

namespace App\Http\Controllers\Backend;

use App\Dto\Coordinate;
use App\Enum\BrouterProfile;
use App\Exceptions\DistanceDeviationException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Controller;
use App\Jobs\RefreshPolyline;
use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\Trip;
use App\Objects\LineSegment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use stdClass;

abstract class BrouterController extends Controller
{
    private static function getHttpClient(): PendingRequest {
        return Http::baseUrl(config('trwl.brouter_url'))
                   ->timeout(config('trwl.brouter_timeout'));
    }

    /**
     * @param array          $coordinates Array of App\Dto\Coordinate objects
     * @param BrouterProfile $profile
     *
     * @return stdClass
     * @throws JsonException|InvalidArgumentException|ConnectionException
     */
    private static function getGeoJSONForRoute(
        array          $coordinates,
        BrouterProfile $profile = BrouterProfile::RAIL //Maybe extend this for other travel types later
    ): stdClass {
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
        Log::debug('[RefreshPolyline] Brouter URL is ' . $response->effectiveUri());
        if (!$response->ok()) {
            Log::debug('[RefreshPolyline] Brouter response was not okay.', ['body' => $response->body()]);
            throw new InvalidArgumentException('Brouter response was not okay.');
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
     * 1. Fetch route for all stations of a trip at Brouter
     * 2. split the route by stations (we need the GeoJSON split!)
     * 3. Create features for every station and route between
     *
     * @param Trip $trip
     *
     * @return void
     * @throws JsonException
     */
    public static function reroutePolyline(Trip $trip): void {
        if (App::runningUnitTests() || !config('trwl.brouter')) {
            return;
        }

        //0. Check if brouter Polyline is already available
        $childPolyline   = PolyLine::where('parent_id', $trip->polyline_id)->orderBy('id', 'desc')->first();
        $currentPolyline = $trip->polyline()->first();
        if ($childPolyline?->source === 'brouter' || $currentPolyline?->source === 'brouter') {
            Log::debug('[RefreshPolyline] Brouter Polyline already available for Trip#' . $trip->trip_id);

            if ($currentPolyline?->source !== 'brouter') {
                //If the current Polyline is not from Brouter, we need to recalculate the distance and points
                self::recalculateDistanceAndPoints($trip, $childPolyline);
            }
            return;
        }

        //1. Prepare coordinates from stations
        $coordinates = [];
        foreach ($trip->stopovers as $stopover) {
            $coordinates[] = new Coordinate($stopover->station->latitude, $stopover->station->longitude);
        }

        try {
            //2. Request route at brouter
            $brouterGeoJSON = self::getGeoJSONForRoute($coordinates);
        } catch (InvalidArgumentException) {
            Log::warning('[RefreshPolyline] Error while getting Polyline for Trip#' . $trip->trip_id . ' (Required data is missing in Brouter response)');
            return;
        } catch (ConnectionException) {
            Log::info('[RefreshPolyline] Getting Polyline for Trip#' . $trip->trip_id . ' timed out.');
            return;
        }
        //3. Create "new" GeoJSON split by stations (as features)
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
        foreach ($trip->stopovers as $stopover) {
            $properties = [
                'id'   => $stopover->station->ibnr,
                'name' => $stopover->station->name,
            ];

            //Get feature with the lowest distance to station
            $minDistance       = null;
            $closestFeatureKey = null;
            foreach ($geoJson['features'] as $key => $feature) {
                if (($highestMappedKey !== null && $key <= $highestMappedKey) || !isset($feature['geometry']['coordinates'])) {
                    //Don't look again at the same stations.
                    //This is required and very important to prevent bugs for ring lines!
                    continue;
                }
                $distance = (new LineSegment(
                    new Coordinate($feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0]),
                    new Coordinate($stopover->station->latitude, $stopover->station->longitude)
                ))->calculateDistance();

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance       = $distance;
                    $closestFeatureKey = $key;
                }
            }
            $highestMappedKey                                      = $closestFeatureKey;
            $geoJson['features'][$closestFeatureKey]['properties'] = $properties;
        }

        $childPolyline = PolyLine::create([
                                              'hash'      => Str::uuid(), //In this case a non required unique key
                                              'polyline'  => json_encode($geoJson),
                                              'source'    => 'brouter',
                                              'parent_id' => $trip->polyline_id
                                          ]);
        self::recalculateDistanceAndPoints($trip, $childPolyline);
    }

    /**
     * @param Trip     $trip
     * @param          $polyline
     *
     * @return void
     */
    private static function recalculateDistanceAndPoints(Trip $trip, $polyline): void {
        DB::beginTransaction();
        $oldPolyLine = self::getOldPolyline($trip);
        Log::debug('[RefreshPolyline] Recalculating distance and points for Trip#' . $trip->trip_id);
        Log::debug('[RefreshPolyline] New Polyline ID: ' . $trip->polyline_id);
        Log::debug('[RefreshPolyline] Old Polyline ID: ' . $oldPolyLine);
        $trip->update(['polyline_id' => $polyline->id]);

        //Refresh distance and points of trips
        $checkinsToRecalc = Checkin::with(['status'])->where('trip_id', $trip->trip_id)->get();
        try {
            foreach ($checkinsToRecalc as $checkin) {
                TrainCheckinController::refreshDistanceAndPoints($checkin->status);
            }
            DB::commit();
        } catch (DistanceDeviationException) {
            $trip->update(['polyline_id' => $oldPolyLine]);
            Log::debug('[RefreshPolyline] Distance Deviation detected. Reverting changes.');
            DB::rollBack();
        }
    }

    /**
     * Check if Polyline has missing parts. If yes: Automatically schedule a job to get a real route via brouter
     *
     * @param Trip $trip
     *
     * @return void
     */
    public static function checkPolyline(Trip $trip): void {
        if (!$trip->category?->onRails()) {
            return;
        }

        if (!self::checkIfPolylineHasMissingParts($trip)) {
            Log::debug('no parts missing');
            //Nothing to do here.
            return;
        }
        Log::debug('parts missing: dispatch');
        RefreshPolyline::dispatch($trip);
    }

    private static function checkIfPolylineHasMissingParts(Trip $trip): bool {
        if (is_null($trip->polyline)) {
            Log::debug('Missing route found. No polyline available.');
            return true;
        }
        $geoJson      = json_decode($trip->polyline->polyline);
        $features     = $geoJson->features;
        $lastStopOver = null; // To detect whether as the crow flies or real routing
        foreach ($features as $data) {
            if (!isset($data->properties->id)) {
                $lastStopOver = null;
            } else {
                if (!is_null($lastStopOver) && $trip->category?->onRails()) { // A real route is missing -> request route via Brouter
                    Log::debug('Missing route found between ' . ($lastStopOver->properties->name ?? 'unknown') . ' and ' . ($data->properties->name ?? 'unknown'));
                    return true;
                }

                $lastStopOver = $data;
            }
        }
        return false;
    }

    private static function getOldPolyline(Trip $trip): ?int {
        $oldPolyLine = PolyLine::where('parent_id', $trip->polyline_id)->orderBy('id', 'desc')->first();
        $limit       = 20;
        while ($oldPolyLine?->source === 'brouter' && $limit-- > 0) {
            $next = PolyLine::where('parent_id', $oldPolyLine->id)->orderBy('id', 'desc')->first();
            if ($next) {
                $oldPolyLine = $next;
            } else {
                break;
            }
        }

        return $oldPolyLine->id ?? null;
    }
}
