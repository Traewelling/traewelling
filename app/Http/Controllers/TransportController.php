<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\HafasTrip;
use App\TrainStations;

class TransportController extends Controller
{
    public function TrainAutocomplete($station) {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $response = $client->request('GET', "stations?query=$station&fuzzy=true");
        if ($response->getBody()->getContents() <= 2 ) {
            $response = $client->request('GET', "locations?query=$station");
        }

        $json = $response->getBody()->getContents();

        $array=json_decode($json, true);

        foreach(array_keys($array) as $key) {
            unset($array[$key]['type']);
            unset($array[$key]['location']);
            unset($array[$key]['products']);
            $array[$key]['provider'] = 'train';
        }

        return response()->json($array);
    }

    public function BusAutocomplete($station) {
        $client = new Client(['base_uri' => env('FLIX_REST','https://1.flixbus.transport.rest/')]);
        $response = $client->request('GET', "stations/?query=$station");


        $json = $response->getBody()->getContents();

        $array=json_decode($json, true);

        foreach(array_keys($array) as $key) {
            unset($array[$key]['relevance']);
            unset($array[$key]['score']);
            unset($array[$key]['weight']);
            unset($array[$key]['type']);
            $array[$key]['provider'] = 'bus';
        }

        return response()->json($array);
    }

    public function stationboard(Request $request) {

        if (!isset($request->when)) {
            $request->when = 'now';
        }
        if ($request->get('provider') === 'train') {
            $departuresArray = $this->getTrainDepartures($request->get('station'), $request->when);
            $departures = $departuresArray[1];
            $station = $departuresArray[0];
        } elseif($request->get('provider') === 'bus') {
            $baseURI = env('FLIX_REST', 'https://1.flixbus.transport.rest/');
        }

        return view('stationboard', compact('station', 'departures'));
    }

    function getTrainDepartures($station, $when='now') {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $ibnrObject = json_decode($this->TrainAutocomplete($station)->content());
        $ibnr = $ibnrObject{0}->id;

        $response = $client->request('GET', "stations/$ibnr/departures?when=$when");
        $json =  json_decode($response->getBody()->getContents());

        return [$ibnrObject{0}, $json];
    }

    function trip(Request $request) {

        $trip = $this->getHAFAStrip($request->tripID, $request->lineName);

        $train = $trip->getAttributes();
        $stopovers = json_decode($train['stopovers'],true);
        $offset = $this->searchForId($request->start, $stopovers) + 1;
        $stopovers = array_slice($stopovers, $offset);
        $destination = TrainStations::where('ibnr', $train['destination'])->first()->name;

        return view('trip', compact('train', 'stopovers', 'destination'));
    }

    function getHAFAStrip($tripID, $lineName) {
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
            if(isset($json->arrivalDelay)) {
                $trip->delay = $json->arrivalDelay;
            }
            $trip->save();
        }

        return $trip;
    }


}
