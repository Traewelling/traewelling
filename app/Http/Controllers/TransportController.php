<?php

namespace App\Http\Controllers;

use App\MastodonServer;
use Mastodon;
use Abraham\TwitterOAuth\TwitterOAuth;
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
    public static function dateToMySQLEscape(String $in): String {
        return date("Y-m-d H:i:s", strtotime($in));
    }

    public static function TrainAutocomplete($station) {
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
        return $array;
    }

    public static function BusAutocomplete($station) {
        $client = new Client(['base_uri' => env('FLIX_REST','https://1.flixbus.transport.rest/')]);
        $response = $client->request('GET', "stations/?query=$station");
        $json = $response->getBody()->getContents();
        $array = json_decode($json, true);

        foreach(array_keys($array) as $key) {
            unset($array[$key]['relevance']);
            unset($array[$key]['score']);
            unset($array[$key]['weight']);
            unset($array[$key]['type']);
            $array[$key]['provider'] = 'bus';
        }
        return $array;
    }

    public static function TrainStationboard($station, $when='now', $travelType=null) {
        if (empty($station)) {
            return false;
        }
        if ($when === null) {
            $when = strtotime('-5 minutes');
        }
        $departuresArray = self::getTrainDepartures($station, $when, $travelType);
        $station = $departuresArray[0];
        $departures = $departuresArray[1];

        if (empty($station['name'])) {
            return null;
        }
        return ['station' => $station, 'departures' => $departures, 'when' => $when];
    }

    private static function getTrainDepartures($station, $when='now', $trainType=null) {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $ibnrObject = self::TrainAutocomplete($station);
        $ibnr = $ibnrObject[0]['id'];
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
        $json = json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        if ($trainType != null && $trainType != 'express') {
            foreach ($json as $key=>$item) {
                if ($item->line->product != $trainType) {
                    unset($json[$key]);
                }
            }
        }
        $json = self::sortByWhenOrScheduledWhen($json);
        return [$ibnrObject[0], $json];
    }

    // Train with cancelled stops show up in the stationboard sometimes with when == 0.
    // However, they will have a scheduledWhen. This snippet will sort the departures
    // by actualWhen or use scheduledWhen if actual is empty.
    public static function sortByWhenOrScheduledWhen(Array $departuresList): Array {
        uasort($departuresList, function($a, $b) {
            $dateA = $a->when;
            if($dateA == null) {
                $dateA = $a->scheduledWhen;
            }

            $dateB = $b->when;
            if($dateB == null) {
                $dateB = $b->scheduledWhen;
            }

            return ($dateA < $dateB) ? -1 : 1;
        });

        return $departuresList;
    }

    public static function TrainTrip($tripId, $lineName, $start) {

        $trip = self::getHAFAStrip($tripId, $lineName);

        $train = $trip->getAttributes();
        $stopovers = json_decode($train['stopovers'],true);
        $offset = self::searchForId($start, $stopovers);
        if ($offset === null) {
            return null;
        }
        $stopovers = array_slice($stopovers, $offset + 1);
        $destination = TrainStations::where('ibnr', $train['destination'])->first()->name;
        $start = TrainStations::where('ibnr', $train['origin'])->first()->name;

        return [
            'train' => $train,
            'stopovers' => $stopovers,
            'destination' => $destination,
            'start' => $start
        ];
    }

    private static function CalculateTrainPoints($distance, $category, $departure, $delay) {
        $factor = DB::table('pointscalculation')
            ->where([
                        ['type', 'train'],
                        ['transport_type', $category
                        ]])
            ->first();
        $factor = $factor->value;
        if ($factor === null) { $factor = 1; }
        $time = strtotime($departure);
        $points = $factor + ceil($distance / 10);
        if ($time < strtotime('+20 minutes') && $time > strtotime('-20 minutes')) {
            return $points;
        }

        if ($time < strtotime('+1 hour') && $time > strtotime('-1 hour')) {
            return ceil($points * 0.25);
        }
        return 1;
    }

    public static function TrainCheckin($tripId, $start, $destination, $body, $user, $business_check, $tweet_check, $toot_check) {
        $hafas = self::getHAFAStrip($tripId, '')->getAttributes();
        $stopovers = json_decode($hafas['stopovers'], true);
        $offset1 = self::searchForId($start, $stopovers);
        $offset2 = self::searchForId($destination, $stopovers);
        $polyline = self::polyline($start, $destination, json_decode($hafas['polyline'], true));
        $distance = 0.0;
        foreach ($polyline as $key=>$point) {
            if ($key === 0) { continue; }
            $distance += self::distanceCalculation(
                $point['geometry']['coordinates'][0],
                $point['geometry']['coordinates'][1],
                $polyline[$key-1]['geometry']['coordinates'][0],
                $polyline[$key-1]['geometry']['coordinates'][1]
            );
            //I really don't know what i did here or if there's a better version for this but fuck it, it's 5am and it works.
        }
        $originAttributes = $stopovers[$offset1];
        $destinationAttributes = $stopovers[$offset2];
        $originStation = self::getTrainStation(
            $originAttributes['stop']['id'],
            $originAttributes['stop']['name'],
            $originAttributes['stop']['location']['latitude'],
            $originAttributes['stop']['location']['longitude']
        );
        $destinationStation = self::getTrainStation(
            $destinationAttributes['stop']['id'],
            $destinationAttributes['stop']['name'],
            $destinationAttributes['stop']['location']['latitude'],
            $destinationAttributes['stop']['location']['longitude']
        );
        $points = self::CalculateTrainPoints(
            $distance,
            $hafas['category'],
            $stopovers[$offset1]['departure'],
            $hafas['delay']
        );

        $status = new Status();
        $status->body = $body;
        $status->business = isset($business_check) && $business_check == 'on';

        $trainCheckin = new TrainCheckin;
        $trainCheckin->trip_id = $tripId;
        $trainCheckin->origin = $originStation->ibnr;
        $trainCheckin->destination = $destinationStation->ibnr;
        $trainCheckin->distance = $distance;
        $trainCheckin->departure = self::dateToMySQLEscape($stopovers[$offset1]['departure']);
        $trainCheckin->arrival = self::dateToMySQLEscape($stopovers[$offset2]['arrival']);
        $trainCheckin->delay = $hafas['delay'];
        $trainCheckin->points = $points;

        //check if there are colliding checkins
        $overlap = TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where(function($query) use ($trainCheckin) {
            $query->whereBetween('arrival', [$trainCheckin->departure, $trainCheckin->arrival])
                ->orwhereBetween('departure', [$trainCheckin->departure, $trainCheckin->arrival]);
        })->first();
        if(!empty($overlap)) {
            return ['success' => false, 'overlap' => $overlap];
        }

        $user->statuses()->save($status)->trainCheckin()->save($trainCheckin);

        $user->train_distance += $trainCheckin->distance;
        $user->train_duration += (strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)) / 60;
        $user->points += $trainCheckin->points;

        $user->update();

        if ((isset($toot_check) || isset($tweet_check)) && env('POST_SOCIAL') === TRUE) {
            $post_text = __(
                'controller.transport.social-post',
                ['linename' => $hafas['linename'], 'destination' => $destinationStation->name]
            );
            
            $post_url = url("/status/{$trainCheckin->status_id}");

            if (isset($status->body)) {
                $appendix = " (@ " . $hafas['linename'] . ' ➜ ' . $destinationStation->name . ") #NowTräwelling ";
                
                $appendix_length = strlen($appendix) + 30;
                $post_text = substr($status->body, 0, 280 - $appendix_length);
                if (strlen($post_text) != strlen($status->body)) {
                    $post_text.='...';
                }
                $post_text .= $appendix;
            }

            if (isset($toot_check)) {
                $mastodonDomain = MastodonServer::where('id', $user->socialProfile->mastodon_server)->first()->domain;
                Mastodon::domain($mastodonDomain)->token($user->socialProfile->mastodon_token);
                Mastodon::createStatus($post_text . $post_url, ['visibility' => 'unlisted']);
            }
            if (isset($tweet_check)) {
                $connection = new TwitterOAuth(
                    env('TWITTER_ID'),
                    env('TWITTER_SECRET'),
                    $user->socialProfile->twitter_token,
                    $user->socialProfile->twitter_tokenSecret
                );
                // #dbl only works on Twitter.
                if($user->always_dbl) {
                    $post_text .= "#dbl ";
                }
                $connection->post(
                    "statuses/update",
                    [
                        "status" => $post_text . $post_url,
                        'lat' => $originStation->latitude,
                        'lon' => $originStation->longitude
                    ]
                );
            }
        }

        // check for other people on this train
        $alsoOnThisConnection = TrainCheckin::where([
            ['trip_id', '=', $trainCheckin->trip_id],
            ['status_id', '!=', $status->id]
        ])->get()
            ->filter(function ($t) use ($trainCheckin) {
                return ($t->arrival > $trainCheckin->departure) && ($t->departure < $trainCheckin->arrival);
            });

        return [
            'success' => true,
            'points' => $trainCheckin->points,
            'alsoOnThisConnection' => $alsoOnThisConnection,
            'lineName' => $hafas['linename'],
            'distance' => $trainCheckin->distance,
            'duration' => strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)
        ];
    }

    private static function getHAFAStrip($tripID, $lineName) {
        $trip = HafasTrip::where('trip_id', $tripID)->first();

        if ($trip === null) {
            $trip = new HafasTrip;

            $client = new Client(['base_uri' => env('DB_REST', 'http://localhost:3000/')]);
            $response = $client->request('GET', "trips/$tripID?lineName=$lineName&polyline=true");
            $json = json_decode($response->getBody()->getContents());


            $origin = self::getTrainStation($json->origin->id, $json->origin->name, $json->origin->location->latitude, $json->origin->location->longitude);
            $destination = self::getTrainStation($json->destination->id, $json->destination->name, $json->destination->location->latitude, $json->destination->location->longitude);
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

    private static function getTrainStation ($ibnr, $name, $latitude, $longitude) {
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

    public static function getLatestArrivals($user) {
        return TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function($t) {
            return TrainStations::where("ibnr", $t->destination)->first();
        })->unique()->take(5);
    }

    public static function SetHome($user, $ibnr) {
        $client = new Client(['base_uri' => env('DB_REST','https://2.db.transport.rest/')]);
        $response = $client->request('GET', "locations?query=$ibnr")->getBody()->getContents();
        $ibnrObject = json_decode($response);

        $station = self::getTrainStation(
            $ibnrObject[0]->id,
            $ibnrObject[0]->name,
            $ibnrObject[0]->location->latitude,
            $ibnrObject[0]->location->longitude
        );

        $user->home_id = $station->id;
        try {
            $user->save();
        }
        catch (\Exception $e) {
            return false;
        }
        return $station->name;
    }
}
