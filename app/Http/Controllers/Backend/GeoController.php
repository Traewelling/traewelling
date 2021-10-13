<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HafasTrip;
use App\Models\TrainStopover;

abstract class GeoController extends Controller
{
    public static function calculateDistance(
        HafasTrip     $hafasTrip,
        TrainStopover $origin,
        TrainStopover $destination
    ): int {
        if ($hafasTrip->polyline == null || $hafasTrip?->polyline?->polyline == null) {
            return self::calculateDistanceByStopovers($hafasTrip, $origin, $destination);
        }

        $allFeatures = json_decode($hafasTrip->polyline->polyline);

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($allFeatures->features as $key => $data) {
            if (!isset($data->properties->id)) {
                continue;
            }
            if ($origin->trainStation->ibnr == $data->properties->id && $originIndex == null) {
                $originIndex = $key;
            }
            if ($destination->trainStation->ibnr == $data->properties->id) {
                $destinationIndex = $key;
            }
        }

        if ($destinationIndex < $originIndex) {
            //Some polyline are inverted, so switch the keys...
            $temp             = $destinationIndex;
            $destinationIndex = $originIndex;
            $originIndex      = $temp;
        }

        $slicedFeatures = array_slice($allFeatures->features, $originIndex, $destinationIndex - $originIndex + 1);

        $distance     = 0;
        $lastStopover = null;
        foreach ($slicedFeatures as $stopover) {
            if ($lastStopover != null) {
                $distance += self::calculateDistanceBetweenCoordinates(
                    latitudeA:  $lastStopover->geometry->coordinates[1],
                    longitudeA: $lastStopover->geometry->coordinates[0],
                    latitudeB:  $stopover->geometry->coordinates[1],
                    longitudeB: $stopover->geometry->coordinates[0]
                );
            }

            $lastStopover = $stopover;
        }
        return $distance;
    }

    /**
     * Fallback calculation if no polyline is given. Calculates the length using the coordinates of the stations.
     * @param HafasTrip $hafasTrip
     * @param TrainStopover $origin
     * @param TrainStopover $destination
     * @return float
     */
    private static function calculateDistanceByStopovers(
        HafasTrip     $hafasTrip,
        TrainStopover $origin,
        TrainStopover $destination
    ): int {
        $stopovers                = $hafasTrip->stopoversNEW->sortBy('departure');
        $originStopoverIndex      = $stopovers->search(function($item) use ($origin) {
            return $item->id == $origin->id;
        });
        $destinationStopoverIndex = $stopovers->search(function($item) use ($destination) {
            return $item->id == $destination->id;
        });

        $stopovers = $stopovers->slice($originStopoverIndex, $destinationStopoverIndex - $originStopoverIndex + 1);

        $distance     = 0;
        $lastStopover = null;
        foreach ($stopovers as $stopover) {
            if ($lastStopover == null) {
                $lastStopover = $stopover;
                continue;
            }
            $distance     += self::calculateDistanceBetweenCoordinates(
                latitudeA:  $lastStopover->trainStation->latitude,
                longitudeA: $lastStopover->trainStation->longitude,
                latitudeB:  $stopover->trainStation->latitude,
                longitudeB: $stopover->trainStation->longitude
            );
            $lastStopover = $stopover;
        }
        return $distance;
    }

    public static function calculateDistanceBetweenCoordinates(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB
    ): int {
        if ($longitudeA === $longitudeB && $latitudeA === $latitudeB) {
            return 0.0;
        }

        $equatorialRadiusInMeters = 6378137;

        $pi       = pi();
        $latA     = $latitudeA / 180 * $pi;
        $lonA     = $longitudeA / 180 * $pi;
        $latB     = $latitudeB / 180 * $pi;
        $lonB     = $longitudeB / 180 * $pi;
        $distance = acos(sin($latA) * sin($latB) + cos($latA) * cos($latB) * cos($lonB - $lonA))
                    * $equatorialRadiusInMeters;

        return round($distance);
    }
}
