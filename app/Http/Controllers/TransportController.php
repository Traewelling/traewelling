<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

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
            $departures = $this->getTrainDepartures($request->get('station'), $request->when);
        } elseif($request->get('provider') === 'bus') {
            $baseURI = env('FLIX_REST', 'https://1.flixbus.transport.rest/');
        }

        return view('stationboard', compact('departures'));
    }

    function getTrainDepartures($station, $when='now') {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $response = $client->request('GET', "stations/$station/departures?when=$when");

        return json_decode($response->getBody()->getContents());
    }
}
