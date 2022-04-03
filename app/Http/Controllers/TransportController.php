<?php

namespace App\Http\Controllers;

use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Resources\HafasTripResource;
use App\Models\PolyLine;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
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
        if (!is_numeric($query) && strlen($query) <= 5 && ctype_upper($query)) {
            $station = HafasController::getTrainStationByRilIdentifier($query);
            if ($station !== null) {
                return collect([
                                   'ibnr'          => $station->ibnr,
                                   'rilIdentifier' => $station->rilIdentifier,
                                   'name'          => $station->name
                               ]);
            }
        }

        return HafasController::getStations($query)->map(function(TrainStation $station) {
            return [
                'ibnr'          => $station->ibnr,
                'rilIdentifier' => $station->rilIdentifier,
                'name'          => $station->name
            ];
        });
    }

    /**
     * @param string|int      $stationQuery
     * @param Carbon|null     $when
     * @param TravelType|null $travelType
     *
     * @return array
     * @throws HafasException
     * @api v1
     */
    #[ArrayShape([
        'station'    => TrainStation::class,
        'departures' => Collection::class,
        'times'      => "array"
    ])]
    public static function getDepartures(
        string|int $stationQuery,
        Carbon     $when = null,
        TravelType $travelType = null
    ): array {
        $station = StationController::lookupStation($stationQuery);

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
     * @deprecated use StationController:getLatestArrivals(...) instead.
     */
    public static function getLatestArrivals(User $user, int $maxCount = 5): Collection {
        return StationController::getLatestArrivals($user, $maxCount);
    }
}
