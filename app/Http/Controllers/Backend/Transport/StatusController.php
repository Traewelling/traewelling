<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Controller;
use App\Models\Status;

abstract class StatusController extends Controller
{
    public static function getCurrentPosition(Status $status): ?array {
        if ($status->trainCheckin->departure->isAfter(now()) || $status->trainCheckin->arrival->isBefore(now())) {
            return null;
        }

        $stationBefore = $status->trainCheckin->HafasTrip->stopoversNEW->where('departure', '<', now())->last();
        $stationNext   = $status->trainCheckin->HafasTrip->stopoversNEW->where('arrival', '>', now())->first();

        if ($stationBefore === null || $stationNext === null) {
            return null;
        }

        $polylineBetween = GeoController::getPolylineBetween($status->trainCheckin->HafasTrip, $stationBefore, $stationNext);

        $lastDeparture = $stationBefore->departure;
        $nextArrival   = $stationNext->arrival;
        $minutes       = $lastDeparture->diffInMinutes($nextArrival);
        $minutesPassed = $lastDeparture->diffInMinutes(now());
        $percentage    = $minutesPassed / $minutes;

        $distanceBetweenStations = GeoController::calculateDistance($status->trainCheckin->HafasTrip, $stationBefore, $stationNext);

        $passedMeters = $distanceBetweenStations * $percentage;

        $lastStopover = null;
        $distance     = 0;
        foreach ($polylineBetween->features as $stopover) {
            if ($lastStopover !== null) {
                $distance += GeoController::calculateDistanceBetweenCoordinates(
                    latitudeA:  $lastStopover->geometry->coordinates[1],
                    longitudeA: $lastStopover->geometry->coordinates[0],
                    latitudeB:  $stopover->geometry->coordinates[1],
                    longitudeB: $stopover->geometry->coordinates[0]
                );
            }

            if ($distance >= $passedMeters && $lastStopover !== null) {
                $percentageBetweenStations = ($distance - $passedMeters) / $distanceBetweenStations;
                $latitude                  = $lastStopover->geometry->coordinates[1] + ($stopover->geometry->coordinates[1] - $lastStopover->geometry->coordinates[1]) * $percentageBetweenStations;
                $longitude                 = $lastStopover->geometry->coordinates[0] + ($stopover->geometry->coordinates[0] - $lastStopover->geometry->coordinates[0]) * $percentageBetweenStations;
                return [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                ];
            }

            $lastStopover = $stopover;
        }
        return null;
    }
}
