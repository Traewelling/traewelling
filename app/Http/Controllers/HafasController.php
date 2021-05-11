<?php

namespace App\Http\Controllers;

use App\Enum\HafasTravelType;
use App\Exceptions\HafasException;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use PDOException;
use stdClass;

abstract class HafasController extends Controller
{

    /**
     * Fetch from HAFAS
     * @param int $ibnr
     * @return TrainStation
     * @throws HafasException
     */
    public static function fetchTrainStation(int $ibnr): TrainStation {
        try {
            $client   = new Client(['base_uri' => config('trwl.db_rest')]);
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
            $response = $e->getResponse()->getBody()->getContents();
            throw new HafasException($response->msg ?? $e->getMessage());
        }
    }

    public static function getTrainStationByRilIdentifier(string $rilIdentifier): ?TrainStation {
        $trainStation = TrainStation::where('rilIdentifier', $rilIdentifier)->first();
        if ($trainStation != null) {
            return $trainStation;
        }
        try {
            $client   = new Client(['base_uri' => config('trwl.db_rest')]);
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
            $client   = new Client(['base_uri' => config('trwl.db_rest')]);
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
            $response = $e->getResponse()->getBody()->getContents();
            throw new HafasException($response->msg ?? $e->getMessage());
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
            $client   = new Client(['base_uri' => config('trwl.db_rest')]);
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
                $station->distance = $hafasStation->distance;
                $stations->push($station);
            }

            return $stations;
        } catch (GuzzleException $e) {
            $response = $e->getResponse()->getBody()->getContents();
            throw new HafasException($response->msg ?? $e->getMessage());
        }
    }

    /**
     * @param TrainStation $station
     * @param Carbon $when
     * @param int $duration
     * @param bool $nationalExpress
     * @param bool $national
     * @param bool $regionalExp
     * @param bool $regional
     * @param bool $suburban
     * @param bool $bus
     * @param bool $ferry
     * @param bool $subway
     * @param bool $tram
     * @param bool $taxi
     * @return Collection
     * @throws HafasException
     */
    public static function getDepartures(
        TrainStation $station,
        Carbon $when,
        int $duration = 15,
        bool $nationalExpress = true,
        bool $national = true,
        bool $regionalExp = true,
        bool $regional = true,
        bool $suburban = true,
        bool $bus = true,
        bool $ferry = true,
        bool $subway = true,
        bool $tram = true,
        bool $taxi = true
    ): Collection {
        try {
            $client   = new Client(['base_uri' => config('trwl.db_rest')]);
            $response = $client->get('/stops/' . $station->ibnr . '/departures', [
                'query' => [
                    'when'                            => $when->toIso8601String(),
                    'duration'                        => $duration,
                    HafasTravelType::NATIONAL_EXPRESS => $nationalExpress ? 'true' : 'false',
                    HafasTravelType::NATIONAL         => $national ? 'true' : 'false',
                    HafasTravelType::REGIONAL_EXP     => $regionalExp ? 'true' : 'false',
                    HafasTravelType::REGIONAL         => $regional ? 'true' : 'false',
                    HafasTravelType::SUBURBAN         => $suburban ? 'true' : 'false',
                    HafasTravelType::BUS              => $bus ? 'true' : 'false',
                    HafasTravelType::FERRY            => $ferry ? 'true' : 'false',
                    HafasTravelType::SUBWAY           => $subway ? 'true' : 'false',
                    HafasTravelType::TRAM             => $tram ? 'true' : 'false',
                    HafasTravelType::TAXI             => $taxi ? 'true' : 'false',
                ]
            ]);

            $data       = json_decode($response->getBody()->getContents());
            $departures = collect();
            foreach ($data as $departure) {
                $departures->push($departure);
            }

            return $departures;
        } catch (GuzzleException $e) {
            $response = $e->getResponse()->getBody()->getContents();
            throw new HafasException($response->msg ?? $e->getMessage());
        }
    }

    /**
     * @param string $tripID
     * @param string $lineName
     * @return HafasTrip
     * @throws HafasException
     */
    public static function getHafasTrip(string $tripID, string $lineName): HafasTrip {
        $trip = HafasTrip::where('trip_id', $tripID)->first();
        if ($trip !== null) {
            return $trip;
        }

        return self::fetchHafasTrip($tripID, $lineName);
    }

    /**
     * @param string $tripID
     * @param string $lineName
     * @return HafasTrip
     * @throws HafasException
     */
    public static function fetchHafasTrip(string $tripID, string $lineName): HafasTrip {
        $tripClient = new Client(['base_uri' => config('trwl.db_rest')]);
        try {
            $tripResponse = $tripClient->get("trips/$tripID", [
                'query' => [
                    'lineName'  => $lineName,
                    'polyline'  => 'true',
                    'stopovers' => 'true'
                ]
            ]);
        } catch (GuzzleException) {
            //sometimes DB-Rest gives 502 Bad Request
            throw new HafasException(__('messages.exception.generalHafas'));
        }
        $tripJson = json_decode($tripResponse->getBody()->getContents());

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

        $polylineHash = TransportController::getPolylineHash(json_encode($tripJson->polyline))->hash;

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
                                                   'polyline'    => $polylineHash,
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
                'arrival_planned'            => $stopover->plannedArrival,
                'arrival_platform_planned'   => $stopover->plannedArrivalPlatform,
                'departure_planned'          => $stopover->plannedDeparture,
                'departure_platform_planned' => $stopover->plannedDeparturePlatform
            ];
            //remove "null" values
            $updatePayload = array_filter($updatePayload, 'strlen');

            if ($stopover->arrival != null && Carbon::parse($stopover->arrival)->isFuture()) {
                $updatePayload['arrival_real'] = $stopover->arrival;
                if ($stopover->arrivalPlatform != null) {
                    $updatePayload['arrival_platform_real'] = $stopover->arrivalPlatform;
                }
            }
            if ($stopover->departure != null && Carbon::parse($stopover->departure)->isFuture()) {
                $updatePayload['departure_real'] = $stopover->departure;
                if ($stopover->departurePlatform != null) {
                    $updatePayload['departure_platform_real'] = $stopover->departurePlatform;
                }
            }
            try {
                TrainStopover::updateOrCreate(
                    [
                        'trip_id'          => $tripID,
                        'train_station_id' => $hafasStop->id
                    ], $updatePayload
                );
            } catch (PDOException $exception) {
                report($exception);
            }
        }

        return $hafasTrip;
    }
}
