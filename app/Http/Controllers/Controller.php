<?php

namespace App\Http\Controllers;

use App\PolyLine;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function searchForId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['stop']['id'] === $id) {
                return $key;
            }
        }
        return null;
    }

    public static function polyline($idStart, $idStop, $hash) {
        $polyline = PolyLine::where('hash', $hash)->first();
        if ($polyline === null) {
            return null;
        }
        $polyline = json_decode($polyline->polyline, true)['features'];
        $offset = [];
        foreach ($polyline as $key => $val) {
            if (isset($val['properties']['id']) && $val['properties']['id'] === $idStart) {
                 $offset[0] = $key;
            }
            if (isset($val['properties']['id']) && $val['properties']['id'] === $idStop) {
                $offset[1] = $key;
            }
        }
        if ($offset[1] != key(array_slice($polyline, -1, 1, true))) {
            $offset[1] = $offset[1] - sizeof($polyline) + 1;
        }

        $polyline = array_slice($polyline, $offset[0], $offset[1]);

        return $polyline;
    }

    public static function distanceCalculation($longitude_a, $latitude_a, $longitude_b, $latitude_b, $decimals = 3) {
        if ($longitude_a === $longitude_b && $latitude_a === $latitude_b) {
            return 0.0;
        }

        $EQUATORIAL_RADIUS_KM = 6378.137;

        $pi = pi();
        $latA = $latitude_a  / 180 * $pi;
        $lonA = $longitude_a / 180 * $pi;
        $latB = $latitude_b  / 180 * $pi;
        $lonB = $longitude_b / 180 * $pi;
        $distance = acos(sin($latA) * sin($latB) + cos($latA) * cos($latB) * cos($lonB - $lonA)) * $EQUATORIAL_RADIUS_KM;

        return round($distance, $decimals);
    }

}
