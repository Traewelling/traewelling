<?php

namespace App\Http\Controllers;

use App\Enum\HafasTravelType as HTT;
use App\Enum\TravelType;
use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use PDOException;
use stdClass;

abstract class HafasController extends Controller
{
    public static function getHttpClient(): PendingRequest {
        return Http::baseUrl(config('trwl.db_rest'))
                   ->timeout(config('trwl.db_rest_timeout'));
    }

    public static function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }
        try {
            $response = self::getHttpClient()
                            ->get("/stations/$rilIdentifier");
            if (!$response->ok()) {
                CacheKey::increment(HCK::STATIONS_NOT_OK);
                return null;
            }
            $data = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            CacheKey::increment(HCK::STATIONS_SUCCESS);
            return Station::updateOrCreate([
                                               'ibnr' => $data->id
                                           ], [
                                               'rilIdentifier' => $data->ril100,
                                               'name'          => $data->name,
                                               'latitude'      => $data->location->latitude,
                                               'longitude'     => $data->location->longitude
                                           ]);
        } catch (Exception $exception) {
            CacheKey::increment(HCK::STATIONS_FAILURE);
            report($exception);
        }
        return null;
    }

    public static function getStationsByFuzzyRilIdentifier(string $rilIdentifier): ?Collection {
        $stations = Station::where('rilIdentifier', 'LIKE', "$rilIdentifier%")->orderBy('rilIdentifier')->get();
        if ($stations->count() > 0) {
            return $stations;
        }
        return collect([self::getStationByRilIdentifier(rilIdentifier: $rilIdentifier)]);
    }

    /**
     * @throws HafasException
     */
    public static function getStations(string $query, int $results = 10): Collection {
        try {
            $response = self::getHttpClient()
                            ->get("/locations",
                                  [
                                      'query'     => $query,
                                      'fuzzy'     => 'true',
                                      'stops'     => 'true',
                                      'addresses' => 'false',
                                      'poi'       => 'false',
                                      'results'   => $results
                                  ]);

            $data = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            if (!$response->ok()) {
                CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            }

            if (empty($data) || !$response->ok()) {
                return Collection::empty();
            }

            CacheKey::increment(HCK::LOCATIONS_SUCCESS);
            return self::parseHafasStops($data);
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        } catch (Exception $exception) {
            CacheKey::increment(HCK::LOCATIONS_FAILURE);
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @param stdClass $hafasStop
     *
     * @return Station
     * @throws PDOException
     */
    public static function parseHafasStopObject(stdClass $hafasStop): Station {
        return Station::updateOrCreate([
                                           'ibnr' => $hafasStop->id
                                       ], [
                                           'name'      => $hafasStop->name,
                                           'latitude'  => $hafasStop->location?->latitude,
                                           'longitude' => $hafasStop->location?->longitude,
                                       ]);
    }

    private static function parseHafasStops(array $hafasResponse): Collection {
        $payload = [];
        foreach ($hafasResponse as $hafasStation) {
            $payload[] = [
                'ibnr'      => $hafasStation->id,
                'name'      => $hafasStation->name,
                'latitude'  => $hafasStation?->location?->latitude,
                'longitude' => $hafasStation?->location?->longitude,
            ];
        }
        return self::upsertStations($payload);
    }

    private static function upsertStations(array $payload) {
        $ibnrs = array_column($payload, 'ibnr');
        if (empty($ibnrs)) {
            return new Collection();
        }
        Station::upsert($payload, ['ibnr'], ['name', 'latitude', 'longitude']);
        return Station::whereIn('ibnr', $ibnrs)->get()
                      ->sortBy(function(Station $station) use ($ibnrs) {
                          return array_search($station->ibnr, $ibnrs);
                      })
                      ->values();
    }

    /**
     * @throws HafasException
     */
    public static function getNearbyStations(float $latitude, float $longitude, int $results = 8): Collection {
        try {
            $response = self::getHttpClient()->get("/stops/nearby", [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'results'   => $results
            ]);

            if (!$response->ok()) {
                CacheKey::increment(HCK::NEARBY_NOT_OK);
                throw new HafasException(__('messages.exception.generalHafas'));
            }

            $data     = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            $stations = self::parseHafasStops($data);

            foreach ($data as $hafasStation) {
                $station           = $stations->where('ibnr', $hafasStation->id)->first();
                $station->distance = $hafasStation->distance;
            }

            CacheKey::increment(HCK::NEARBY_SUCCESS);
            return $stations;
        } catch (JsonException $exception) {
            CacheKey::increment(HCK::NEARBY_FAILURE);
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    public static function fetchDepartures(
        Station    $station,
        Carbon     $when,
        int        $duration = 15,
        TravelType $type = null,
        bool       $skipTimeShift = false
    ) {
        $client = self::getHttpClient();
        $time   = $skipTimeShift ? $when : (clone $when)->shiftTimezone("Europe/Berlin");
        $query  = [
            'when'                       => $time->toIso8601String(),
            'duration'                   => $duration,
            HTT::NATIONAL_EXPRESS->value => self::checkTravelType($type, TravelType::EXPRESS),
            HTT::NATIONAL->value         => self::checkTravelType($type, TravelType::EXPRESS),
            HTT::REGIONAL_EXP->value     => self::checkTravelType($type, TravelType::EXPRESS),
            HTT::REGIONAL->value         => self::checkTravelType($type, TravelType::REGIONAL),
            HTT::SUBURBAN->value         => self::checkTravelType($type, TravelType::SUBURBAN),
            HTT::BUS->value              => self::checkTravelType($type, TravelType::BUS),
            HTT::FERRY->value            => self::checkTravelType($type, TravelType::FERRY),
            HTT::SUBWAY->value           => self::checkTravelType($type, TravelType::SUBWAY),
            HTT::TRAM->value             => self::checkTravelType($type, TravelType::TRAM),
            HTT::TAXI->value             => self::checkTravelType($type, TravelType::TAXI),
        ];
        try {
            $response = $client->get('/stops/' . $station->ibnr . '/departures', $query);
        } catch (Exception $exception) {
            CacheKey::increment(HCK::DEPARTURES_FAILURE);
            throw new HafasException($exception->getMessage());
        }

        if (!$response->ok()) {
            CacheKey::increment(HCK::DEPARTURES_NOT_OK);
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        CacheKey::increment(HCK::DEPARTURES_SUCCESS);
        return json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    public static function checkTravelType(?TravelType $type, TravelType $travelType): string {
        return (is_null($type) || $type === $travelType) ? 'true' : 'false';
    }

    /**
     * @param Station         $station
     * @param Carbon          $when
     * @param int             $duration
     * @param TravelType|null $type
     * @param bool            $localtime
     *
     * @return Collection
     * @throws HafasException
     */
    public static function getDepartures(
        Station    $station,
        Carbon     $when,
        int        $duration = 15,
        TravelType $type = null,
        bool       $localtime = false
    ): Collection {
        try {
            $requestTime = is_null($station->time_offset) || $localtime
                ? $when : (clone $when)->subHours($station->time_offset);
            $data        = self::fetchDepartures(
                $station,
                $requestTime,
                $duration,
                $type,
                !$station->shift_time && !$localtime
            );
            if (!$localtime) {
                foreach ($data as $departure) {
                    if ($departure?->when) {
                        $time     = Carbon::parse($departure->when);
                        $timezone = $time->tz->toOffsetName();

                        // check for an offset between results
                        $offset = $time->tz('UTC')->hour - $when->tz('UTC')->hour;
                        if ($offset !== 0) {
                            // Check if the timezone for this station is equal in its offset to Europe/Berlin.
                            // If so, fetch again **without** adjusting the timezone
                            if ($timezone === CarbonTimeZone::create("Europe/Berlin")->toOffsetName()) {
                                $data = self::fetchDepartures($station, $when, $duration, $type, true);

                                $station->shift_time = false;
                                $station->save();
                                break;
                            }
                            // if the timezone is not equal to Europe/Berlin, fetch the offset
                            $data = self::fetchDepartures($station, (clone $when)->subHours($offset), $duration, $type);

                            $station->time_offset = $offset;
                            $station->save();
                        }
                        break;
                    }
                }
            }

            //First fetch all stations in one request
            $stationPayload = [];
            foreach ($data as $departure) {
                if (in_array($departure->stop->id, array_column($stationPayload, 'ibnr'), true)) {
                    continue;
                }
                $stationPayload[] = [
                    'ibnr'      => $departure->stop->id,
                    'name'      => $departure->stop->name,
                    'latitude'  => $departure->stop?->location?->latitude,
                    'longitude' => $departure->stop?->location?->longitude,
                ];
            }
            $stations = self::upsertStations($stationPayload);

            //Then match the stations to the departures
            $departures = collect();
            foreach ($data as $departure) {
                $departure->station = $stations->where('ibnr', $departure->stop->id)->first();
                $departures->push($departure);
            }

            return $departures;
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * Get the Stopover Model from Database
     *
     * @param int         $ibnr
     * @param string|null $name
     * @param float|null  $latitude
     * @param float|null  $longitude
     *
     * @return Station
     * @throws HafasException
     */
    public static function getStation(
        int    $ibnr,
        string $name = null,
        float  $latitude = null,
        float  $longitude = null
    ): Station {

        if ($name === null || $latitude === null || $longitude === null) {
            $dbStation = Station::where('ibnr', $ibnr)->first();
            return $dbStation ?? self::fetchStation($ibnr);
        }
        return Station::updateOrCreate([
                                           'ibnr' => $ibnr
                                       ], [
                                           'name'      => $name,
                                           'latitude'  => $latitude,
                                           'longitude' => $longitude
                                       ]);
    }

    /**
     * Fetch from HAFAS
     *
     * @param int $ibnr
     *
     * @return Station
     * @throws HafasException
     */
    private static function fetchStation(int $ibnr): Station {
        try {
            $response = self::getHttpClient()->get("/stops/$ibnr");
        } catch (Exception $exception) {
            CacheKey::increment(HCK::STOPS_FAILURE);
            throw new HafasException($exception->getMessage());
        }

        if (!$response->ok()) {
            CacheKey::increment(HCK::STOPS_NOT_OK);
            throw new HafasException($response->reason());
        }

        $data = json_decode($response->body());
        CacheKey::increment(HCK::STOPS_SUCCESS);
        return Station::updateOrCreate([
                                           'ibnr' => $data->id
                                       ], [
                                           'name'      => $data->name,
                                           'latitude'  => $data->location->latitude,
                                           'longitude' => $data->location->longitude
                                       ]);

    }

    /**
     * @throws HafasException|JsonException
     */
    public static function fetchRawHafasTrip(string $tripId, string $lineName) {
        try {
            $tripResponse = self::getHttpClient()->get("trips/" . rawurlencode($tripId), [
                'lineName'  => $lineName,
                'polyline'  => 'true',
                'stopovers' => 'true'
            ]);
        } catch (Exception $exception) {
            CacheKey::increment(HCK::TRIPS_FAILURE);
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        if ($tripResponse->ok()) {
            CacheKey::increment(HCK::TRIPS_SUCCESS);
            return json_decode($tripResponse->body(), false, 512, JSON_THROW_ON_ERROR);
        }
        //sometimes HAFAS returnes 502 Bad Gateway
        if ($tripResponse->status() === 502) {
            CacheKey::increment(HCK::TRIPS_502);
            Log::error('Cannot fetch trip with id: ' . $tripId);
            throw new HafasException(__('messages.exception.hafas.502'));
        }
        CacheKey::increment(HCK::TRIPS_NOT_OK);
        Log::error('Unknown HAFAS Error (fetchRawHafasTrip)', [
            'status' => $tripResponse->status(),
            'body'   => $tripResponse->body()
        ]);
        throw new HafasException(__('messages.exception.generalHafas'));
    }

    /**
     * @param string $tripID
     * @param string $lineName
     *
     * @return Trip
     * @throws HafasException|JsonException
     */
    public static function fetchHafasTrip(string $tripID, string $lineName): Trip {
        $tripJson    = self::fetchRawHafasTrip($tripID, $lineName);
        $origin      = self::parseHafasStopObject($tripJson->origin);
        $destination = self::parseHafasStopObject($tripJson->destination);
        $operator    = null;

        if (isset($tripJson->line->operator->id)) {
            $operator = HafasOperator::updateOrCreate([
                                                          'hafas_id' => $tripJson->line->operator->id,
                                                      ], [
                                                          'name' => $tripJson->line->operator->name,
                                                      ]);
        }

        if ($tripJson->line->name === null) {
            $tripJson->line->name = $tripJson->line->fahrtNr;
        }

        if ($tripJson->line->id === null) {
            $tripJson->line->id = '';
        }

        $polyline = TransportController::getPolylineHash(json_encode($tripJson->polyline));

        $trip = Trip::updateOrCreate([
                                         'trip_id' => $tripID
                                     ], [
                                         'category'       => $tripJson->line->product,
                                         'number'         => $tripJson->line->id,
                                         'linename'       => $tripJson->line->name,
                                         'journey_number' => $tripJson->line?->fahrtNr === "0" ? null : $tripJson->line?->fahrtNr,
                                         'operator_id'    => $operator?->id,
                                         'origin_id'      => $origin->id,
                                         'destination_id' => $destination->id,
                                         'polyline_id'    => $polyline->id,
                                         'departure'      => $tripJson->plannedDeparture,
                                         'arrival'        => $tripJson->plannedArrival,
                                         'delay'          => $tripJson->arrivalDelay ?? null,
                                         'source'         => TripSource::HAFAS,
                                     ]);

        //Save Stations
        $payload = [];
        foreach ($tripJson->stopovers as $stopover) {
            $payload[] = [
                'ibnr'      => $stopover->stop->id,
                'name'      => $stopover->stop->name,
                'latitude'  => $stopover->stop->location?->latitude,
                'longitude' => $stopover->stop->location?->longitude,
            ];
        }
        $stations = self::upsertStations($payload);

        foreach ($tripJson->stopovers as $stopover) {
            //TODO: make this better 🤯

            //This array is a workaround because Hafas doesn't give
            //us delay-data if the train already passed this station
            //so... just save data we really got. :)
            $updatePayload = [
                'arrival_platform_planned'   => $stopover->plannedArrivalPlatform,
                'departure_platform_planned' => $stopover->plannedDeparturePlatform,
                'cancelled'                  => $stopover?->cancelled ?? false,
            ];
            //remove "null" values
            $updatePayload = array_filter($updatePayload, 'strlen'); //TODO: This is deprecated, find a better way

            //the arrival and departure attributes are always included, so to recognize whether we have realtime data,
            // arrivalDelay and departureDelay are checked for being null or not.
            if ($stopover->arrival !== null && isset($stopover->arrivalDelay)) {
                $updatePayload['arrival_real'] = Carbon::parse($stopover->arrival);
                if ($stopover->arrivalPlatform !== null) {
                    $updatePayload['arrival_platform_real'] = $stopover->arrivalPlatform;
                }
            }
            if ($stopover->departure !== null && isset($stopover->departureDelay)) {
                $updatePayload['departure_real'] = Carbon::parse($stopover->departure);
                if ($stopover->departurePlatform !== null) {
                    $updatePayload['departure_platform_real'] = $stopover->departurePlatform;
                }
            }
            try {
                //If there is no arrival set, we need to set the departure as arrival and vice versa
                // -> this is for checkins for trips were no entry/exit is planned.
                $plannedArrival   = Carbon::parse($stopover->plannedArrival);
                $plannedDeparture = Carbon::parse($stopover->plannedDeparture);

                Stopover::updateOrCreate(
                    [
                        'trip_id'           => $tripID,
                        'train_station_id'  => $stations->where('ibnr', $stopover->stop->id)->first()->id,
                        'arrival_planned'   => isset($stopover->plannedArrival) ? $plannedArrival : $plannedDeparture,
                        'departure_planned' => isset($stopover->plannedDeparture) ? $plannedDeparture : $plannedArrival,
                    ],
                    $updatePayload
                );
            } catch (PDOException) {
                //do nothing: updateOrCreate will handle duplicate keys, but if the database is a bit laggy
                // it can be thrown an error here. But that's not a big deal.
            }
        }
        return $trip;
    }

    public static function refreshStopovers(stdClass $rawHafas): stdClass {
        $stopoversUpdated = 0;
        $payloadArrival   = [];
        $payloadDeparture = [];
        $payloadCancelled = [];
        foreach ($rawHafas->stopovers ?? [] as $stopover) {
            if (!isset($stopover->arrivalDelay) && !isset($stopover->departureDelay) && !isset($stopover->cancelled)) {
                continue; // No realtime data present for this stopover, keep existing data
            }

            $stop             = self::parseHafasStopObject($stopover->stop);
            $arrivalPlanned   = Carbon::parse($stopover->plannedArrival)->tz(config('app.timezone'));
            $departurePlanned = Carbon::parse($stopover->plannedDeparture)->tz(config('app.timezone'));

            $basePayload = [
                'trip_id'           => $rawHafas->id,
                'train_station_id'  => $stop->id,
                'arrival_planned'   => isset($stopover->plannedArrival) ? $arrivalPlanned : $departurePlanned,
                'departure_planned' => isset($stopover->plannedDeparture) ? $departurePlanned : $arrivalPlanned,
            ];

            if (isset($stopover->arrivalDelay) && isset($stopover->arrival)) {
                $arrivalReal      = Carbon::parse($stopover->arrival)->tz(config('app.timezone'));
                $payloadArrival[] = array_merge($basePayload, ['arrival_real' => $arrivalReal]);
            }

            if (isset($stopover->departureDelay) && isset($stopover->departure)) {
                $departureReal      = Carbon::parse($stopover->departure)->tz(config('app.timezone'));
                $payloadDeparture[] = array_merge($basePayload, ['departure_real' => $departureReal]);
            }

            // In case of cancellation, arrivalDelay/departureDelay will be null while the cancelled attribute will be present and true
            // If cancelled is false / missing while other RT data is present (see initial if expression), it will be upserted to false
            // This behavior is required for potential withdrawn cancellations
            $payloadCancelled[] = array_merge($basePayload, ['cancelled' => $stopover->cancelled ?? false]);

            $stopoversUpdated++;
        }

        $key = ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'];

        return (object) [
            "stopovers" => $stopoversUpdated,
            "rows"      => [
                "arrival"   => Stopover::upsert($payloadArrival, $key, ['arrival_real']),
                "departure" => Stopover::upsert($payloadDeparture, $key, ['departure_real']),
                "cancelled" => Stopover::upsert($payloadCancelled, $key, ['cancelled'])
            ]
        ];
    }

    /**
     * This function is used to refresh the departure of a trip, if the planned_departure is in the past and no
     * real-time data is given. The HAFAS stationboard gives us this real-time data even for trips in the past, so give
     * it a chance.
     *
     * This function should be called in an async job, if not needed instantly.
     *
     * @param Stopover $stopover
     *
     * @return void
     * @throws HafasException
     */
    public static function refreshStopover(Stopover $stopover): void {
        $departure = self::getDepartures(
            station: $stopover->station,
            when:    $stopover->departure_planned,
        )->filter(function(stdClass $trip) use ($stopover) {
            return $trip->tripId === $stopover->trip_id;
        })->first();

        if ($departure === null || $departure->when === null || $departure->plannedWhen === $departure->when) {
            return; //do nothing, if the trip isn't found.
        }

        $stopover->update([
                              'departure_real' => Carbon::parse($departure->when),
                          ]);
    }
}
