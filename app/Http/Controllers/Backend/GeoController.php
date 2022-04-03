<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\HafasTrip;
use App\Models\TrainStopover;
use JsonException;
use stdClass;

abstract class GeoController extends Controller
{

    /**
     * Timestamps in the GeoJSON are required to calculate the distance of ring lines correctly.
     *
     * @param HafasTrip $hafasTrip
     *
     * @return mixed
     * @throws JsonException
     */
    private static function getPolylineWithTimestamps(HafasTrip $hafasTrip): stdClass {
        $geoJsonObj = json_decode($hafasTrip->polyline->polyline, false, 512, JSON_THROW_ON_ERROR);
        $stopovers  = $hafasTrip->stopoversNEW;
        foreach ($geoJsonObj->features as $polylineFeature) {
            $stopover                                       = $stopovers->where('trainStation.ibnr', $polylineFeature->properties->id)
                                                                        ->whereNull('passed')
                                                                        ->first();
            $stopover->passed                               = true;
            $polylineFeature->properties->departure_planned = $stopover->departure_planned?->clone();
            $polylineFeature->properties->arrival_planned   = $stopover->arrival_planned?->clone();
        }
        return $geoJsonObj;
    }

    public static function calculateDistance(
        HafasTrip     $hafasTrip,
        TrainStopover $origin,
        TrainStopover $destination
    ): int {
        if ($hafasTrip->polyline === null || $hafasTrip?->polyline?->polyline === null) {
            return self::calculateDistanceByStopovers($hafasTrip, $origin, $destination);
        }

        $allFeatures = self::getPolylineWithTimestamps($hafasTrip);

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($allFeatures->features as $key => $data) {
            if (!isset($data->properties->id)) {
                continue;
            }
            if ($originIndex === null
                && $origin->trainStation->ibnr === (int) $data->properties->id
                && $origin->departure_planned->is($data->properties->departure_planned) //Important for ring lines!
            ) {
                $originIndex = $key;
            }
            if ($destinationIndex === null
                && $destination->trainStation->ibnr === (int) $data->properties->id
                && $destination->arrival_planned->is($data->properties->arrival_planned) //Important for ring lines!
            ) {
                $destinationIndex = $key;
            }
        }

        if ($destinationIndex < $originIndex) { //TODO: should not happen... remove?
            //Some polyline are inverted, so switch the keys...
            $temp             = $destinationIndex;
            $destinationIndex = $originIndex;
            $originIndex      = $temp;
        }

        $slicedFeatures = array_slice($allFeatures->features, $originIndex, $destinationIndex - $originIndex + 1);

        $distance     = 0;
        $lastStopover = null;
        foreach ($slicedFeatures as $stopover) {
            if ($lastStopover !== null) {
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
     *
     * @param HafasTrip     $hafasTrip
     * @param TrainStopover $origin
     * @param TrainStopover $destination
     *
     * @return int
     */
    private static function calculateDistanceByStopovers(
        HafasTrip     $hafasTrip,
        TrainStopover $origin,
        TrainStopover $destination
    ): int {
        $stopovers                = $hafasTrip->stopoversNEW->sortBy('departure');
        $originStopoverIndex      = $stopovers->search(function($item) use ($origin) {
            return $item->is($origin);
        });
        $destinationStopoverIndex = $stopovers->search(function($item) use ($destination) {
            return $item->is($destination);
        });

        $stopovers = $stopovers->slice($originStopoverIndex, $destinationStopoverIndex - $originStopoverIndex + 1);

        $distance     = 0;
        $lastStopover = null;
        foreach ($stopovers as $stopover) {
            if ($lastStopover === null) {
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

        $latA     = $latitudeA / 180 * M_PI;
        $lonA     = $longitudeA / 180 * M_PI;
        $latB     = $latitudeB / 180 * M_PI;
        $lonB     = $longitudeB / 180 * M_PI;
        $distance = acos(sin($latA) * sin($latB) + cos($latA) * cos($latB) * cos($lonB - $lonA))
                    * $equatorialRadiusInMeters;

        return round($distance);
    }
}
