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
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;
use PDOException;
use stdClass;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 * @deprecated Will be replaced by https://github.com/Traewelling/hafas-client-php
 */
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
            $data     = json_decode($response->getBody()->getContents());
            return TrainStation::updateOrCreate([
                                                    'ibnr' => $data->id
                                                ], [
                                                    'rilIdentifier' => $data->ril100,
                                                    'name'          => $data->name,
                                                    'latitude'      => $data->location->latitude,
                                                    'longitude'     => $data->location->longitude
                                                ]);
        } catch (GuzzleException) {
            return null;
        }
    }

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

            $data     = json_decode($response->getBody()->getContents());
            $stations = collect();
            foreach ($data as $hafasStation) {
                $stations->push(self::parseHafasStopObject($hafasStation));
            }

            return $stations;
        } catch (GuzzleException $e) {
            throw new HafasException($e->getMessage());
        }
    }

    public static function parseHafasStopObject(stdClass $hafasStop): TrainStation {
        return TrainStation::updateOrCreate([
                                                'ibnr' => $hafasStop->id
                                            ], [
                                                'name'      => $hafasStop->name,
                                                'latitude'  => $hafasStop?->location?->latitude,
                                                'longitude' => $hafasStop?->location?->longitude,
                                            ]);
    }

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

            $data     = json_decode($response->getBody()->getContents());
            $stations = collect();
            foreach ($data as $hafasStation) {
                $station           = self::parseHafasStopObject($hafasStation);
                $station->distance = $hafasStation->distance ?? 0;
                $stations->push($station);
            }

            return $stations;
        } catch (GuzzleException $e) {
            throw new HafasException($e->getMessage());
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

            $data       = json_decode($response->getBody()->getContents());
            $departures = collect();
            foreach ($data as $departure) {
                $departure->station = self::getTrainStation($departure->stop->id);
                $departures->push($departure);
            }

            return $departures;
        } catch (GuzzleException $e) {
            throw new HafasException($e->getMessage());
        }
    }

    /**
     * Get the TrainStation Model from Database
     *
     * @param int         $ibnr
     * @param string|null $name
     * @param float|null  $latitude
     * @param float|null  $longitude
     *
     * @return TrainStation
     * @throws HafasException
     */
    public static function getTrainStation(int    $ibnr,
                                           string $name = null,
                                           float  $latitude = null,
                                           float  $longitude = null): TrainStation {

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
     * @param string $tripID
     * @param string $lineName
     *
     * @return HafasTrip
     * @throws HafasException
     */
    public static function fetchHafasTrip(string $tripID, string $lineName): HafasTrip {
        $tripClient = new Client(['base_uri' => config('trwl.db_rest'), 'timeout' => config('trwl.db_rest_timeout')]);
        try {
            $tripResponse = $tripClient->get("trips/$tripID", [
                'query' => [
                    'lineName'  => $lineName,
                    'polyline'  => 'true',
                    'stopovers' => 'true'
                ]
            ]);
            $tripJson     = json_decode($tripResponse->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException|JsonException) {
            //sometimes DB-Rest gives 502 Bad Request
            throw new HafasException(__('messages.exception.generalHafas'));
        }
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

        foreach ($tripJson->stopovers as $stopover) {
            $hafasStop = self::parseHafasStopObject($stopover->stop);
            //This array is a workaround because Hafas doesn't give
            //us delay-data if the train already passed this station
            //so.. just save data we really got. :)
            $updatePayload = [
                'arrival_platform_planned'   => $stopover->plannedArrivalPlatform,
                'departure_platform_planned' => $stopover->plannedDeparturePlatform,
            ];
            //remove "null" values
            $updatePayload = array_filter($updatePayload, 'strlen');

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
            $updatePayload['cancelled'] = $stopover?->cancelled ?? false;
            try {
                TrainStopover::updateOrCreate(
                    [
                        'trip_id'           => $tripID,
                        'train_station_id'  => $hafasStop->id,
                        'arrival_planned'   => isset($stopover->plannedArrival) ? Carbon::parse($stopover->plannedArrival)->format('Y-m-d H:i:s') : null,
                        'departure_planned' => isset($stopover->plannedDeparture) ? Carbon::parse($stopover->plannedDeparture)->format('Y-m-d H:i:s') : null,
                    ],
                    $updatePayload
                );
            } catch (PDOException $exception) {
                report($exception);
            }
        }

        self::saveRemarks($tripJson?->remarks ?? [], $hafasTrip);

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
}
