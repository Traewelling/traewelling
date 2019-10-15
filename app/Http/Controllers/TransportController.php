<?php

namespace App\Http\Controllers;

use App\MastodonServer;
use Mastodon;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\HafasTrip;
use App\TrainStations;
use App\Status;
use App\TrainCheckin;
use Illuminate\Support\Facades\DB;

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
        if (empty($request->station)) {
            return redirect()->back()->with('error', __('controller.transport.no-name-given'));
        }

        if (!isset($request->when)) {
            $request->when = strtotime('-5 minutes');
        }
        if (!isset($request->travelType)) {
            $request->travelType = null;
        }
            $departuresArray = $this->getTrainDepartures($request->get('station'), $request->when, $request->travelType);

            $departures = $departuresArray[1];
            $station = $departuresArray[0];

        return view('stationboard', compact('station', 'departures', 'request'));
    }

    function getTrainDepartures($station, $when='now', $trainType=null) {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $ibnrObject = json_decode($this->TrainAutocomplete($station)->content());
        $ibnr = $ibnrObject{0}->id;
        $trainTypes = array(
            'suburban' => 'false',
            'subway' => 'false',
            'tram' => 'false',
            'bus' => 'false',
            'ferry' => 'false',
            'express' => 'false',
            'regional' => 'false',
        );
        $appendix = '';

        if ($trainType != null) {
            $trainTypes[$trainType] = 'true';
            $appendix = '&'.http_build_query($trainTypes);
        }

        $response = $client->request('GET', "stations/$ibnr/departures?when=$when&duration=15" . $appendix);
        $json =  json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        if ($trainType != null && $trainType != 'express') {
            foreach ($json as $key=>$item) {
                if ($item->line->product != $trainType) {
                    unset($json[$key]);
                }
            }
        }

        return [$ibnrObject{0}, $json];
    }

    public function trainTrip(Request $request) {

        $trip = $this->getHAFAStrip($request->tripID, $request->lineName);

        $train = $trip->getAttributes();
        $stopovers = json_decode($train['stopovers'],true);
        $offset = $this->searchForId($request->start, $stopovers);
        if ($offset === null) {
            return redirect()->back()->with('error', __('controller.transport.not-in-stopovers'));
        }
        $stopovers = array_slice($stopovers, $offset + 1);
        $destination = TrainStations::where('ibnr', $train['destination'])->first()->name;
        $start = TrainStations::where('ibnr', $train['origin'])->first()->name;

        return view('trip', compact('train', 'stopovers', 'destination', 'start'));
    }

    public function trainCheckin(Request $request) {
        $this->validate($request, [
            'body' => 'max:280',
            'business_check' => 'max:2'
        ]);

        $hafas = $this->getHAFAStrip($request['tripID'], '')->getAttributes();

        $factor = DB::table('pointscalculation')->where([['type', 'train'], ['transport_type', $hafas['category']]])->first();

        if ($factor === null) {
            $factor = 1;
        } else {
            $factor = $factor->value;
        }

        $stopovers = json_decode($hafas['stopovers'], true);

        $offset1 = $this->searchForId($request->start, $stopovers);
        $offset2 = $this->searchForId($request->destination, $stopovers);

        $polyline = $this->polyline($request->start, $request->destination, json_decode($hafas['polyline'], true));

        $distance = 0.0;
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
        $status->business = isset($request['business_check']) && $request['business_check'] == 'on';

        $trainCheckin = new TrainCheckin;
        $trainCheckin->trip_id = $request['tripID'];
        $trainCheckin->origin = $originStation->ibnr;
        $trainCheckin->destination = $destinationStation->ibnr;
        $trainCheckin->distance = $distance;
        $trainCheckin->departure = self::dateToMySQLEscape($stopovers[$offset1]['departure']);
        $trainCheckin->arrival = self::dateToMySQLEscape($stopovers[$offset2]['arrival']);
        $trainCheckin->delay = $hafas['delay'];
        $trainCheckin->points = $factor + ceil($distance / 10);

        //check if there are colliding checkins
        $between = TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->where(function($query) use ($trainCheckin) {
            $query->whereBetween('arrival', [$trainCheckin->departure, $trainCheckin->arrival])->orwhereBetween('departure', [$trainCheckin->departure, $trainCheckin->arrival]);
        })->first();
        if(!empty($between)) {
            return redirect()->route('dashboard')->withErrors(__('controller.transport.overlapping-checkin', ['url' => url('/status/'.$between->id), 'id' => $between->id]));
        }

        $request->user()->statuses()->save($status)->trainCheckin()->save($trainCheckin);

        $user = $request->user();
        $user->train_distance += $trainCheckin->distance;
        $user->train_duration += (strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)) / 60;
        $user->points += $trainCheckin->points;

        $user->update();

        if ((isset($request->toot_check) || isset($request->tweet_check)) && env('POST_SOCIAL') === TRUE) {
            $post_text = "I'm in " .  $hafas['linename'] . " towards " . $destinationStation->name . '! ';
            $post_url = url("/status/{$trainCheckin->status_id}");

            if (isset($status->body)) {
                $appendix = " (@ " . $hafas['linename'] . ' ➜ ' . $destinationStation->name . ') #NowTräwelling ';
                $appendix_length = strlen($appendix) + 30;
                $post_text = substr($status->body, 0, 280 - $appendix_length);
                if (strlen($post_text) != strlen($status->body)) {
                    $post_text.='...';
                }
                $post_text .= $appendix;
            }

            if (isset($request->toot_check)) {
                $mastodonDomain = MastodonServer::where('id', $user->socialProfile->mastodon_server)->first()->domain;
                Mastodon::domain($mastodonDomain)->token($user->socialProfile->mastodon_token);
                Mastodon::createStatus($post_text . $post_url, ['visibility' => 'unlisted']);
            }
            if (isset($request->tweet_check)) {
                $connection = new TwitterOAuth(env('TWITTER_ID'), env('TWITTER_SECRET'), $user->socialProfile->twitter_token, $user->socialProfile->twitter_tokenSecret);

                $connection->post("statuses/update", ["status" => $post_text . $post_url, 'lat' => $originStation->latitude, 'lon' => $originStation->longitude]);
            }
        }

        // check for other people on this train
        $alsoOnThisTrain = [];
        $corresponding = TrainCheckin::where([
            ['trip_id', '=', $trainCheckin->trip_id],
            ['status_id', '!=', $status->id]
        ])->get()
            ->filter(function ($t) use ($trainCheckin) {
                return ($t->arrival > $trainCheckin->departure) && ($t->departure < $trainCheckin->arrival);
            });

        foreach ($corresponding as $t) {
            $u = $t->status->user;
            $alsoOnThisTrain[] = "<a href=\"" . route('account.show',  ['username' => $u->username]) . "\">" . $u->name . " (@" . $u->username . ")</a>";
        }

        $concatSameTrain = implode(', ', $alsoOnThisTrain);
        if (!empty($concatSameTrain)) {
            $concatSameTrain = "<br />" . trans_choice('controller.transport.also-in-train', count($alsoOnThisTrain), ['people' => $concatSameTrain]);
        }

        return redirect()->route('dashboard')->with(
            'message',
            __('controller.transport.checkin-ok', ['pts' => $trainCheckin->points])
                . $concatSameTrain
        );
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
            if ($json->line->name === null) {
                $json->line->name = $json->line->fahrtNr;
            }

            if ($json->line->id === null) {
                $json->line->id = '';
            }

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
            $station->longitude = $longitude;
            $station->save();
        }
        return $station;
    }

}
