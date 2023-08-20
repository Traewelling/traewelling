<?php

namespace App\Http\Controllers\Backend\Support;

use App\Dto\Coordinate;
use App\Dto\LivePointDto;
use App\Http\Controllers\Backend\GeoController;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainStopover;
use App\Objects\LineSegment;
use Carbon\Carbon;

class LocationController
{
    private HafasTrip $hafasTrip;
    private ?int $statusId;

    public function __construct(HafasTrip $hafasTrip, int $statusId = null) {
        $this->hafasTrip = $hafasTrip;
        $this->statusId = $statusId;
    }

    public static function forStatus(Status $status): LocationController {
        return new self($status->trainCheckin->HafasTrip, $status->id);
    }

    private function filterStopOversFromStatus(): ?array {
        $stopovers    = $this->hafasTrip->stopovers;
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
        $hafasTrip    = $this->hafasTrip;
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
                $this->statusId
            );
        }

        $now        = Carbon::now()->timestamp;
        $percentage = ($now - $newStopovers[0]->departure->timestamp)
                      / ($newStopovers[1]->arrival->timestamp - $newStopovers[0]->departure->timestamp);
        $polyline   = GeoController::getPolylineBetween(
            $this->hafasTrip,
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
            $this->statusId,
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

    private function getPolylineBetween(TrainStopover $origin, TrainStopover $destination, bool $preservedKeys = true) {
        $hafasTrip->loadMissing(['stopovers.trainStation']);
        $geoJson  = self::getPolylineWithTimestamps($hafasTrip);
        $features = $geoJson->features;

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($features as $key => $data) {
            if (!isset($data->properties->id)) {
                continue;
            }

            if ($originIndex === null
                && $origin->trainStation->ibnr === (int) $data->properties->id
                && isset($data->properties->departure_planned) //Important for ring lines!
                && $origin->departure_planned->is($data->properties->departure_planned) //Important for ring lines!
            ) {
                $originIndex = $key;
            }

            if ($destinationIndex === null
                && $destination->trainStation->ibnr === (int) $data->properties->id
                && isset($data->properties->arrival_planned) //Important for ring lines!
                && $destination->arrival_planned->is($data->properties->arrival_planned) //Important for ring lines!
            ) {
                $destinationIndex = $key;
            }
        }
        if (is_array($features)) { // object is a rarely contentless stdClass if no features in the GeoJSON
            $slicedFeatures    = array_slice($features, $originIndex, $destinationIndex - $originIndex + 1, $preserveKeys);
            $geoJson->features = $slicedFeatures;
        }
        return $geoJson;

    }
}
