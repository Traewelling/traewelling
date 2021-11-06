<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Exceptions\HafasException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\TrainStation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class StationController extends Controller
{

    /**
     * @throws HafasException
     * @throws ModelNotFoundException
     */
    public static function lookupStation(string|int $query): TrainStation {
        //Lookup by station id
        if (is_numeric($query)) {
            $station = TrainStation::find($query);
            if ($station !== null) {
                return $station;
            }
        }

        //Lookup by ril identifier
        if (!is_numeric($query) && strlen($query) <= 5 && ctype_upper($query)) {
            $station = HafasController::getTrainStationByRilIdentifier($query);
            if ($station !== null) {
                return $station;
            }
        }

        //Lookup HAFAS
        $station = HafasController::getStations(query: $query, results: 1)->first();
        if ($station !== null) {
            return $station;
        }

        throw new ModelNotFoundException;
    }
}
