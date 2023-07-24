<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\TrainStation;
use App\Models\TrainStopover;

abstract class StatusController extends Controller
{

    /**
     * @param Status $status
     *
     * @return TrainStation|null
     */
    public static function getNextStationForStatus(Status $status): ?TrainStation {
        return $status->trainCheckin->HafasTrip->stopovers
            ->filter(function(TrainStopover $stopover) {
                return $stopover->arrival->isFuture();
            })
            ->sortBy('arrival') //sort by real time and if not available by planned time
            ->first()?->trainStation;
    }
}
