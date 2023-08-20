<?php

namespace App\Http\Controllers\Backend\Support;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Dto\LivePointDto;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStopover;
use App\Objects\LineSegment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use JsonException;
use stdClass;

class LocationController
{
    private HafasTrip      $hafasTrip;
    private ?TrainStopover $origin;
    private ?TrainStopover $destination;
    private ?int           $statusId;

    public function __construct(
        HafasTrip     $hafasTrip,
        TrainStopover $origin = null,
        TrainStopover $destination = null,
        int           $statusId = null
    ) {
        $this->hafasTrip   = $hafasTrip;
        $this->origin      = $origin;
        $this->destination = $destination;
        $this->statusId    = $statusId;
    }

    public static function forStatus(Status $status): LocationController {
        return new self($status->trainCheckin->HafasTrip, null, null, $status->id);
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

    /**
     * @throws JsonException
     */
    public function calculateLivePosition(): ?LivePointDto {
        $hafasTrip    = $this->hafasTrip;
        $newStopovers = $this->filterStopOversFromStatus();

        if (!$newStopovers) {
            return null;
        }
        if (count($newStopovers) === 1) {

            return new LivePointDto(
                (new Coordinate(
                    $newStopovers[0]->trainStation->latitude,
                    $newStopovers[0]->trainStation->longitude
                )),
                null,
                $newStopovers[0]->arrival->timestamp,
                $newStopovers[0]->departure->timestamp,
                $hafasTrip->linename,
                $this->statusId
            );
        }

        $now               = Carbon::now()->timestamp;
        $percentage        = ($now - $newStopovers[0]->departure->timestamp)
                             / ($newStopovers[1]->arrival->timestamp - $newStopovers[0]->departure->timestamp);
        $this->origin      = $newStopovers[0];
        $this->destination = $newStopovers[1];
        $polyline          = $this->getPolylineBetween(false);

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
        array_unshift($polyline->features, Feature::fromCoordinate($pointS));

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

    /**
     * @throws JsonException
     */
    private function getPolylineWithTimestamps(): stdClass {
        $geoJsonObj = json_decode($this->hafasTrip->polyline->polyline, false, 512, JSON_THROW_ON_ERROR);
        $stopovers  = $this->hafasTrip->stopovers;

        $stopovers = $stopovers->map(function($stopover) {
            $stopover['passed'] = false;
            return $stopover;
        });

        foreach ($geoJsonObj->features as $polylineFeature) {
            if (!isset($polylineFeature->properties->id)) {
                continue;
            }

            $stopover = $stopovers->where('trainStation.ibnr', $polylineFeature->properties->id)
                                  ->where('passed', false)
                                  ->first();

            if (is_null($stopover)) {
                continue;
            }

            $stopover->passed                               = true;
            $polylineFeature->properties->departure_planned = $stopover->departure_planned?->clone();
            $polylineFeature->properties->arrival_planned   = $stopover->arrival_planned?->clone();
        }
        return $geoJsonObj;
    }

    public static function getMapLinesForCheckin(TrainCheckin $checkin, bool $invert = false): array {
        try {
            $geoJson  = (new self($checkin->HafasTrip, $checkin->origin_stopover, $checkin->destination_stopover))
                ->getPolylineBetween();
            $mapLines = [];
            foreach ($geoJson->features as $feature) {
                if (isset($feature->geometry->coordinates[0], $feature->geometry->coordinates[1])) {
                    $mapLines[] = [
                        $feature->geometry->coordinates[$invert ? 1 : 0],
                        $feature->geometry->coordinates[$invert ? 0 : 1]
                    ];
                }
            }
            return $mapLines;
        } catch (Exception $exception) {
            report($exception);
            return [
                [$checkin->originStation->latitude, $checkin->originStation->longitude],
                [$checkin->destinationStation->latitude, $checkin->destinationStation->longitude]
            ];
        }
    }

    /**
     * @throws JsonException
     */
    private function getPolylineBetween(bool $preserveKeys = true): stdClass {
        $this->hafasTrip->loadMissing(['stopovers.trainStation']);
        $geoJson  = $this->getPolylineWithTimestamps();
        $features = $geoJson->features;

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($features as $key => $data) {
            if (!isset($data->properties->id)) {
                continue;
            }

            if ($originIndex === null
                && $this->origin->trainStation->ibnr === (int) $data->properties->id
                && isset($data->properties->departure_planned) //Important for ring lines!
                && $this->origin->departure_planned->is($data->properties->departure_planned) //ring lines!
            ) {
                $originIndex = $key;
            }

            if ($destinationIndex === null
                && $this->destination->trainStation->ibnr === (int) $data->properties->id
                && isset($data->properties->arrival_planned) //Important for ring lines!
                && $this->destination->arrival_planned->is($data->properties->arrival_planned) //ring lines!
            ) {
                $destinationIndex = $key;
            }
        }
        if (is_array($features)) { // object is a rarely stdClass without content if no features in the GeoJSON
            $slicedFeatures    = array_slice(
                $features,
                $originIndex,
                $destinationIndex - $originIndex + 1,
                $preserveKeys
            );
            $geoJson->features = $slicedFeatures;
        }

        return $geoJson;
    }

    public function calculateDistance(): int {
        if (
            $this->hafasTrip->polyline === null ||
            $this->hafasTrip->polyline?->polyline === null ||
            strlen($this->hafasTrip->polyline?->polyline) < 10
        ) {
            return $this->calculateDistanceByStopovers();
        }

        $distance = 0;
        try {
            $geoJson      = $this->getPolylineBetween();
            $lastStopover = null;
            foreach ($geoJson->features as $stopover) {
                if ($lastStopover !== null) {
                    $distance += (new LineSegment(
                        new Coordinate(
                            $lastStopover->geometry->coordinates[1],
                            $lastStopover->geometry->coordinates[0]
                        ),
                        new Coordinate($stopover->geometry->coordinates[1], $stopover->geometry->coordinates[0])
                    ))->calculateDistance();
                }

                $lastStopover = $stopover;
            }
        } catch (JsonException $e) {
            report($e);
        }

        return $distance;
    }

    private function calculateDistanceByStopovers(): int {
        $stopovers                = $this->hafasTrip->stopovers->sortBy('departure');
        $originStopoverIndex      = $stopovers->search(function($item) {
            return $item->is($this->origin);
        });
        $destinationStopoverIndex = $stopovers->search(function($item) {
            return $item->is($this->destination);
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
