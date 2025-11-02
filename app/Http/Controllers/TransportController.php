<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @deprecated Content will be moved to the backend/frontend/API packages soon, please don't add new functions here!
 */
class TransportController extends Controller
{

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
     * @param string      $polyline The Polyline as a json string given by hafas
     * @param string|null $source
     *
     * @return PolyLine
     */
    public static function getPolylineHash(string $polyline, ?string $source = null): PolyLine {
        return PolyLine::updateOrCreate([
                                            'hash' => md5($polyline),
                                        ], [
                                            'polyline' => $polyline,
                                            'source'   => $source,
                                        ]);
    }
}
