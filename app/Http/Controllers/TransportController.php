<?php

namespace App\Http\Controllers;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Http\Controllers\Backend\Social\TwitterController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Resources\HafasTripResource;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\PolyLine;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Mastodon;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class TransportController extends Controller
{

    /**
     * @param string $query
     *
     * @return Collection
     * @throws HafasException
     * @api v1
     */
    public static function getTrainStationAutocomplete(string $query): Collection {
        return HafasController::getStations($query)->map(function($station) {
            return [
                'ibnr' => $station->ibnr,
                'name' => $station->name
            ];
        });
    }

    /**
     * @param string      $stationName
     * @param Carbon|null $when
     * @param string|null $travelType
     *
     * @return array
     * @throws HafasException
     * @api v1
     */
    #[ArrayShape([
        'station'    => "\App\Models\TrainStation|mixed|null",
        'departures' => Collection::class,
        'times'      => "array"
    ])]
    public static function getDepartures(
        string     $stationName,
        Carbon     $when = null,
        TravelType $travelType = null
    ): array {
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
            when:    $when,
            type:    $travelType
        )->sortBy(function($departure) {
            return $departure->when ?? $departure->plannedWhen;
        });

        return ['station' => $station, 'departures' => $departures->values(), 'times' => $times];
    }

    /**
     * @param $departure
     * @param $lineName
     * @param $number
     * @param $when
     *
     * @return mixed|null
     * @throws GuzzleException
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
     * @param        $ibnr
     * @param string $when
     * @param null   $trainType
     *
     * @return array
     * @throws GuzzleException
     * @deprecated replaced by getDepartures()
     */
    private static function getTrainDepartures($ibnr, string $when = 'now', $trainType = null) {
        $client     = new Client(['base_uri' => config('trwl.db_rest')]);
        $trainTypes = [
            TravelType::SUBURBAN->value => 'false',
            TravelType::SUBWAY->value   => 'false',
            TravelType::TRAM->value     => 'false',
            TravelType::BUS->value      => 'false',
            TravelType::FERRY->value    => 'false',
            TravelType::EXPRESS->value  => 'false',
            TravelType::REGIONAL->value => 'false',
        ];
        $appendix   = '';

        if ($trainType != null) {
            $trainTypes[$trainType] = 'true';
            $appendix               = '&' . http_build_query($trainTypes);
        }
        $response = $client->request('GET', "stations/$ibnr/departures?when=$when&duration=15" . $appendix);
        $json     = json_decode($response->getBody()->getContents());

        //remove express trains in filtered results
        //TODO: Check if $trainType is string or enum
        if ($trainType != null && $trainType != TravelType::EXPRESS->value) {
            foreach ($json as $key => $item) {
                if ($item->line->product != $trainType) {
                    unset($json[$key]);
                }
            }
        }
        return self::sortByWhenOrScheduledWhen($json);
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
     * @param string      $tripId   TripID in Hafas format
     * @param string      $lineName Line Name in Hafas format
     * @param             $start
     * @param Carbon|null $departure
     *
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
     *
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
     * @param                  $tripId
     * @param                  $start
     * @param                  $destination
     * @param                  $body
     * @param                  $user
     * @param Business         $business
     * @param                  $tweetCheck
     * @param                  $tootCheck
     * @param StatusVisibility $visibility
     * @param int              $eventId
     * @param Carbon|null      $departure
     * @param Carbon|null      $arrival
     *
     * @return array
     * @throws CheckInCollisionException
     * @throws HafasException
     * @throws StationNotOnTripException
     * @throws TrainCheckinAlreadyExistException
     * @throws NotConnectedException
     * @deprecated replaced by createTrainCheckin()
     */
    #[ArrayShape([
        'success'              => "bool",
        'statusId'             => "int",
        'points'               => "int",
        'alsoOnThisConnection' => Collection::class,
        'lineName'             => "string",
        'distance'             => "float",
        'duration'             => "float",
        'event'                => "mixed"
    ])]
    public static function TrainCheckin(
        $tripId,
        $start,
        $destination,
        $body,
        $user,
        Business $business,
        $tweetCheck,
        $tootCheck,
        StatusVisibility $visibility,
        $eventId = 0,
        Carbon $departure = null,
        Carbon $arrival = null
    ): array {
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

        $distanceInMeters = 0;
        if ($departureStopover != null && $arrivalStopover != null) {
            $distanceInMeters = GeoController::calculateDistance($hafasTrip, $departureStopover, $arrivalStopover);
        }

        $points = PointsCalculationController::calculatePoints(
            $distanceInMeters,
            $hafasTrip->category,
            Carbon::parse($stopovers[$offset1]['departure']),
            Carbon::parse($stopovers[$offset2]['arrival']),
        )['points'];

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
                                     'business'   => $business,
                                     'visibility' => $visibility
                                 ]);

        $plannedDeparture = Carbon::parse(
            $stopovers[$offset1]['plannedDeparture'] ?? $stopovers[$offset2]['plannedArrival']
        );
        $plannedArrival   = Carbon::parse(
            $stopovers[$offset2]['plannedArrival'] ?? $stopovers[$offset2]['plannedDeparture']
        );

        try {
            $trainCheckin = TrainCheckin::create([
                                                     'status_id'   => $status->id,
                                                     'user_id'     => $user->id,
                                                     'trip_id'     => $tripId,
                                                     'origin'      => $originStation->ibnr,
                                                     'destination' => $destinationStation->ibnr,
                                                     'distance'    => $distanceInMeters,
                                                     'points'      => $points,
                                                     'departure'   => $plannedDeparture,
                                                     'arrival'     => $plannedArrival
                                                 ]);
        } catch (QueryException $exception) {
            $errorCode = $exception->errorInfo[1];
            if ($errorCode == 1062) {
                //duplicate entry
                $status->delete();
                throw new TrainCheckinAlreadyExistException();
            }
        }
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
            MastodonController::postStatus($status);
        }
        if (isset($tweetCheck) && $tweetCheck) {
            TwitterController::postStatus($status);
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
     * Check if there are colliding CheckIns
     *
     * @param User   $user
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection
     * @see https://stackoverflow.com/questions/53697172/laravel-eloquent-query-to-check-overlapping-start-and-end-datetime-fields/53697498
     */
    public static function getOverlappingCheckIns(User $user, Carbon $start, Carbon $end): Collection {
        //increase the tolerance for start and end of collisions
        $start = $start->clone()->addMinutes(10);
        $end   = $end->clone()->subMinutes(10);

        if ($end->isBefore($start)) {
            return collect();
        }

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
     * Get the PolyLine Model from Database
     *
     * @param string $polyline The Polyline as a json string given by hafas
     *
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
     *
     * @param User $user
     * @param int  $maxCount
     *
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
}
