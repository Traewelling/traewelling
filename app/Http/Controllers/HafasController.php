<?php

namespace App\Http\Controllers;

use App\Enum\HafasTravelType as HTT;
use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
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

    public static function getTrainStationByRilIdentifier(string $rilIdentifier): ?TrainStation {
        $trainStation = TrainStation::where('rilIdentifier', $rilIdentifier)->first();
        if ($trainStation !== null) {
            return $trainStation;
        }
        try {
            $response = self::getHttpClient()
                            ->get("/stations/$rilIdentifier");
            if (!$response->ok()) {
                return null;
            }
            $data = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            return TrainStation::updateOrCreate([
                                                    'ibnr' => $data->id
                                                ], [
                                                    'rilIdentifier' => $data->ril100,
                                                    'name'          => $data->name,
                                                    'latitude'      => $data->location->latitude,
                                                    'longitude'     => $data->location->longitude
                                                ]);
        } catch (Exception $exception) {
            report($exception);
        }
        return null;
    }

    public static function getTrainStationsByFuzzyRilIdentifier(string $rilIdentifier): ?Collection {
        $trainStations = TrainStation::where('rilIdentifier', 'LIKE', "$rilIdentifier%")->orderBy('rilIdentifier')->get();
        if ($trainStations->count() > 0) {
            return $trainStations;
        }
        return collect([self::getTrainStationByRilIdentifier(rilIdentifier: $rilIdentifier)]);
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
            if (empty($data) || !$response->ok()) {
                return Collection::empty();
            }

            return self::parseHafasStops($data);
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @param stdClass $hafasStop
     *
     * @return TrainStation
     * @throws PDOException
     */
    public static function parseHafasStopObject(stdClass $hafasStop): TrainStation {
        return TrainStation::updateOrCreate([
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
        return self::upsertTrainStations($payload);
    }

    private static function upsertTrainStations(array $payload) {
        $ibnrs = array_column($payload, 'ibnr');
        TrainStation::upsert($payload, ['ibnr'], ['name', 'latitude', 'longitude']);
        return TrainStation::whereIn('ibnr', $ibnrs)->get();
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
                throw new HafasException(__('messages.exception.generalHafas'));
            }

            $data     = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            $stations = self::parseHafasStops($data);

            foreach ($data as $hafasStation) {
                $station           = $stations->where('ibnr', $hafasStation->id)->first();
                $station->distance = $hafasStation->distance;
            }

            return $stations;
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    public static function fetchDepartures(
        TrainStation $station,
        Carbon       $when,
        int          $duration = 15,
        TravelType   $type = null,
        bool         $skipTimeShift = false
    ) {
        $client   = self::getHttpClient();
        $time   = $skipTimeShift ? $when : (clone $when)->shiftTimezone("Europe/Berlin");
        $query    = [
            'when'                       => $time->toIso8601String(),
            'duration'                   => $duration,
            HTT::NATIONAL_EXPRESS->value => self::checkTravelType($type, TravelType::EXPRESS),
            HTT::NATIONAL->value         => self::checkTravelType($type, TravelType::EXPRESS),
            HTT::REGIONAL_EXP->value     => self::checkTravelType($type, TravelType::REGIONAL),
            HTT::REGIONAL->value         => self::checkTravelType($type, TravelType::REGIONAL),
            HTT::SUBURBAN->value         => self::checkTravelType($type, TravelType::SUBURBAN),
            HTT::BUS->value              => self::checkTravelType($type, TravelType::BUS),
            HTT::FERRY->value            => self::checkTravelType($type, TravelType::FERRY),
            HTT::SUBWAY->value           => self::checkTravelType($type, TravelType::SUBWAY),
            HTT::TRAM->value             => self::checkTravelType($type, TravelType::TRAM),
            HTT::TAXI->value             => 'false',
        ];
        $response = $client->get('/stops/' . $station->ibnr . '/departures', $query);

        if (!$response->ok()) {
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        return json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    public static function checkTravelType(?TravelType $type, TravelType $travelType): string {
        return (is_null($type) || $type === $travelType) ? 'true' : 'false';
    }

    /**
     * @param TrainStation    $station
     * @param Carbon          $when
     * @param int             $duration
     * @param TravelType|null $type
     * @param bool            $localtime
     *
     * @return Collection
     * @throws HafasException
     */
    public static function getDepartures(
        TrainStation $station,
        Carbon       $when,
        int          $duration = 15,
        TravelType   $type = null,
        bool         $localtime = false
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
            $trainStationPayload = [];
            foreach ($data as $departure) {
                if (in_array($departure->stop->id, array_column($trainStationPayload, 'ibnr'), true)) {
                    continue;
                }
                $trainStationPayload[] = [
                    'ibnr'      => $departure->stop->id,
                    'name'      => $departure->stop->name,
                    'latitude'  => $departure->stop?->location?->latitude,
                    'longitude' => $departure->stop?->location?->longitude,
                ];
            }
            $trainStations = self::upsertTrainStations($trainStationPayload);

            //Then match the stations to the departures
            $departures = collect();
            foreach ($data as $departure) {
                $departure->station = $trainStations->where('ibnr', $departure->stop->id)->first();
                $departures->push($departure);
            }

            return $departures;
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * Get the TrainStopover Model from Database
     *
     * @param int         $ibnr
     * @param string|null $name
     * @param float|null  $latitude
     * @param float|null  $longitude
     *
     * @return TrainStation
     * @throws HafasException
     */
    public static function getTrainStation(
        int    $ibnr,
        string $name = null,
        float  $latitude = null,
        float  $longitude = null
    ): TrainStation {

        if ($name === null || $latitude === null || $longitude === null) {
            $dbTrainStation = TrainStation::where('ibnr', $ibnr)->first();
            return $dbTrainStation ?? self::fetchTrainStation($ibnr);
        }
        return TrainStation::updateOrCreate([
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
     * @return TrainStation
     * @throws HafasException
     */
    private static function fetchTrainStation(int $ibnr): TrainStation {
        $response = self::getHttpClient()->get("/stops/$ibnr");

        if (!$response->ok()) {
            throw new HafasException($response->reason());
        }

        $data = json_decode($response->body());
        return TrainStation::updateOrCreate([
                                                'ibnr' => $data->id
                                            ], [
                                                'name'      => $data->name,
                                                'latitude'  => $data->location->latitude,
                                                'longitude' => $data->location->longitude
                                            ]);

    }

    /**
     * @param string $tripID
     * @param string $lineName
     *
     * @return HafasTrip
     * @throws HafasException
     */
    public static function getHafasTrip(string $tripID, string $lineName): HafasTrip {
        $trip = HafasTrip::where('trip_id', $tripID)->where('linename', $lineName)->first();
        return $trip ?? self::fetchHafasTrip($tripID, $lineName);
    }

    /**
     * @throws HafasException|JsonException
     */
    public static function fetchRawHafasTrip(string $tripId, string $lineName) {
        $tripResponse = self::getHttpClient()->get("trips/$tripId", [
            'lineName'  => $lineName,
            'polyline'  => 'true',
            'stopovers' => 'true'
        ]);

        if ($tripResponse->ok()) {
            return json_decode($tripResponse->body(), false, 512, JSON_THROW_ON_ERROR);
        }
        //sometimes HAFAS returnes 502 Bad Gateway
        if ($tripResponse->status() === 502) {
            throw new HafasException(__('messages.exception.hafas.502'));
        }
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
     * @return HafasTrip
     * @throws HafasException
     */
    public static function fetchHafasTrip(string $tripID, string $lineName): HafasTrip {
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

        $hafasTrip = HafasTrip::updateOrCreate([
                                                   'trip_id' => $tripID
                                               ], [
                                                   'category'       => $tripJson->line->product,
                                                   'number'         => $tripJson->line->id,
                                                   'linename'       => $tripJson->line->name,
                                                   'journey_number' => $tripJson->line?->fahrtNr === "0" ? null : $tripJson->line?->fahrtNr,
                                                   'operator_id'    => $operator?->id,
                                                   'origin'         => $origin->ibnr,
                                                   'destination'    => $destination->ibnr,
                                                   'polyline_id'    => $polyline->id,
                                                   'departure'      => $tripJson->plannedDeparture,
                                                   'arrival'        => $tripJson->plannedArrival,
                                                   'delay'          => $tripJson->arrivalDelay ?? null
                                               ]);

        //Save TrainStations
        $payload = [];
        foreach ($tripJson->stopovers as $stopover) {
            $payload[] = [
                'ibnr'      => $stopover->stop->id,
                'name'      => $stopover->stop->name,
                'latitude'  => $stopover->stop->location?->latitude,
                'longitude' => $stopover->stop->location?->longitude,
            ];
        }
        $trainStations = self::upsertTrainStations($payload);

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

                TrainStopover::updateOrCreate(
                    [
                        'trip_id'           => $tripID,
                        'train_station_id'  => $trainStations->where('ibnr', $stopover->stop->id)->first()->id,
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
        return $hafasTrip;
    }

    public static function refreshStopovers(stdClass $rawHafas): int {
        $payload = [];
        foreach ($rawHafas->stopovers ?? [] as $stopover) {
            if (!isset($stopover->arrivalDelay) && !isset($stopover->departureDelay)) {
                continue;
            }

            $stop             = self::parseHafasStopObject($stopover->stop);
            $arrivalPlanned   = Carbon::parse($stopover->plannedArrival)->tz(config('app.timezone'));
            $arrivalReal      = Carbon::parse($stopover->arrival)->tz(config('app.timezone'));
            $departurePlanned = Carbon::parse($stopover->plannedDeparture)->tz(config('app.timezone'));
            $departureReal    = Carbon::parse($stopover->departure)->tz(config('app.timezone'));

            $payload[] = [
                'trip_id'           => $rawHafas->id,
                'train_station_id'  => $stop->id,
                'arrival_planned'   => isset($stopover->plannedArrival) ? $arrivalPlanned : $departurePlanned,
                'arrival_real'      => isset($stopover->arrival) ? $arrivalReal : null,
                'departure_planned' => isset($stopover->plannedDeparture) ? $departurePlanned : $arrivalPlanned,
                'departure_real'    => isset($stopover->departure) ? $departureReal : null,
            ];
        }

        return TrainStopover::upsert(
            $payload,
            ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'],
            ['arrival_real', 'departure_real']
        );
    }

    /**
     * This function is used to refresh the departure of a trip, if the planned_departure is in the past and no
     * real-time data is given. The HAFAS stationboard gives us this real-time data even for trips in the past, so give
     * it a chance.
     *
     * This function should be called in an async job, if not needed instantly.
     *
     * @param TrainStopover $stopover
     *
     * @return void
     * @throws HafasException
     */
    public static function refreshStopover(TrainStopover $stopover): void {
        $departure = self::getDepartures(
            station: $stopover->trainStation,
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
