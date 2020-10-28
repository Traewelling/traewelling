<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\MastodonServer;
use App\Models\PolyLine;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use App\Notifications\TwitterNotSent;
use App\Notifications\MastodonNotSent;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mastodon;

class TransportController extends Controller
{

    public static function TrainAutocomplete($station) {
        $client   = new Client(['base_uri' => config('trwl.db_rest')]);
        $response = $client->request('GET', "stations?query=$station&fuzzy=true");
        if ($response->getBody()->getContents() <= 2) {
            $response = $client->request('GET', "locations?query=$station");
        }
        $json  = $response->getBody()->getContents();
        $array = json_decode($json, true);
        foreach (array_keys($array) as $key) {
            unset($array[$key]['type']);
            unset($array[$key]['location']);
            unset($array[$key]['products']);
            $array[$key]['provider'] = 'train';
        }
        return $array;
    }

    public static function BusAutocomplete($station) {
        $client   = new Client(['base_uri' => config('trwl.flix_rest')]);
        $response = $client->request('GET', "stations/?query=$station");
        $json     = $response->getBody()->getContents();
        $array    = json_decode($json, true);

        foreach (array_keys($array) as $key) {
            unset($array[$key]['relevance']);
            unset($array[$key]['score']);
            unset($array[$key]['weight']);
            unset($array[$key]['type']);
            $array[$key]['provider'] = 'bus';
        }
        return $array;
    }

    public static function TrainStationboard($station, $when = 'now', $travelType = null) {
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

    public static function FastTripAccess($departure, $lineName, $number, $when) {
        $departuresArray = self::getTrainDepartures($departure, $when);
        foreach ($departuresArray as $departure) {
            if ($departure->line->name === $lineName && $departure->line->fahrtNr == $number) {
                return $departure;
            }
        }
        return null;
    }

    public static function StationByCoordinates($latitude, $longitude) {
        $client   = new Client(['base_uri' => config('trwl.db_rest')]);
        $response = $client->request('GET', "stops/nearby?latitude=$latitude&longitude=$longitude&results=1");
        $json     = json_decode($response->getBody()->getContents());

        if (count($json) === 0) {
            return null;
        }

        return $json[0];
    }

    private static function getTrainDepartures($ibnr, $when = 'now', $trainType = null) {
        $client     = new Client(['base_uri' => config('trwl.db_rest')]);
        $trainTypes = [
            'suburban' => 'false',
            'subway'   => 'false',
            'tram'     => 'false',
            'bus'      => 'false',
            'ferry'    => 'false',
            'express'  => 'false',
            'regional' => 'false',
        ];
        $appendix   = '';

        if ($trainType != null) {
            $trainTypes[$trainType] = 'true';
            $appendix               = '&' . http_build_query($trainTypes);
        }
        $response = $client->request('GET', "stations/$ibnr/departures?when=$when&duration=15" . $appendix);
        $json     = json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        if ($trainType != null && $trainType != 'express') {
            foreach ($json as $key => $item) {
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
    public static function sortByWhenOrScheduledWhen(array $departuresList): array {
        uasort($departuresList, function($a, $b) {
            $dateA = $a->when;
            if ($dateA == null) {
                $dateA = $a->scheduledWhen;
            }

            $dateB = $b->when;
            if ($dateB == null) {
                $dateB = $b->scheduledWhen;
            }

            return ($dateA < $dateB) ? -1 : 1;
        });

        return $departuresList;
    }

    /**
     * @param string $tripId TripID in Hafas format
     * @param string $lineName Line Name in Hafas format
     * @param $start
     * @return array|null
     * @throws GuzzleException
     */
    public static function TrainTrip(string $tripId, string $lineName, $start) {

        $hafasTrip = self::getHAFAStrip($tripId, $lineName);
        $stopovers = json_decode($hafasTrip->stopovers, true);
        $offset    = self::searchForId($start, $stopovers);
        if ($offset === null) {
            return null;
        }
        $stopovers   = array_slice($stopovers, $offset + 1);
        $destination = $hafasTrip->destinationStation->name;
        $start       = $hafasTrip->originStation->name;

        return [
            'train'       => $hafasTrip->getAttributes(), //deprecated. use hafasTrip instead
            'hafasTrip'   => $hafasTrip,
            'stopovers'   => $stopovers,
            'destination' => $destination, //deprecated. use hafasTrip->destinationStation instead
            'start'       => $start //deprecated. use hafasTrip->originStation instead
        ];
    }

    public static function CalculateTrainPoints($distance, $category, $departure, $arrival, $delay) {
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
        $arrivalTime   = ((is_int($arrival)) ? $arrival : strtotime($arrival)) + $delay;
        $departureTime = ((is_int($departure)) ? $departure : strtotime($departure)) + $delay;
        $points        = $factor + ceil($distance / 10);

        /**
         * Full points, 20min before the departure time or during the ride
         *   D-20         D                      A
         *    |           |                      |
         * -----------------------------------------> t
         *     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
         */
        // print_r([$departureTime - 20*60 < $now, $now < $arrivalTime]);
        if (($departureTime - 20 * 60) < $now && $now < $arrivalTime) {
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
        if (($departureTime - 60 * 60) < $now && $now < ($arrivalTime + 60 * 60)) {
            return ceil($points * 0.25);
        }

        // Else: Just give me one. It's a point for funsies and the minimal amount of points that you can get.
        return 1;
    }

    /**
     * @param $tripId
     * @param $start
     * @param $destination
     * @param $body
     * @param $user
     * @param $businessCheck
     * @param $tweetCheck
     * @param $tootCheck
     * @param int $eventId
     * @return array
     * @throws CheckInCollisionException
     * @throws GuzzleException
     * @throws HafasException
     */
    public static function TrainCheckin($tripId,
                                        $start,
                                        $destination,
                                        $body,
                                        $user,
                                        $businessCheck,
                                        $tweetCheck,
                                        $tootCheck,
                                        $eventId = 0) {
        $hafasTrip             = self::getHAFAStrip($tripId, '');
        $stopovers             = json_decode($hafasTrip->stopovers, true);
        $offset1               = self::searchForId($start, $stopovers);
        $offset2               = self::searchForId($destination, $stopovers);
        $polyline              = self::polyline($start, $destination, $hafasTrip->polyline);
        $originAttributes      = $stopovers[$offset1];
        $destinationAttributes = $stopovers[$offset2];

        $distance = self::distanceCalculation($originAttributes['stop']['location']['latitude'],
                                              $originAttributes['stop']['location']['longitude'],
                                              $destinationAttributes['stop']['location']['latitude'],
                                              $destinationAttributes['stop']['location']['longitude']);
        if ($polyline !== null) {
            $distance = 0.0;
            foreach ($polyline as $key => $point) {
                if ($key === 0) {
                    continue;
                }
                $distance += self::distanceCalculation(
                    $point['geometry']['coordinates'][0],
                    $point['geometry']['coordinates'][1],
                    $polyline[$key - 1]['geometry']['coordinates'][0],
                    $polyline[$key - 1]['geometry']['coordinates'][1]
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
            $hafasTrip->category,
            $stopovers[$offset1]['departure'],
            $stopovers[$offset2]['arrival'],
            $hafasTrip->delay
        );

        $departure = Carbon::parse($stopovers[$offset1]['departure']);
        $departure->subSeconds($stopovers[$offset1]['departureDelay'] ?? 0);


        if ($stopovers[$offset2]['arrival']) {
            $arrival = Carbon::parse($stopovers[$offset2]['arrival']);
            $arrival->subSeconds($stopovers[$offset2]['arrivalDelay'] ?? 0);
        } else {
            $arrival = Carbon::parse($stopovers[$offset2]['departure']);
            $arrival->subSeconds($stopovers[$offset2]['departureDelay'] ?? 0);
        }

        $overlapping = self::getOverlappingCheckIns($user, $departure, $arrival);
        if ($overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $status = Status::create([
                                     'user_id'  => $user->id,
                                     'body'     => $body,
                                     'business' => isset($businessCheck) && $businessCheck == 'on'
                                 ]);

        $trainCheckin = TrainCheckin::create([
                                                 'status_id'   => $status->id,
                                                 'trip_id'     => $tripId,
                                                 'origin'      => $originStation->ibnr,
                                                 'destination' => $destinationStation->ibnr,
                                                 'distance'    => $distance,
                                                 'delay'       => $hafasTrip->delay,
                                                 'points'      => $points,
                                                 'departure'   => $departure,
                                                 'arrival'     => $arrival
                                             ]);

        $user->load(['statuses']);

        // Let's connect our statuses and the events
        $event = null;
        if ($eventId != 0) {
            $event = Event::find($eventId);
            if ($event === null) {
                abort(404);
            }
            if (Carbon::now()->isBetween(new Carbon($event->begin), new Carbon($event->end))) {
                $status->update([
                                    'event_id' => $event->id
                                ]);
            }
        }

        $user->train_distance += $trainCheckin->distance;
        $user->train_duration += $trainCheckin->duration;
        $user->points         += $trainCheckin->points;

        $user->update();

        if (isset($tootCheck) && $tootCheck == true) {
            self::postMastodon($status);
        }
        if (isset($tweetCheck) && $tweetCheck == true) {
            self::postTwitter($status);
        }

        // check for other people on this train
        foreach ($trainCheckin->alsoOnThisConnection as $otherStatus) {
            $otherStatus->user->notify(new UserJoinedConnection($status->id,
                                                                $status->trainCheckin->HafasTrip->linename,
                                                                $status->trainCheckin->Origin->name,
                                                                $status->trainCheckin->Destination->name));
        }

        return [
            'success'              => true,
            'statusId'             => $status->id,
            'points'               => $trainCheckin->points,
            'alsoOnThisConnection' => $trainCheckin->alsoOnThisConnection,
            'lineName'             => $hafasTrip->linename,
            'distance'             => $trainCheckin->distance,
            'duration'             => $trainCheckin->duration,
            'event'                => $event ?? null
        ];
    }

    /**
     * @param Status $status
     */
    private static function postTwitter(Status $status) {
        if (config('trwl.post_social') !== true) {
            return;
        }
        if ($status->user->socialProfile->twitter_id === null) {
            return;
        }

        try {
            $connection = new TwitterOAuth(
                config('trwl.twitter_id'),
                config('trwl.twitter_secret'),
                $status->user->socialProfile->twitter_token,
                $status->user->socialProfile->twitter_tokenSecret
            );
            #dbl only works on Twitter.
            $socialText = $status->socialText;
            if ($status->user->always_dbl) {
                $socialText .= "#dbl ";
            }
            $socialText .= ' ' . url("/status/{$status->id}");
            $connection->post(
                "statuses/update",
                [
                    "status" => $socialText,
                    'lat'    => $status->trainCheckin->Origin->latitude,
                    'lon'    => $status->trainCheckin->Origin->longitude
                ]
            );

            if ($connection->getLastHttpCode() != 200) {
                $status->user->notify(new TwitterNotSent($connection->getLastHttpCode(), $status));
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            // The Twitter adapter itself won't throw Exceptions, but rather return HTTP codes.
            // However, we still want to continue if it explodes, thus why not catch exceptions here.
        }
    }

    /**
     * @param Status $status
     */
    private static function postMastodon(Status $status) {
        if (config('trwl.post_social') !== true) {
            return;
        }
        if ($status->user->socialProfile->mastodon_server === null) {
            return;
        }

        try {
            $statusText     = $status->socialText . ' ' . url("/status/{$status->id}");
            $mastodonDomain = MastodonServer::find($status->user->socialProfile->mastodon_server)->domain;
            Mastodon::domain($mastodonDomain)->token($status->user->socialProfile->mastodon_token);
            Mastodon::createStatus($statusText, ['visibility' => 'unlisted']);
        } catch (RequestException $e) {
            $status->user->notify(new MastodonNotSent($e->getResponse()->getStatusCode(), $status));
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @param string $tripID
     * @param string $lineName
     * @return HafasTrip
     * @throws GuzzleException|HafasException
     */
    private static function getHAFAStrip(string $tripID, string $lineName): HafasTrip {
        $trip = HafasTrip::where('trip_id', $tripID)->first();
        if ($trip !== null) {
            return $trip;
        }

        $tripClient   = new Client(['base_uri' => config('trwl.db_rest')]);
        $tripResponse = $tripClient->get("trips/$tripID?lineName=$lineName&polyline=true");
        $tripJson     = json_decode($tripResponse->getBody()->getContents());
        $origin       = self::getTrainStation($tripJson->origin->id,
                                              $tripJson->origin->name,
                                              $tripJson->origin->location->latitude,
                                              $tripJson->origin->location->longitude);
        $destination  = self::getTrainStation($tripJson->destination->id,
                                              $tripJson->destination->name,
                                              $tripJson->destination->location->latitude,
                                              $tripJson->destination->location->longitude);
        if ($tripJson->line->name === null) {
            $tripJson->line->name = $tripJson->line->fahrtNr;
        }

        if ($tripJson->line->id === null) {
            $tripJson->line->id = '';
        }

        $departure = Carbon::parse($tripJson->departure ?? $tripJson->scheduledDeparture);
        $departure->subSeconds($tripJson->departureDelay ?? 0);

        $arrival = Carbon::parse($tripJson->arrival ?? $tripJson->scheduledArrival);
        $arrival->subSeconds($tripJson->arrivalDelay ?? 0);

        $polylineHash = self::getPolylineHash(json_encode($tripJson->polyline))->hash;

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
                                             'departure'   => $departure,
                                             'arrival'     => $arrival,
                                             'delay'       => $tripJson->arrivalDelay ?? null
                                         ]);

    }

    /**
     * Get the TrainStation Model from Database
     * @param int $ibnr
     * @param string|null $name
     * @param float|null $latitude
     * @param float|null $longitude
     * @return TrainStation
     * @throws HafasException
     */
    public static function getTrainStation(int $ibnr,
                                           string $name = null,
                                           float $latitude = null,
                                           float $longitude = null): TrainStation {

        if ($name === null || $latitude === null || $longitude === null) {
            $dbTrainStation = TrainStation::where('ibnr', $ibnr)->first();
            if($dbTrainStation !== null) {
                return $dbTrainStation;
            }
            return HafasController::fetchTrainStation($ibnr);
        }
        return TrainStation::updateOrCreate([
                                                'ibnr' => $ibnr
                                            ], [
                                                'name'      => $name,
                                                'latitude'  => $latitude,
                                                'longitude' => $longitude
                                            ]);
    }

    /**
     * Get the PolyLine Model from Database
     * @param string $polyline The Polyline as a json string given by hafas
     * @return PolyLine
     */
    public static function getPolylineHash(string $polyline): PolyLine {
        return PolyLine::updateOrCreate([
                                            'hash' => md5($polyline)
                                        ], [
                                            'polyline' => $polyline
                                        ]);
    }

    /**
     * Get the latest TrainStations the user is arrived.
     * @param User $user
     * @param int $maxCount
     * @return Collection
     */
    public static function getLatestArrivals(User $user, int $maxCount = 5): Collection {
        $user->loadMissing(['statuses', 'statuses.trainCheckIn', 'statuses.trainCheckIn.Destination']);
        return $user->statuses
            ->sortByDesc('trainCheckin.arrival')
            ->map(function($status) {
                return $status->trainCheckIn;
            })->map(function($checkIns) {
                return $checkIns->Destination;
            })->groupBy('ibnr')
            ->map(function($trainStations) {
                return $trainStations->first();
            })->take($maxCount);
    }

    /**
     * @param User $user
     * @param int $ibnr
     * @return TrainStation
     * @throws HafasException
     */
    public static function setHome(User $user, int $ibnr): TrainStation {
        $trainStation = self::getTrainStation($ibnr);

        $user->update([
                          'home_id' => $trainStation->id
                      ]);

        return $trainStation;
    }

    public static function usageByDay(Carbon $date) {
        $hafas = HafasTrip::where("created_at", ">=", $date->copy()->startOfDay())
                          ->where("created_at", "<=", $date->copy()->endOfDay())
                          ->count();

        $returnArray = ["hafas" => $hafas];

        /** Shortcut, wenn eh nichts passiert ist. */
        if ($hafas == 0) {
            return $returnArray;
        }

        $polylines                = PolyLine::where("created_at", ">=", $date->copy()->startOfDay())
                                            ->where("created_at", "<=", $date->copy()->endOfDay())
                                            ->count();
        $returnArray['polylines'] = $polylines;

        $transportTypes = [
            'nationalExpress',
            'national',
            'express',
            'regionalExp',
            'regional',
            'suburban',
            'bus',
            'tram',
            'subway',
            'ferry'
        ];

        $seenCheckins = 0;
        for ($i = 0; $seenCheckins < $hafas && $i < count($transportTypes); $i++) {
            $transport = $transportTypes[$i];

            $returnArray[$transport] = HafasTrip::where("created_at", ">=", $date->copy()->startOfDay())
                                                ->where("created_at", "<=", $date->copy()->endOfDay())
                                                ->where('category', '=', $transport)
                                                ->count();
            $seenCheckins            += $returnArray[$transport];
        }

        return $returnArray;
    }

    /**
     * Check if there are colliding CheckIns
     * @param User $user
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     * @see https://stackoverflow.com/questions/53697172/laravel-eloquent-query-to-check-overlapping-start-and-end-datetime-fields/53697498
     */
    private static function getOverlappingCheckIns(User $user, Carbon $start, Carbon $end): Collection {

        $user->loadMissing(['statuses', 'statuses.trainCheckin']);

        //increase the tolerance for start and end of collisions
        $start = $start->clone()->addMinutes(2);
        $end   = $end->clone()->subMinutes(2);

        return $user->statuses->map(function($status) {
            return $status->trainCheckin;
        })->filter(function($trainCheckIn) use ($start, $end) {
            return ($trainCheckIn->arrival->isAfter($start) && $trainCheckIn->departure->isBefore($end))
                || ($trainCheckIn->arrival->isAfter($end) && $trainCheckIn->departure->isBefore($start))
                || ($trainCheckIn->departure->isAfter($start) && $trainCheckIn->arrival->isBefore($start));
        });
    }
}
