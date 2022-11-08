<?php

namespace App\Http\Controllers;

use App\Enum\HafasTravelType as HTT;
use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\Remark;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;
use PDOException;
use stdClass;

abstract class HafasController extends Controller
{

    public static function getTrainStationByRilIdentifier(string $rilIdentifier): ?TrainStation {
        $trainStation = TrainStation::where('rilIdentifier', $rilIdentifier)->first();
        if ($trainStation !== null) {
            return $trainStation;
        }
        try {
            $client   = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
            $response = $client->get("/stations/$rilIdentifier");
            $data     = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            return TrainStation::updateOrCreate([
                                                    'ibnr' => $data->id
                                                ], [
                                                    'rilIdentifier' => $data->ril100,
                                                    'name'          => $data->name,
                                                    'latitude'      => $data->location->latitude,
                                                    'longitude'     => $data->location->longitude
                                                ]);
        } catch (ClientException $exception) {
            if ($exception->getCode() !== 404) {
                report($exception);
            }
        } catch (Exception $exception) {
            report($exception);
        }
        return null;
    }

    public static function getTrainStationsByRilIdentifier(string $rilIdentifier): ?Collection {
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
            $client   = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
            $response = $client->get("/locations", [
                'query' => [
                    'query'     => $query,
                    'fuzzy'     => 'true',
                    'stops'     => 'true',
                    'addresses' => 'false',
                    'poi'       => 'false',
                    'results'   => $results
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            return self::parseHafasStops($data);
        } catch (GuzzleException|JsonException $exception) {
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
                                                'latitude'  => $hafasStop?->location?->latitude,
                                                'longitude' => $hafasStop?->location?->longitude,
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
            $client   = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
            $response = $client->get("/stops/nearby", [
                'query' => [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                    'results'   => $results
                ]
            ]);

            $data     = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            $stations = self::parseHafasStops($data);

            foreach ($data as $hafasStation) {
                $station           = $stations->where('ibnr', $hafasStation->id)->first();
                $station->distance = $hafasStation->distance;
            }

            return $stations;
        } catch (GuzzleException|JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @param TrainStation    $station
     * @param Carbon          $when
     * @param int             $duration
     * @param TravelType|null $type
     *
     * @return Collection
     * @throws HafasException
     */
    public static function getDepartures(
        TrainStation $station,
        Carbon       $when,
        int          $duration = 15,
        TravelType   $type = null
    ): Collection {
        try {
            $client   = new Client([
                                       'base_uri' => config('trwl.db_rest'),
                                       'timeout'  => config('trwl.db_rest_timeout'),
                                   ]);
            $query    = [
                'when'                       => $when->toIso8601String(),
                'duration'                   => $duration,
                HTT::NATIONAL_EXPRESS->value => (is_null($type) || $type === TravelType::EXPRESS) ? 'true' : 'false',
                HTT::NATIONAL->value         => (is_null($type) || $type === TravelType::EXPRESS) ? 'true' : 'false',
                HTT::REGIONAL_EXP->value     => (is_null($type) || $type === TravelType::REGIONAL) ? 'true' : 'false',
                HTT::REGIONAL->value         => (is_null($type) || $type === TravelType::REGIONAL) ? 'true' : 'false',
                HTT::SUBURBAN->value         => (is_null($type) || $type === TravelType::SUBURBAN) ? 'true' : 'false',
                HTT::BUS->value              => (is_null($type) || $type === TravelType::BUS) ? 'true' : 'false',
                HTT::FERRY->value            => (is_null($type) || $type === TravelType::FERRY) ? 'true' : 'false',
                HTT::SUBWAY->value           => (is_null($type) || $type === TravelType::SUBWAY) ? 'true' : 'false',
                HTT::TRAM->value             => (is_null($type) || $type === TravelType::TRAM) ? 'true' : 'false',
                HTT::TAXI->value             => 'false',
            ];
            $response = $client->get('/stops/' . $station->ibnr . '/departures', [
                'query' => $query,
            ]);

            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

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
        } catch (GuzzleException|JsonException $exception) {
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
            if ($dbTrainStation !== null) {
                return $dbTrainStation;
            }
            return HafasController::fetchTrainStation($ibnr);
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
        try {
            $client   = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
            $response = $client->get("/stops/$ibnr");
            $data     = json_decode($response->getBody()->getContents());
            return TrainStation::updateOrCreate([
                                                    'ibnr' => $data->id
                                                ], [
                                                    'name'      => $data->name,
                                                    'latitude'  => $data->location->latitude,
                                                    'longitude' => $data->location->longitude
                                                ]);
        } catch (GuzzleException $e) {
            throw new HafasException($e->getMessage());
        }
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
     * @throws HafasException
     */
    public static function fetchRawHafasTrip(string $tripId, string $lineName) {
        $tripClient = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
        try {
            $tripResponse = $tripClient->get("trips/$tripId", [
                'query' => [
                    'lineName'  => $lineName,
                    'polyline'  => 'true',
                    'stopovers' => 'true'
                ]
            ]);
            return json_decode($tripResponse->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException|JsonException) {
            //sometimes DB-Rest gives 502 Bad Request
        }
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
                                                   'category'    => $tripJson->line->product,
                                                   'number'      => $tripJson->line->id,
                                                   'linename'    => $tripJson->line->name,
                                                   'operator_id' => $operator?->id,
                                                   'origin'      => $origin->ibnr,
                                                   'destination' => $destination->ibnr,
                                                   'stopovers'   => json_encode($tripJson->stopovers),
                                                   'polyline_id' => $polyline->id,
                                                   'departure'   => $tripJson->plannedDeparture,
                                                   'arrival'     => $tripJson->plannedArrival,
                                                   'delay'       => $tripJson->arrivalDelay ?? null
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
            //so.. just save data we really got. :)
            $updatePayload = [
                'arrival_platform_planned'   => $stopover->plannedArrivalPlatform,
                'departure_platform_planned' => $stopover->plannedDeparturePlatform,
                'cancelled'                  => $stopover?->cancelled ?? false,
            ];
            //remove "null" values
            $updatePayload = array_filter($updatePayload, 'strlen'); //TODO: This is deprecated, find a better way

            if ($stopover->arrival !== null && Carbon::parse($stopover->arrival)->isFuture()) {
                $updatePayload['arrival_real'] = Carbon::parse($stopover->arrival);
                if ($stopover->arrivalPlatform !== null) {
                    $updatePayload['arrival_platform_real'] = $stopover->arrivalPlatform;
                }
            }
            if ($stopover->departure !== null && Carbon::parse($stopover->departure)->isFuture()) {
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
                        'arrival_planned'   => isset($stopover->plannedArrival) ? $plannedArrival?->toDateTimeString() : $plannedDeparture?->toDateTimeString(),
                        'departure_planned' => isset($stopover->plannedDeparture) ? $plannedDeparture?->toDateTimeString() : $plannedArrival?->toDateTimeString(),
                    ],
                    $updatePayload
                );
            } catch (PDOException) {
                //do nothing: updateOrCreate will handle duplicate keys, but if the database is a bit laggy
                // it can be throw an error here. But thats not a big deal.
            }
        }
        try {
            self::saveRemarks($tripJson?->remarks ?? [], $hafasTrip);
        } catch (PDOException) {
            // do nothing (not important)
        }
        return $hafasTrip;
    }

    private static function saveRemarks(iterable $remarks, HafasTrip $hafasTrip): void {
        $remarkObjects = [];
        foreach ($remarks as $remark) {
            try {
                $dbRemark        = Remark::firstOrCreate([
                                                             'text'    => $remark?->text,
                                                             'type'    => $remark?->type,
                                                             'code'    => $remark?->code,
                                                             'summary' => $remark?->summary ?? null,
                                                         ]);
                $remarkObjects[] = $dbRemark->id;
            } catch (Exception $exception) {
                report($exception);
            }
        }
        $hafasTrip->remarks()->syncWithoutDetaching($remarkObjects);
    }

    public static function refreshStopovers(stdClass $rawHafas): int {
        $payload = [];
        foreach ($rawHafas?->stopovers ?? [] as $stopover) {
            $timestampToCheck = Carbon::parse($stopover->departure ?? $stopover->arrival);
            if ($timestampToCheck->isPast() || $timestampToCheck->isAfter(now()->addDay())) {
                //HAFAS doesn't give as real time information on past stopovers, so... don't overwrite our data. :)
                continue;
            }

            $stop             = self::parseHafasStopObject($stopover->stop);
            $arrivalPlanned   = Carbon::parse($stopover->plannedArrival);
            $arrivalReal      = Carbon::parse($stopover->arrival);
            $departurePlanned = Carbon::parse($stopover->plannedDeparture);
            $departureReal    = Carbon::parse($stopover->departure);

            $payload[] = [
                'trip_id'           => $rawHafas->id,
                'train_station_id'  => $stop->id,
                'arrival_planned'   => isset($stopover->plannedArrival) ? $arrivalPlanned->toIso8601String() : $departurePlanned->toIso8601String(),
                'arrival_real'      => $arrivalReal->toDateTimeString(),
                'departure_planned' => isset($stopover->plannedDeparture) ? $departurePlanned->toIso8601String() : $arrivalPlanned->toIso8601String(),
                'departure_real'    => $departureReal->toDateTimeString(),
            ];
        }

        return TrainStopover::upsert(
            $payload,
            ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'],
            ['arrival_real', 'departure_real']
        );
    }

    /**
     * This function is used to refresh the departure of an trip, if the planned_departure is in the past and no
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
        $departure = HafasController::getDepartures(
            station: $stopover->trainStation,
            when:    $stopover->departure_planned,
        )->filter(function(stdClass $trip) use ($stopover) {
            return $trip->tripId === $stopover->trip_id;
        })->first();

        if ($departure === null || $departure->when === null || $departure->plannedWhen === $departure->when) {
            return; //do nothing, if the trip isn't found.
        }

        $stopover->update([
                              'departure_real' => Carbon::parse($departure->when)->toIso8601String(),
                          ]);
    }
}
