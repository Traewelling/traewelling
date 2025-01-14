<?php

namespace App\Http\Controllers\Backend\Support;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Dto\GeoJson\FeatureCollection;
use App\Dto\LivePointDto;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
use App\Objects\LineSegment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use JsonException;
use stdClass;

class LocationController
{
    private Trip      $trip;
    private ?Stopover $origin;
    private ?Stopover $destination;
    private ?Status   $status;

    public function __construct(
        Trip     $trip,
        Stopover $origin = null,
        Stopover $destination = null,
        Status   $status = null
    ) {
        $this->trip        = $trip;
        $this->origin      = $origin;
        $this->destination = $destination;
        $this->status      = $status;
    }

    public static function forStatus(Status $status): LocationController {
        return new self(
            $status->checkin->trip,
            $status->checkin->originStopover,
            $status->checkin->destinationStopover,
            $status
        );
    }

    private function filterStopoversFromStatus(): ?array {
        $stopovers    = $this->trip->stopovers;
        $newStopovers = null;
        foreach ($stopovers as $key => $stopover) {
            if ($stopover->departure->isFuture()) {
                if ($stopover->arrival->isPast()) {
                    $newStopovers = [$stopover];
                    break;
                }
                if (!empty($stopovers[$key - 1])) {
                    $newStopovers[] = $stopovers[$key - 1];
                }
                $newStopovers[] = $stopover;
                break;
            }
        }

        return $newStopovers;
    }

    /**
     * @throws JsonException
     */
    public function calculateLivePosition(): ?LivePointDto {
        $newStopovers = $this->filterStopoversFromStatus();

        if (!$newStopovers || !isset($this->trip->polyline->polyline)) {
            return null;
        }
        if (count($newStopovers) === 1) {
            return new LivePointDto(
                (new Coordinate(
                    $newStopovers[0]->station->latitude,
                    $newStopovers[0]->station->longitude
                )),
                null,
                $newStopovers[0]->arrival->timestamp,
                $newStopovers[0]->departure->timestamp,
                $this->trip->linename,
                $this->status
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

        $currentPosition = $lineSegment->interpolatePoint($meters / $distance);

        $polyline->features = array_slice($polyline->features, $key);
        array_unshift($polyline->features, Feature::fromCoordinate($currentPosition));

        return new LivePointDto(
            null,
            $polyline,
            $newStopovers[1]->arrival->timestamp,
            $newStopovers[1]->departure->timestamp,
            $this->trip->linename,
            $this->status,
        );
    }

    private function getDistanceFromGeoJson(stdClass $geoJson): int {
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
        if (!empty($this->trip->polyline)) {
            // decode GeoJSON object from polyline
            $geoJsonObj = json_decode($this->trip->polyline->polyline, false, 512, JSON_THROW_ON_ERROR);
        } else {
            // create empty GeoJSON object
            $geoJsonObj           = new stdClass();
            $geoJsonObj->type     = 'FeatureCollection';
            $geoJsonObj->features = [];
        }
        $stopovers = $this->trip->stopovers;

        $stopovers = $stopovers->map(function($stopover) {
            $stopover['passed'] = false;
            return $stopover;
        });

        foreach ($geoJsonObj->features as $polylineFeature) {
            if (!isset($polylineFeature->properties->id)) {
                continue;
            }

            $stopover = $stopovers->where('station.ibnr', $polylineFeature->properties->id)
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

    public function getMapLines(bool $invert = false): array {
        try {
            $geoJson = $this->getPolylineBetween();
            if ($geoJson instanceof FeatureCollection) {
                return $geoJson->features[0]->getCoordinates($invert);
            }

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
                [$this->origin->latitude, $this->origin->longitude],
                [$this->destination->latitude, $this->destination->longitude]
            ];
        }
    }

    private function createPolylineFromStopovers(): FeatureCollection {
        $coordinates = [];
        $firstStop   = null;
        foreach ($this->trip->stopovers as $stopover) {
            if ($firstStop !== null || $stopover->is($this->origin)) {
                $firstStop     = $stopover;
                $coordinates[] = new Coordinate($stopover->station->latitude, $stopover->station->longitude);

                if ($stopover->is($this->destination)) {
                    break;
                }
            }
        }

        $features = new Collection([new Feature($coordinates)]);
        return new FeatureCollection($features);
    }

    /**
     * @throws JsonException
     */
    private function getPolylineBetween(bool $preserveKeys = true): stdClass|FeatureCollection {
        $this->trip->loadMissing(['stopovers.station']);
        $geoJson = $this->getPolylineWithTimestamps();
        if (count((array) $geoJson->features) === 0) {
            return $this->createPolylineFromStopovers();
        }

        $features = $geoJson->features;

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($features as $key => $data) {
            if (!isset($data->properties->id)) {
                continue;
            }

            if ($originIndex === null
                && $this->origin->station->ibnr === (int) $data->properties->id
                && isset($data->properties->departure_planned) //Important for ring lines!
                && $this->origin->departure_planned->is($data->properties->departure_planned) //ring lines!
            ) {
                $originIndex = $key;
            }

            if ($destinationIndex === null
                && $this->destination->station->ibnr === (int) $data->properties->id
                && isset($data->properties->arrival_planned) //Important for ring lines!
                && $this->destination->arrival_planned->is($data->properties->arrival_planned) //ring lines!
            ) {
                $destinationIndex = $key;
            }
        }
        if (is_array($features)) { // object is a rarely stdClass without content if no features in the GeoJSON
            $slicedFeatures    = array_slice(
                array:         $features,
                offset:        $originIndex,
                length:        $destinationIndex - $originIndex + 1,
                preserve_keys: $preserveKeys
            );
            $geoJson->features = $slicedFeatures;
        }

        return $geoJson;
    }

    public function calculateDistance(): int {
        if (
            $this->trip->polyline === null ||
            $this->trip->polyline?->polyline === null ||
            strlen($this->trip->polyline?->polyline) < 10
        ) {
            return $this->calculateDistanceByStopovers();
        }

        $distance = 0;
        try {
            $geoJson      = $this->getPolylineBetween();
            $lastStopover = null;
            foreach ($geoJson->features as $stopover) {
                if ($lastStopover === null || !isset($stopover->geometry->coordinates[0]) || !isset($stopover->geometry->coordinates[1])) {
                    $lastStopover = $stopover;
                    continue;
                }

                $distance += (new LineSegment(
                    new Coordinate(
                        $lastStopover->geometry->coordinates[1],
                        $lastStopover->geometry->coordinates[0]
                    ),
                    new Coordinate($stopover->geometry->coordinates[1], $stopover->geometry->coordinates[0])
                ))->calculateDistance();

                $lastStopover = $stopover;
            }
        } catch (JsonException $e) {
            report($e);
        }

        return $distance;
    }

    private function calculateDistanceByStopovers(): int {
        $stopovers                = $this->trip->stopovers->sortBy('departure');
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
                new Coordinate($lastStopover->station->latitude, $lastStopover->station->longitude),
                new Coordinate($stopover->station->latitude, $stopover->station->longitude)
            ))->calculateDistance();
            $lastStopover = $stopover;
        }
        return $distance;
    }
}
