<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Enum\HafasTravelType;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Resources\HafasTripResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\MastodonServer;
use App\Models\PolyLine;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use App\Notifications\MastodonNotSent;
use App\Notifications\TwitterNotSent;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use Mastodon;

class TransportController extends Controller
{

    public static function TrainAutocomplete($station): Collection {
        return HafasController::getStations($station)->map(function($station) {
            return [
                'id'       => $station->ibnr,
                'name'     => $station->name,
                'provider' => 'train'
            ];
        });
    }

    /**
     * @param string $stationName
     * @param Carbon|null $when
     * @param string|null $travelType
     * @return array
     * @throws HafasException
     * @api v1
     */
    #[ArrayShape([
        'station'    => "\App\Models\TrainStation|mixed|null",
        'departures' => "\Illuminate\Support\Collection",
        'times'      => "array"
    ])]
    public static function getDepartures(string $stationName, Carbon $when = null, string $travelType = null): array {
        //first check if the query is a valid DS100 identifier
        if (strlen($stationName) <= 5 && ctype_upper($stationName)) {
            $station = HafasController::getTrainStationByRilIdentifier($stationName);
        }
        //if we cannot find any station by DS100 identifier continue to search normal
        if (empty($station)) {
            $station = HafasController::getStations($stationName)->first();
            if ($station == null) {
                throw new ModelNotFoundException;
            }
        }

        $when  = $when ?? Carbon::now()->subMinutes(5);
        $times = [
            'now'  => $when,
            'prev' => $when->clone()->subMinutes(15),
            'next' => $when->clone()->addMinutes(15)
        ];

        $departures = HafasController::getDepartures(
            station: $station,
            when: $when,
            type: $travelType
        )->sortBy(function($departure) {
            return $departure->when ?? $departure->plannedWhen;
        });

        return ['station' => $station, 'departures' => $departures, 'times' => $times];
    }

    /**
     * @param $departure
     * @param $lineName
     * @param $number
     * @param $when
     * @return mixed|null
     * @deprecated with vue, replaced by frontend logic
     */
    public static function FastTripAccess($departure, $lineName, $number, $when) {
        $departuresArray = self::getTrainDepartures($departure, $when);
        foreach ($departuresArray as $departure) {
            if ($departure->line->name === $lineName && $departure->line->fahrtNr == $number) {
                return $departure;
            }
        }
        return null;
    }

    /**
     * @param $ibnr
     * @param string $when
     * @param null $trainType
     * @return array
     * @throws GuzzleException
     * @deprecated replaced by getDepartures()
     */
    private static function getTrainDepartures($ibnr, $when = 'now', $trainType = null) {
        $client     = new Client(['base_uri' => config('trwl.db_rest')]);
        $trainTypes = [
            TravelType::SUBURBAN => 'false',
            TravelType::SUBWAY   => 'false',
            TravelType::TRAM     => 'false',
            TravelType::BUS      => 'false',
            TravelType::FERRY    => 'false',
            TravelType::EXPRESS  => 'false',
            TravelType::REGIONAL => 'false',
        ];
        $appendix   = '';

        if ($trainType != null) {
            $trainTypes[$trainType] = 'true';
            $appendix               = '&' . http_build_query($trainTypes);
        }
        $response = $client->request('GET', "stations/$ibnr/departures?when=$when&duration=15" . $appendix);
        $json     = json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        if ($trainType != null && $trainType != TravelType::EXPRESS) {
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
     * @param Carbon|null $departure
     * @return array|null
     * @throws HafasException
     * @deprecated replaced by getTrainTrip
     */
    public static function TrainTrip(string $tripId, string $lineName, $start, Carbon $departure = null): ?array {
        $hafasTrip = HafasController::getHafasTrip($tripId, $lineName);
        $hafasTrip->loadMissing(['stopoversNEW', 'originStation', 'destinationStation']);
        $stopovers = json_decode($hafasTrip->stopovers, true);
        $offset    = self::searchForId($start, $stopovers, $departure);
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

    /**
     * @param string $tripId
     * @param string $lineName
     * @param string $start
     * @return HafasTripResource
     * @throws HafasException
     * @throws StationNotOnTripException
     * @api v1
     */
    public static function getTrainTrip(string $tripId, string $lineName, string $start): HafasTripResource {
        $hafasTrip = HafasController::getHafasTrip($tripId, $lineName);
        $hafasTrip->loadMissing(['stopoversNEW', 'originStation', 'destinationStation']);

        if ($hafasTrip->stopoversNEW->where('train_station_id', $start)->count() == 0) {
            throw new StationNotOnTripException();
        }
        return new HafasTripResource($hafasTrip);
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
     * @param $visibility
     * @param int $eventId
     * @param Carbon|null $departure
     * @param Carbon|null $arrival
     * @return array
     * @throws CheckInCollisionException
     * @throws HafasException
     * @throws StationNotOnTripException
     * @deprecated replaced by createTrainCheckin()
     */
    #[ArrayShape([
        'success'              => "bool",
        'statusId'             => "int",
        'points'               => "int",
        'alsoOnThisConnection' => "Illuminate\\Support\\Collection",
        'lineName'             => "string",
        'distance'             => "float",
        'duration'             => "float",
        'event'                => "mixed"
    ])]
    public static function TrainCheckin($tripId,
                                        $start,
                                        $destination,
                                        $body,
                                        $user,
                                        $businessCheck,
                                        $tweetCheck,
                                        $tootCheck,
                                        $visibility,
                                        $eventId = 0,
                                        Carbon $departure = null,
                                        Carbon $arrival = null): array {

        $hafasTrip = HafasTrip::where('trip_id', $tripId)->first();
        $stopovers = json_decode($hafasTrip->stopovers, true);
        $offset1   = self::searchForId($start, $stopovers, $departure);
        $offset2   = self::searchForId($destination, $stopovers, null, $arrival);
        if ($offset1 === null || $offset2 === null) {
            throw new StationNotOnTripException();
        }
        $originAttributes      = $stopovers[$offset1];
        $destinationAttributes = $stopovers[$offset2];

        $originStation      = HafasController::getTrainStation(
            $originAttributes['stop']['id'],
            $originAttributes['stop']['name'],
            $originAttributes['stop']['location']['latitude'],
            $originAttributes['stop']['location']['longitude']
        );
        $destinationStation = HafasController::getTrainStation(
            $destinationAttributes['stop']['id'],
            $destinationAttributes['stop']['name'],
            $destinationAttributes['stop']['location']['latitude'],
            $destinationAttributes['stop']['location']['longitude']
        );

        $departureStopover = $hafasTrip->stopoversNEW
            ->where('train_station_id', $originStation->id)
            ->where('departure_planned', $departure)
            ->first();
        $arrivalStopover   = $hafasTrip->stopoversNEW
            ->where('train_station_id', $destinationStation->id)
            ->where('arrival_planned', $arrival)
            ->first();

        $distance = 0;
        if ($departureStopover != null && $arrivalStopover != null) {
            $distance = GeoController::calculateDistance($hafasTrip, $departureStopover, $arrivalStopover);
        }

        $points = self::CalculateTrainPoints(
            $distance / 1000,
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
                                     'user_id'    => $user->id,
                                     'body'       => $body,
                                     'business'   => $businessCheck,
                                     'visibility' => $visibility
                                 ]);

        $plannedDeparture = Carbon::parse(
            $stopovers[$offset1]['plannedDeparture'] ?? $stopovers[$offset2]['plannedArrival']
        );
        $plannedArrival   = Carbon::parse(
            $stopovers[$offset2]['plannedArrival'] ?? $stopovers[$offset2]['plannedDeparture']
        );

        $trainCheckin = TrainCheckin::create([
                                                 'status_id'   => $status->id,
                                                 'trip_id'     => $tripId,
                                                 'origin'      => $originStation->ibnr,
                                                 'destination' => $destinationStation->ibnr,
                                                 'distance'    => $distance,
                                                 'delay'       => $hafasTrip->delay,
                                                 'points'      => $points,
                                                 'departure'   => $plannedDeparture,
                                                 'arrival'     => $plannedArrival
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

        if (isset($tootCheck) && $tootCheck) {
            self::postMastodon($status);
        }
        if (isset($tweetCheck) && $tweetCheck) {
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
            'distance'             => $trainCheckin->distance / 1000,
            'duration'             => $trainCheckin->duration,
            'event'                => $event ?? null
        ];
    }

    public static function CalculateTrainPoints($distance, $category, $departure, $arrival, $delay): int {
        $now = time();

        $factor = 1;
        if (in_array($category, HafasTravelType::getList())) {
            $factor = config('trwl.base_points.train.' . $category, 1);
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
     * Check if there are colliding CheckIns
     * @param User $user
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     * @see https://stackoverflow.com/questions/53697172/laravel-eloquent-query-to-check-overlapping-start-and-end-datetime-fields/53697498
     */
    private static function getOverlappingCheckIns(User $user, Carbon $start, Carbon $end): Collection {
        //increase the tolerance for start and end of collisions
        $start = $start->clone()->addMinutes(2);
        $end   = $end->clone()->subMinutes(2);

        $checkInsToCheck = TrainCheckin::with(['HafasTrip.stopoversNEW', 'Origin', 'Destination'])
                                       ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
                                       ->where('statuses.user_id', $user->id)
                                       ->where('departure', '>=', $start->clone()->subDays(3)->toIso8601String())
                                       ->get();

        return $checkInsToCheck->filter(function($trainCheckIn) use ($start, $end) {
            //use realtime-data or use planned if not available
            $departure = $trainCheckIn?->origin_stopover?->departure ?? $trainCheckIn->departure;
            $arrival   = $trainCheckIn?->destination_stopover?->arrival ?? $trainCheckIn->arrival;

            return (
                       $arrival->isAfter($start) &&
                       $departure->isBefore($end)
                   ) || (
                       $arrival->isAfter($end) &&
                       $departure->isBefore($start)
                   ) || (
                       $departure->isAfter($start) &&
                       $arrival->isBefore($start)
                   );
        });
    }

    /**
     * @param Status $status
     */
    public static function postMastodon(Status $status): void {
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
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @param Status $status
     */
    public static function postTwitter(Status $status): void {
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
        } catch (Exception $exception) {
            Log::error($exception);
            // The Twitter adapter itself won't throw Exceptions, but rather return HTTP codes.
            // However, we still want to continue if it explodes, thus why not catch exceptions here.
        }
    }

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws ModelNotFoundException
     * @api v1
     */
    public static function createTrainCheckin(
        Status $status,
        HafasTrip $trip,
        int $entryStop,
        int $exitStop,
        Carbon $departure = null,
        Carbon $arrival = null,
        bool $ibnr = false
    ): array {
        $trip->load('stopoversNEW');

        if (!$ibnr) {
            $firstStop = $trip->stopoversNEW->where('train_station_id', $entryStop)
                                            ->where('departure_planned', $departure)->first();

            $lastStop = $trip->stopoversNEW->where('train_station_id', $exitStop)
                                           ->where('arrival_planned', $arrival)->first();
        } else {
            $firstStop = $trip->stopoversNEW->where('trainStation.ibnr', $entryStop)
                                            ->where('departure_planned', $departure)->first();

            $lastStop = $trip->stopoversNEW->where('trainStation.ibnr', $exitStop)
                                           ->where('arrival_planned', $arrival)->first();
        }

        if (empty($firstStop) || empty($lastStop)) {
            throw new StationNotOnTripException();
        }

        $overlapping = self::getOverlappingCheckIns(
            user: Auth::user(),
            start: $firstStop->departure,
            end: $lastStop->arrival
        );
        if ($overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $distance = GeoController::calculateDistance(hafasTrip: $trip, origin: $firstStop, destination: $lastStop);

        $points = self::CalculateTrainPoints(
            distance: $distance,
            category: $trip->category,
            departure: $firstStop->planned_departure,
            arrival: $lastStop->planned_arrival,
            delay: $trip->delay
        );

        $trainCheckin = TrainCheckin::create([
                                                 'status_id'   => $status->id,
                                                 'trip_id'     => $trip->trip_id,
                                                 'origin'      => $firstStop->trainStation->ibnr,
                                                 'destination' => $lastStop->trainStation->ibnr,
                                                 'distance'    => $distance,
                                                 'delay'       => $trip->delay,
                                                 'points'      => $points,
                                                 'departure'   => $firstStop->departure_planned,
                                                 'arrival'     => $lastStop->arrival_planned
                                             ]);
        foreach ($trainCheckin->alsoOnThisConnection as $otherStatus) {
            if ($otherStatus?->user) {
                $otherStatus->user->notify(new UserJoinedConnection(
                                               statusId: $status->id,
                                               linename: $trip->linename,
                                               origin: $firstStop->name,
                                               destination: $lastStop->name
                                           ));
            }
        }

        return [
            'status'               => new StatusResource($status),
            'alsoOnThisConnection' => $trainCheckin->alsoOnThisConnection
        ];
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
        $user->loadMissing(['statuses.trainCheckIn.Destination']);
        return $user->statuses
            ->map(function($status) {
                return $status->trainCheckIn;
            })
            ->sortByDesc('arrival')
            ->map(function($checkIn) {
                return $checkIn->Destination;
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
     * @deprecated replaced by setTrainHome()
     */
    public static function setHome(User $user, int $ibnr): TrainStation {
        $trainStation = HafasController::getTrainStation($ibnr);

        $user->update([
                          'home_id' => $trainStation->id
                      ]);

        return $trainStation;
    }

    /**
     * @param User $user
     * @param string $stationName
     * @return TrainStation
     * @throws HafasException
     * @api v1
     */
    public static function setTrainHome(User $user, string $stationName): TrainStation {
        $trainStation = HafasController::getStations(query: $stationName)->first();
        if ($trainStation == null) {
            throw new ModelNotFoundException;
        }

        $user->update([
                          'home_id' => $trainStation->id
                      ]);
        return $trainStation;
    }
}
