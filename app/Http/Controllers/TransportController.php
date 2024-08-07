<?php

namespace App\Http\Controllers;

use App\Enum\TravelType;
use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Transport\StationController;
use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

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
            $stations = HafasController::getStationsByFuzzyRilIdentifier(rilIdentifier: $query);
        }

        if (!isset($stations) || $stations[0] === null) {
            $stations = HafasController::getStations($query);
        }

        return $stations->map(function(Station $station) {
            return new StationResource($station);
        });
    }

    /**
     * @param string|int      $stationQuery
     * @param Carbon|null     $when
     * @param TravelType|null $travelType
     * @param bool            $localtime
     *
     * @return array
     * @throws HafasException
     * @deprecated use HafasController::getDepartures(...) directly instead (-> less overhead)
     *
     * @api        v1
     */
    #[ArrayShape([
        'station'    => Station::class,
        'departures' => Collection::class,
        'times'      => "array"
    ])]
    public static function getDepartures(
        string|int $stationQuery,
        Carbon     $when = null,
        TravelType $travelType = null,
        bool       $localtime = false
    ): array {
        $station = StationController::lookupStation($stationQuery);

        $when  = $when ?? Carbon::now()->subMinutes(5);
        $times = [
            'now'  => $when,
            'prev' => $when->clone()->subMinutes(15),
            'next' => $when->clone()->addMinutes(15)
        ];

        $departures = HafasController::getDepartures(
            station:   $station,
            when:      $when,
            type:      $travelType,
            localtime: $localtime
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

        $checkInsToCheck = Checkin::with(['Trip.stopovers', 'originStopover.station.names', 'destinationStopover.station.names'])
                                  ->join('statuses', 'statuses.id', '=', 'train_checkins.status_id')
                                  ->where('statuses.user_id', $user->id)
                                  ->where('departure', '>=', $start->clone()->subDays(3))
                                  ->get();

        return $checkInsToCheck->filter(function(Checkin $checkin) use ($start, $end) {
            //use realtime-data or use planned if not available
            $departure = $checkin?->originStopover?->departure ?? $checkin->departure;
            $arrival   = $checkin?->destinationStopover?->arrival ?? $checkin->arrival;

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
}
