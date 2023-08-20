<?php

namespace App\Http\Controllers\Backend\Support;

use App\Dto\Coordinate;
use App\Dto\LivePointDto;
use App\Http\Controllers\Backend\GeoController;
use App\Models\Status;
use App\Objects\LineSegment;
use Carbon\Carbon;

class LocationController
{
    private Status $status;

    public function __construct(Status $status) {
        $this->status = $status;
    }

    private function filterStopOversFromStatus(): ?array {
        $stopovers    = $this->status->trainCheckin->HafasTrip->stopovers;
        $newStopovers = null;
        foreach ($stopovers as $key => $stopover) {
            if ($stopover->departure->isFuture()) {
                if ($stopover->arrival->isPast()) {
                    $newStopovers = [$stopover];
                    break;
                }
                $newStopovers = [
                    $stopovers[$key - 1],
                    $stopover
                ];
                break;
            }
        }

        return $newStopovers;
    }

    public function calculateLivePosition(): ?LivePointDto {
        $hafasTrip    = $this->status->trainCheckin->HafasTrip;
        $newStopovers = $this->filterStopOversFromStatus();

        if (!$newStopovers) {
            return null;
        }
        if (count($newStopovers) === 1) {

            return new LivePointDto(
                (new Coordinate(
                    $newStopovers[0]->trainStation->longitude,
                    $newStopovers[0]->trainStation->latitude
                )),
                null,
                $newStopovers[0]->arrival->timestamp,
                $newStopovers[0]->departure->timestamp,
                $hafasTrip->linename,
                $this->status->id
            );
        }

        $now        = Carbon::now()->timestamp;
        $percentage = ($now - $newStopovers[0]->departure->timestamp)
                      / ($newStopovers[1]->arrival->timestamp - $newStopovers[0]->departure->timestamp);
        $polyline   = GeoController::getPolylineBetween(
            $this->status->trainCheckin->HafasTrip,
            $newStopovers[0], $newStopovers[1],
            false
        );


        $meters      = $this->getDistanceFromGeoJson($polyline) * $percentage;
        $recentPoint = null;
        $distance    = 0;
        foreach ($polyline->features as $key => $point) {
            $point = Coordinate::fromGeoJson($point);
            if ($recentPoint !== null && $point !== null) {
                $lineSegment = new LineSegment($recentPoint, $point);

                $distance += $lineSegment->calculateDistance();
                if ($distance >= $meters) {

                    break;
                }
            }

            $recentPoint = $point ?? $recentPoint;
        }


        $pointS = $lineSegment->interpolatePoint($meters / $distance);

        $polyline->features = array_slice($polyline->features, $key);
        array_unshift($polyline->features, $pointS->toGeoJsonPoint());

        return new LivePointDto(
            null,
            $polyline,
            $newStopovers[1]->arrival->timestamp,
            $newStopovers[1]->departure->timestamp,
            $hafasTrip->linename,
            $this->status->id,
        );
    }

    private function getDistanceFromGeoJson(\stdClass $geoJson): int {
        $fullD        = 0;
        $lastStopover = null;
        foreach ($geoJson->features as $stopover) {
            $stopover = Coordinate::fromGeoJson($stopover);
            if ($lastStopover === null) {
                $lastStopover = $stopover;
                continue;
            }
            $fullD        += (new LineSegment($lastStopover, $stopover))->calculateDistance();
            $lastStopover = $stopover;
        }

        return $fullD;
    }
}
