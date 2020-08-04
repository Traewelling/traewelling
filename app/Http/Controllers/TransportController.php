<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Event;
use App\HafasTrip;
use App\MastodonServer;
use App\PolyLine;
use App\Status;
use App\TrainCheckin;
use App\TrainStations;
use App\Notifications\TwitterNotSent;
use App\Notifications\MastodonNotSent;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mastodon;

class TransportController extends Controller
{
    /**
     * Takes just about any date string and formats it in Y-m-d H:i:s which is
     * the required format for MySQL inserts.
     * @return String
     */
    public static function dateToMySQLEscape(String $timeString, $delaySeconds = 0): String
    {
        return date("Y-m-d H:i:s", strtotime($timeString) - $delaySeconds);
    }

    public static function TrainAutocomplete($station)
    {
        $client   = new Client(['base_uri' => config('trwl.db_rest')]);
        $response = $client->request('GET', "stations?query=$station&fuzzy=true");
        if ($response->getBody()->getContents() <= 2) {
            $response = $client->request('GET', "locations?query=$station");
        }
        $json  = $response->getBody()->getContents();
        $array = json_decode($json, true);
        foreach(array_keys($array) as $key) {
            unset($array[$key]['type']);
            unset($array[$key]['location']);
            unset($array[$key]['products']);
            $array[$key]['provider'] = 'train';
        }
        return $array;
    }

    public static function BusAutocomplete($station)
    {
        $client   = new Client(['base_uri' => config('trwl.flix_rest')]);
        $response = $client->request('GET', "stations/?query=$station");
        $json     = $response->getBody()->getContents();
        $array    = json_decode($json, true);

        foreach(array_keys($array) as $key) {
            unset($array[$key]['relevance']);
            unset($array[$key]['score']);
            unset($array[$key]['weight']);
            unset($array[$key]['type']);
            $array[$key]['provider'] = 'bus';
        }
        return $array;
    }

    public static function TrainStationboard($station, $when='now', $travelType=null)
    {
        if (empty($station)) {
            return false;
        }
        if ($when === null) {
            $when = strtotime('-5 minutes');
        }
        $ibnrObject = self::TrainAutocomplete($station);
        $departures = self::getTrainDepartures($ibnrObject[0]['id'], $when, $travelType);
        $station    = $ibnrObject[0];

        if (empty($station['name'])) {
            return null;
        }
        return ['station' => $station, 'departures' => $departures, 'when' => $when];
    }

    public static function FastTripAccess($departure, $lineName, $number, $when)
    {
        $departuresArray = self::getTrainDepartures($departure, $when);
        foreach ($departuresArray as $departure) {
            if ($departure->line->name === $lineName && $departure->line->fahrtNr == $number) {
                return $departure;
            }
        }
        return null;
    }

    public static function StationByCoordinates($latitude, $longitude)
    {
        $client = new Client(['base_uri' => config('trwl.db_rest')]);
        $response = $client->request('GET', "stops/nearby?latitude=$latitude&longitude=$longitude&results=1");
        $json = json_decode($response->getBody()->getContents());

        if (count($json) === 0) {
            return null;
        }

        return $json[0];
    }

    private static function getTrainDepartures($ibnr, $when='now', $trainType=null)
    {
        $client = new Client(['base_uri' => config('trwl.db_rest')]);
        $trainTypes = array(
            'suburban' => 'false',
            'subway' => 'false',
            'tram' => 'false',
            'bus' => 'false',
            'ferry' => 'false',
            'express' => 'false',
            'regional' => 'false',
        );
        $appendix   = '';

        if ($trainType != null) {
            $trainTypes[$trainType] = 'true';
            $appendix               = '&'.http_build_query($trainTypes);
        }
        $response = $client->request('GET', "stations/$ibnr/departures?when=$when&duration=15" . $appendix);
        $json     = json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        if ($trainType != null && $trainType != 'express') {
            foreach ($json as $key=>$item) {
                if ($item->line->product != $trainType) {
                    unset($json[$key]);
                }
            }
        }
        $json = self::sortByWhenOrScheduledWhen($json);
        return $json;
    }

    // Train with cancelled stops show up in the stationboard sometimes with when == 0.
    // However, they will have a scheduledWhen. This snippet will sort the departures
    // by actualWhen or use scheduledWhen if actual is empty.
    public static function sortByWhenOrScheduledWhen(Array $departuresList): Array
    {
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

    public static function TrainTrip($tripId, $lineName, $start)
    {

        $trip      = self::getHAFAStrip($tripId, $lineName);
        $train     = $trip->getAttributes();
        $stopovers = json_decode($train['stopovers'], true);
        $offset    = self::searchForId($start, $stopovers);
        if ($offset === null) {
            return null;
        }
        $stopovers   = array_slice($stopovers, $offset + 1);
        $destination = TrainStations::where('ibnr', $train['destination'])->first()->name;
        $start       = TrainStations::where('ibnr', $train['origin'])->first()->name;

        return [
            'train' => $train,
            'stopovers' => $stopovers,
            'destination' => $destination,
            'start' => $start
        ];
    }

    public static function CalculateTrainPoints($distance, $category, $departure, $arrival, $delay)
    {
        $now      = time();
        $factorDB = DB::table('pointscalculation')
            ->where([
                        ['type', 'train'],
                        ['transport_type', $category
                        ]])
            ->first();

        $factor = 1;
        if ($factorDB != null) {
            $factor = $factorDB->value;
        }
        $arrivalTime   = ( (is_int($arrival)) ? $arrival : strtotime($arrival)) + $delay;
        $departureTime = ( (is_int($departure)) ? $departure : strtotime($departure)) + $delay;
        $points        = $factor + ceil($distance / 10);

        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        // print_r([$departureTime - 20*60 < $now, $now < $arrivalTime]);
        if (($departureTime - 20*60) < $now && $now < $arrivalTime) {
            return $points;
        }

        /**
         * Reduced points, one hour before departure and after arrival
         *
         *   D-60         D          A          A+60
         *    |           |          |           |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        if (($departureTime - 60*60) < $now && $now < ($arrivalTime + 60*60)) {
            return ceil($points * 0.25);
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return 1;
    }

    public static function TrainCheckin($tripId,
                                        $start,
                                        $destination,
                                        $body,
                                        $user,
                                        $businessCheck,
                                        $tweetCheck,
                                        $tootCheck,
                                        $eventId=0)
    {
        $hafas                 = self::getHAFAStrip($tripId, '')->getAttributes();
        $stopovers             = json_decode($hafas['stopovers'], true);
        $offset1               = self::searchForId($start, $stopovers);
        $offset2               = self::searchForId($destination, $stopovers);
        $polyline              = self::polyline($start, $destination, $hafas['polyline']);
        $originAttributes      = $stopovers[$offset1];
        $destinationAttributes = $stopovers[$offset2];

        $distance = self::distanceCalculation($originAttributes['stop']['location']['latitude'],
                                              $originAttributes['stop']['location']['longitude'],
                                              $destinationAttributes['stop']['location']['latitude'],
                                              $destinationAttributes['stop']['location']['longitude']);
        if ($polyline !== null) {
            $distance = 0.0;
            foreach ($polyline as $key=>$point) {
                if ($key === 0) { continue; }
                $distance += self::distanceCalculation(
                    $point['geometry']['coordinates'][0],
                    $point['geometry']['coordinates'][1],
                    $polyline[$key-1]['geometry']['coordinates'][0],
                    $polyline[$key-1]['geometry']['coordinates'][1]
                );
                // I really don't know what i did here or if there's a better version for this but fuck it,
                // it's 5am and it works.
            }
        }

        $originStation      = self::getTrainStation(
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
        $points             = self::CalculateTrainPoints(
            $distance,
            $hafas['category'],
            $stopovers[$offset1]['departure'],
            $stopovers[$offset2]['arrival'],
            $hafas['delay']
        );

        $status           = new Status();
        $status->body     = $body;
        $status->business = isset($businessCheck) && $businessCheck == 'on';

        $trainCheckin              = new TrainCheckin;
        $trainCheckin->trip_id     = $tripId;
        $trainCheckin->origin      = $originStation->ibnr;
        $trainCheckin->destination = $destinationStation->ibnr;
        $trainCheckin->distance    = $distance;
        $trainCheckin->departure   = self::dateToMySQLEscape($stopovers[$offset1]['departure'],
                                                             $stopovers[$offset1]['departureDelay'] ?? 0);
        $trainCheckin->arrival     = self::dateToMySQLEscape($stopovers[$offset2]['arrival'],
                                                             $stopovers[$offset2]['arrivalDelay'] ?? 0);
        $trainCheckin->delay       = $hafas['delay'];
        $trainCheckin->points      = $points;

        //check if there are colliding checkins
        $overlapDeparture = self::dateToMySQLEscape($trainCheckin->departure, -120);
        $overlapArrival   = self::dateToMySQLEscape($trainCheckin->arrival, 120);

        $overlap = TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where(function($query) use ($overlapArrival, $overlapDeparture) {
            $query->where([['arrival', '>', $overlapDeparture], ['departure', '<', $overlapDeparture]])
                ->orwhere([['arrival', '>', $overlapArrival], ['departure', '<', $overlapArrival]])
                ->orwhere([['departure', '>', $overlapDeparture], ['arrival', '<', $overlapArrival]]);
        })->first();
        if(!empty($overlap)) {
            return ['success' => false, 'overlap' => $overlap];
        }

        // Let's connect our statuses and the events
        $event = null;
        if($eventId != 0) {
            $event = Event::find($eventId);
            if($event === null) {
                abort(404);
            }
            if(Carbon::now()->isBetween(new Carbon($event->begin), new Carbon($event->end))) {
                $status->event_id = $event->id;
            }
        }

        $user->statuses()->save($status)->trainCheckin()->save($trainCheckin);

        $user->train_distance += $trainCheckin->distance;
        $user->train_duration += (strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure)) / 60;
        $user->points         += $trainCheckin->points;

        $user->update();
        if ((isset($tootCheck) || isset($tweetCheck)) && config('trwl.post_social') === true) {
            $postText = trans_choice(
                             'controller.transport.social-post',
                                      preg_match('/\s/', $hafas['linename']),
                                      ['lineName' => $hafas['linename'], 'destination' => $destinationStation->name]
                         );
            if ($event !== null) {
                $postText = trans_choice(
                    'controller.transport.social-post-with-event',
                    preg_match('/\s/', $hafas['linename']),
                    ['lineName' => $hafas['linename'],
                     'destination' => $destinationStation->name,
                     'hashtag' => $event->hashtag]
                );
            }

            $postUrl = url("/status/{$trainCheckin->status_id}");

            if (isset($status->body)) {
                $eventIntercept = "";
                if($event !== null) {
                    $eventIntercept = __('controller.transport.social-post-for') . '#' . $event->hashtag;
                }

                $appendix = " (@ " .
                    $hafas['linename'] .
                    ' ➜ ' .
                    $destinationStation->name .
                    $eventIntercept .
                    ") #NowTräwelling ";

                $appendix_length = strlen($appendix) + 30;
                $postText        = substr($status->body, 0, 280 - $appendix_length);
                if (strlen($postText) != strlen($status->body)) {
                    $postText .= '...';
                }
                $postText .= $appendix;
            }

            if (isset($tootCheck) && $user->socialProfile) {
                try {
                    $mastodonDomain = MastodonServer::where('id', $user->socialProfile->mastodon_server)
                        ->first()->domain;
                    Mastodon::domain($mastodonDomain)->token($user->socialProfile->mastodon_token);
                    Mastodon::createStatus($postText . $postUrl, ['visibility' => 'unlisted']);
                } catch (RequestException $e) {
                    $user->notify(new MastodonNotSent($e->getResponse()->getStatusCode(), $status));
                } catch (\Exception $e) {
                    Log::error($e);
                }
            }
            if (isset($tweetCheck) && $user->socialProfile) {
                try {
                    $connection = new TwitterOAuth(
                        config('trwl.twitter_id'),
                        config('trwl.twitter_secret'),
                        $user->socialProfile->twitter_token,
                        $user->socialProfile->twitter_tokenSecret
                    );
                    // #dbl only works on Twitter.
                    if($user->always_dbl) {
                        $postText .= "#dbl ";
                    }
                    $connection->post(
                        "statuses/update",
                        [
                            "status" => $postText . $postUrl,
                            'lat' => $originStation->latitude,
                            'lon' => $originStation->longitude
                        ]
                    );

                    if($connection->getLastHttpCode() != 200) {
                        $user->notify(new TwitterNotSent($connection->getLastHttpCode(), $status));
                    }
                } catch (\Exception $exception) {
                    Log::error($e);
                    // The Twitter adapter itself won't throw Exceptions, but rather return HTTP codes.
                    // However, we still want to continue if it explodes, thus why not catch exceptions here.
                }
            }
        }

        // check for other people on this train

        $alsoOnThisConnection = TrainCheckin::where([
            ['trip_id', '=', $trainCheckin->trip_id],
            ['status_id', '!=', $status->id],
            ['arrival', '>', $trainCheckin->departure],
            ['departure', '<', $trainCheckin->arrival]
        ])->get()->pluck('status.user')
            ->each(function($t) use ($status, $hafas, $originStation, $destinationStation) {
                $t->notify(new UserJoinedConnection($status->id,
                                                    $hafas['linename'],
                                                    $originStation->name,
                                                    $destinationStation->name));
                return $t;
            });

        return [
            'success' => true,
            'points' => $trainCheckin->points,
            'alsoOnThisConnection' => $alsoOnThisConnection,
            'lineName' => $hafas['linename'],
            'distance' => $trainCheckin->distance,
            'duration' => strtotime($trainCheckin->arrival) - strtotime($trainCheckin->departure),
            'event'    => $event ?? null
        ];
    }

    private static function getHAFAStrip($tripID, $lineName)
    {
        $trip = HafasTrip::where('trip_id', $tripID)->first();

        if ($trip === null) {
            $trip        = new HafasTrip;
            $client      = new Client(['base_uri' => config('trwl.db_rest')]);
            $response    = $client->request('GET', "trips/$tripID?lineName=$lineName&polyline=true");
            $json        = json_decode($response->getBody()->getContents());
            $origin      = self::getTrainStation($json->origin->id,
                                              $json->origin->name,
                                              $json->origin->location->latitude,
                                              $json->origin->location->longitude);
            $destination = self::getTrainStation($json->destination->id,
                                                 $json->destination->name,
                                                 $json->destination->location->latitude,
                                                 $json->destination->location->longitude);
            if ($json->line->name === null) {
                $json->line->name = $json->line->fahrtNr;
            }

            if ($json->line->id === null) {
                $json->line->id = '';
            }
            $polyLineHash = self::getPolylineHash(json_encode($json->polyline));

            $trip->trip_id     = $tripID;
            $trip->category    = $json->line->product;
            $trip->number      = $json->line->id;
            $trip->linename    = $json->line->name;
            $trip->origin      = $origin->ibnr;
            $trip->destination = $destination->ibnr;
            $trip->stopovers   = json_encode($json->stopovers);
            $trip->polyline    = $polyLineHash;
            $trip->departure   = self::dateToMySQLEscape($json->departure ?? $json->scheduledDeparture,
                                                       $json->departureDelay ?? 0);
            $trip->arrival     = self::dateToMySQLEscape($json->arrival ?? $json->scheduledArrival,
                                                     $json->arrivalDelay ?? 0);
            if(isset($json->arrivalDelay)) {
                $trip->delay = $json->arrivalDelay;
            }
            $trip->save();
        }
        return $trip;
    }

    public static function getTrainStation ($ibnr, $name, $latitude, $longitude)
    {
        $station = TrainStations::where('ibnr', $ibnr)->first();
        if ($station === null) {
            $station            = new TrainStations;
            $station->ibnr      = $ibnr;
            $station->name      = $name;
            $station->latitude  = $latitude;
            $station->longitude = $longitude;
            $station->save();
        }
        return $station;
    }

    public static function getPolylineHash($polyline)
    {
        $hash       = md5($polyline);
        $dbPolyline = PolyLine::where('hash', $hash)->first();
        if ($dbPolyline === null) {
            $newPolyline           = new PolyLine;
            $newPolyline->hash     = $hash;
            $newPolyline->polyline = $polyline;
            $newPolyline->save();
        }
        return $hash;
    }

    public static function getLatestArrivals($user)
    {
        return TrainCheckin::with('Status')->whereHas('Status', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function($t) {
            return TrainStations::where("ibnr", $t->destination)->first();
        })->unique()->take(5);
    }

    public static function SetHome($user, $ibnr)
    {
        $client     = new Client(['base_uri' => config('trwl.db_rest')]);
        $response   = $client->request('GET', "locations?query=$ibnr")->getBody()->getContents();
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

    public static function usageByDay(Carbon $date)
    {
        $hafas = HafasTrip::where("created_at", ">=", $date->copy()->startOfDay())
            ->where("created_at", "<=", $date->copy()->endOfDay())
            ->count();

        $returnArray = ["hafas" => $hafas];

        /** Shortcut, wenn eh nichts passiert ist. */
        if($hafas == 0) {
            return $returnArray;
        }

        $polylines                = PolyLine::where("created_at", ">=", $date->copy()->startOfDay())
            ->where("created_at", "<=", $date->copy()->endOfDay())
            ->count();
        $returnArray['polylines'] = $polylines;

        $transportTypes           = ['nationalExpress',
            'national',
            'express',
            'regionalExp',
            'regional',
            'suburban',
            'bus',
            'tram',
            'subway',
            'ferry',];

        $seenCheckins = 0;
        for ($i = 0; $seenCheckins < $hafas && $i < count($transportTypes); $i++) {
            $transport = $transportTypes[$i];

             $returnArray[$transport] = HafasTrip::where("created_at", ">=", $date->copy()->startOfDay())
                ->where("created_at", "<=", $date->copy()->endOfDay())
                ->where('category', '=', $transport)
                ->count();
             $seenCheckins += $returnArray[$transport];
        }

        return $returnArray;
    }
}
