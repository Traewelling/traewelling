<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Station;
use App\Models\Stopover;

abstract class StatusController extends Controller
{

    /**
     * @param Status $status
     *
     * @return Station|null
     */
    public static function getNextStationForStatus(Status $status): ?Station {
        return $status->checkin->trip->stopovers
            ->filter(function(Stopover $stopover) {
                return $stopover->arrival->isFuture();
            })
            ->sortBy('arrival') //sort by real time and if not available by planned time
            ->first()?->station;
    }
}
