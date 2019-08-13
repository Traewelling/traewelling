<?php

namespace App\Http\Controllers;

use App\HafasTrip;
use App\TrainStations;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class HafasTripController extends Controller
{
    public function getTrip(Request $request) {
        $tripID = $request->tripID;
        $lineName = $request->lineName;
        $trip = HafasTrip::where('trip_id', $tripID)->first();

        if ($trip === null) {
            $trip = new HafasTrip;

            $client = new Client(['base_uri' => env('DB_REST', 'http://localhost:3000/')]);
            $response = $client->request('GET', "trips/$tripID?lineName=$lineName&polyline=true");
            $json = json_decode($response->getBody()->getContents());

            $origin = TrainStations::where('ibnr', $json->origin->id)->first();
            if ($origin === null) {
                $origin = New TrainStations;
                $origin->ibnr = $json->origin->id;
                $origin->name = $json->origin->name;
                $origin->latitude = $json->origin->location->latitude;
                $origin->longtitude = $json->origin->location->longitude;
                $origin->save();
            }
            $destination = TrainStations::where('ibnr', $json->destination->id)->first();
            if ($destination === null) {
                $destination = New TrainStations;
                $destination->ibnr = $json->destination->id;
                $destination->name = $json->destination->name;
                $destination->latitude = $json->destination->location->latitude;
                $destination->longtitude = $json->destination->location->longitude;
                $destination->save();
            }
            $trip->trip_id = $tripID;
            $trip->category = $json->line->product;
            $trip->number = $json->line->id;
            $trip->linename = $json->line->name;
            $trip->origin = $origin->ibnr;
            $trip->destination = $destination->ibnr;
            $trip->stopovers = json_encode($json->stopovers);
            $trip->polyline = json_encode($json->polyline);
            $trip->departure = $json->departure;
            $trip->arrival = $json->arrival;
            $trip->delay = $json->arrivalDelay;
            $trip->save();
        }
        return $trip;
    }
}
