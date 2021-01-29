<?php

namespace App\Http\Controllers;

use App\Exceptions\HafasException;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
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

    private static function parseHafasStopObject(stdClass $hafasStop): TrainStation {
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
                    'when'            => $when->toIso8601String(),
                    'duration'        => $duration,
                    'nationalExpress' => $nationalExpress ? 'true' : 'false',
                    'national'        => $national ? 'true' : 'false',
                    'regionalExp'     => $regionalExp ? 'true' : 'false',
                    'regional'        => $regional ? 'true' : 'false',
                    'suburban'        => $suburban ? 'true' : 'false',
                    'bus'             => $bus ? 'true' : 'false',
                    'ferry'           => $ferry ? 'true' : 'false',
                    'subway'          => $subway ? 'true' : 'false',
                    'tram'            => $tram ? 'true' : 'false',
                    'taxi'            => $taxi ? 'true' : 'false',
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

    public static function getHafasTrip(string $tripID, string $lineName): HafasTrip {
        $trip = HafasTrip::where('trip_id', $tripID)->first();
        if ($trip !== null) {
            return $trip;
        }

        return self::fetchHafasTrip($tripID, $lineName);
    }

    private static function fetchHafasTrip(string $tripID, string $lineName): HafasTrip {
        $tripClient   = new Client(['base_uri' => config('trwl.db_rest')]);
        $tripResponse = $tripClient->get("trips/$tripID", [
            'query' => [
                'lineName'  => $lineName,
                'polyline'  => 'true',
                'stopovers' => 'true'
            ]
        ]);
        $tripJson     = json_decode($tripResponse->getBody()->getContents());

        $origin      = self::parseHafasStopObject($tripJson->origin);
        $destination = self::parseHafasStopObject($tripJson->destination);

        if ($tripJson->line->name === null) {
            $tripJson->line->name = $tripJson->line->fahrtNr;
        }

        if ($tripJson->line->id === null) {
            $tripJson->line->id = '';
        }

        $polylineHash = TransportController::getPolylineHash(json_encode($tripJson->polyline))->hash;

        return HafasTrip::updateOrCreate([
                                             'trip_id' => $tripID
                                         ], [
                                             'category'    => $tripJson->line->product,
                                             'number'      => $tripJson->line->id,
                                             'linename'    => $tripJson->line->name,
                                             'origin'      => $origin->ibnr,
                                             'destination' => $destination->ibnr,
                                             'stopovers'   => json_encode($tripJson->stopovers),
                                             'polyline'    => $polylineHash,
                                             'departure'   => $tripJson->plannedDeparture,
                                             'arrival'     => $tripJson->plannedArrival,
                                             'delay'       => $tripJson->arrivalDelay ?? null
                                         ]);

    }

}
