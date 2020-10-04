<?php

namespace App\Http\Controllers;

use App\Exceptions\HafasException;
use App\Models\TrainStation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HafasController extends Controller
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
}
