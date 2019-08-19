<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\HafasTrip;
use App\TrainStations;
use App\Status;
use App\TrainCheckin;

class TransportController extends Controller
{
    /**
     * Takes just about any date string and formats it in Y-m-d H:i:s which is
     * the required format for MySQL inserts.
     * @return String
     */
    function dateToMySQLEscape(String $in): String {
        return date("Y-m-d H:i:s", strtotime($in));
    }

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

    public function trainStationboard(Request $request) {

        if (!isset($request->when)) {
            $request->when = 'now';
        }
            $departuresArray = $this->getTrainDepartures($request->get('station'), $request->when);
            $departures = $departuresArray[1];
            $station = $departuresArray[0];

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

    public function trainTrip(Request $request) {

        $trip = $this->getHAFAStrip($request->tripID, $request->lineName);

        $train = $trip->getAttributes();
        $stopovers = json_decode($train['stopovers'],true);
        $offset = $this->searchForId($request->start, $stopovers);
        if ($offset === null) {
            return redirect()->back()->with('error', __('Start-ID is not in stopovers.'));
        }
        $stopovers = array_slice($stopovers, $offset + 1);
        $destination = TrainStations::where('ibnr', $train['destination'])->first()->name;
        $start = TrainStations::where('ibnr', $train['origin'])->first()->name;

        return view('trip', compact('train', 'stopovers', 'destination', 'start'));
    }

    public function trainCheckin(Request $request) {
        $this->validate($request, [
            'body' => 'required|max:140'
        ]);

        $hafas = $this->getHAFAStrip($request['tripID'], '')->getAttributes();

        $stopovers = json_decode($hafas['stopovers'], true);

        $offset1 = $this->searchForId($request->start, $stopovers);
        $offset2 = $this->searchForId($request->destination, $stopovers);

        $polyline = $this->polyline($request->start, $request->destination, json_decode($hafas['polyline'], true));

        $distance = 0;
        foreach ($polyline as $key=>$point) {
            if ($key === 0) {
                continue;
            }
            $distance += $this->distanceCalculation($point['geometry']['coordinates'][0], $point['geometry']['coordinates'][1], $polyline[$key-1]['geometry']['coordinates'][0], $polyline[$key-1]['geometry']['coordinates'][1]);
            //I really don't know what i did here or if there's a better version for this but fuck it, it's 5am and it works.
        }

        $originAttributes = $stopovers[$offset1];
        $destinationAttributes = $stopovers[$offset2];
        $originStation = $this->getTrainStation($originAttributes['stop']['id'],$originAttributes['stop']['name'], $originAttributes['stop']['location']['latitude'], $originAttributes['stop']['location']['longitude']);
        $destinationStation = $this->getTrainStation($destinationAttributes['stop']['id'],$destinationAttributes['stop']['name'], $destinationAttributes['stop']['location']['latitude'], $destinationAttributes['stop']['location']['longitude']);

        $status = new Status();
        $status->body = $request['body'];

        $trainCheckin = new TrainCheckin;
        $trainCheckin->trip_id = $request['tripID'];
        $trainCheckin->origin = $originStation->ibnr;
        $trainCheckin->destination = $destinationStation->ibnr;
        $trainCheckin->distance = $distance;
        $trainCheckin->departure = self::dateToMySQLEscape($stopovers[$offset1]['departure']);
        $trainCheckin->arrival = self::dateToMySQLEscape($stopovers[$offset2]['arrival']);
        $trainCheckin->delay = $hafas['delay'];

        $request->user()->statuses()->save($status)->trainCheckin()->save($trainCheckin);

        return redirect()->route('dashboard')->with('message', 'Checked in!');
    }

    function getHAFAStrip($tripID, $lineName) {
        $trip = HafasTrip::where('trip_id', $tripID)->first();

        if ($trip === null) {
            $trip = new HafasTrip;

            $client = new Client(['base_uri' => env('DB_REST', 'http://localhost:3000/')]);
            $response = $client->request('GET', "trips/$tripID?lineName=$lineName&polyline=true");
            $json = json_decode($response->getBody()->getContents());


            $origin = $this->getTrainStation($json->origin->id, $json->origin->name, $json->origin->location->latitude, $json->origin->location->longitude);
            $destination = $this->getTrainStation($json->destination->id, $json->destination->name, $json->destination->location->latitude, $json->destination->location->longitude);

            $trip->trip_id = $tripID;
            $trip->category = $json->line->product;
            $trip->number = $json->line->id;
            $trip->linename = $json->line->name;
            $trip->origin = $origin->ibnr;
            $trip->destination = $destination->ibnr;
            $trip->stopovers = json_encode($json->stopovers);
            $trip->polyline = json_encode($json->polyline);
            $trip->departure = self::dateToMySQLEscape($json->departure);
            $trip->arrival = self::dateToMySQLEscape($json->arrival);
            if(isset($json->arrivalDelay)) {
                $trip->delay = $json->arrivalDelay;
            }
            $trip->save();
        }

        return $trip;
    }

    function getTrainStation ($ibnr, $name, $latitude, $longitude) {
        $station = TrainStations::where('ibnr', $ibnr)->first();
        if ($station === null) {
            $station = New TrainStations;
            $station->ibnr = $ibnr;
            $station->name = $name;
            $station->latitude = $latitude;
            $station->longtitude = $longitude;
            $station->save();
        }
        return $station;
    }

}
