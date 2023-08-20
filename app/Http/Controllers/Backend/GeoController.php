<?php

namespace App\Http\Controllers\Backend;

use App\Dto\Coordinate;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Controller;
use App\Models\HafasTrip;
use App\Models\TrainStopover;
use App\Objects\LineSegment;

abstract class GeoController extends Controller
{
    public static function calculateDistance(
        HafasTrip     $hafasTrip,
        TrainStopover $origin,
        TrainStopover $destination
    ): int {
        if (
            $hafasTrip->polyline === null ||
            $hafasTrip->polyline?->polyline === null ||
            strlen($hafasTrip->polyline?->polyline) < 10
        ) {
            return self::calculateDistanceByStopovers($hafasTrip, $origin, $destination);
        }
        $geoJson      = (new LocationController($hafasTrip))->getPolylineBetween($origin, $destination);
        $distance     = 0;
        $lastStopover = null;
        foreach ($geoJson->features as $stopover) {
            if ($lastStopover !== null) {
                $distance += (new LineSegment(
                    new Coordinate($lastStopover->geometry->coordinates[1], $lastStopover->geometry->coordinates[0]),
                    new Coordinate($stopover->geometry->coordinates[1], $stopover->geometry->coordinates[0])
                ))->calculateDistance();
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
        $stopovers                = $hafasTrip->stopovers->sortBy('departure');
        $originStopoverIndex      = $stopovers->search(function ($item) use ($origin) {
            return $item->is($origin);
        });
        $destinationStopoverIndex = $stopovers->search(function ($item) use ($destination) {
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
            $distance     += (new LineSegment(
                new Coordinate($lastStopover->trainStation->latitude, $lastStopover->trainStation->longitude),
                new Coordinate($stopover->trainStation->latitude, $stopover->trainStation->longitude)
            ))->calculateDistance();
            $lastStopover = $stopover;
        }
        return $distance;
    }
}
