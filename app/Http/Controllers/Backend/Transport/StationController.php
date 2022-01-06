<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Exceptions\HafasException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Models\TrainStation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class StationController extends Controller
{

    /**
     * @throws HafasException
     * @throws ModelNotFoundException
     */
    public static function lookupStation(string|int $query): TrainStation {
        //Lookup by station ibnr
        if (is_numeric($query)) {
            $station = TrainStation::where('ibnr', $query)->first();
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

    /**
     * Get the latest TrainStations the user is arrived.
     *
     * @param User $user
     * @param int  $maxCount
     *
     * @return Collection
     */
    public static function getLatestArrivals(User $user, int $maxCount = 5): Collection {
        return TrainStation::join('train_checkins', 'train_checkins.destination', '=', 'train_stations.ibnr')
                           ->where('train_checkins.user_id', $user->id)
                           ->groupBy(['train_stations.id', 'train_stations.ibnr', 'train_stations.name'])
                           ->select(['train_stations.id', 'train_stations.ibnr', 'train_stations.name'])
                           ->orderByDesc(DB::raw('MAX(train_checkins.arrival)'))
                           ->limit($maxCount)
                           ->get();
    }
}
