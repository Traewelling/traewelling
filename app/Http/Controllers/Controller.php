<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function searchForId(
        int $stationId,
        array $array,
        Carbon $departure = null,
        Carbon $arrival = null
    ): ?int {
        foreach ($array as $key => $val) {
            if ($val['stop']['id'] != $stationId) {
                continue;
            }
            $stopDeparture        = Carbon::parse($val['plannedDeparture'] ?? $val['departure']);
            $stopArrival          = Carbon::parse($val['plannedArrival'] ?? $val['arrival']);
            $departureTimeMatches = $departure == null || $departure == $stopDeparture;
            $arrivalTimeMatches   = $arrival == null || $arrival == $stopArrival;

            if ($departureTimeMatches && $arrivalTimeMatches) {
                return $key;
            }
        }

        return null;
    }
}
