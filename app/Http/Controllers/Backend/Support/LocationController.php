<?php

namespace App\Http\Controllers\Backend\Support;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Dto\GeoJson\FeatureCollection;
use App\Dto\LivePointDto;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\GeoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use JsonException;
use stdClass;

class LocationController
{
    private Trip       $trip;
    private ?Stopover  $origin;
    private ?Stopover  $destination;
    private ?Status    $status;
    private GeoService $geoService;

    public function __construct(
        Trip        $trip,
        ?Stopover   $origin = null,
        ?Stopover   $destination = null,
        ?Status     $status = null,
        ?GeoService $geoService = null
    ) {
        $this->trip        = $trip;
        $this->origin      = $origin;
        $this->destination = $destination;
        $this->status      = $status;
        $this->geoService  = $geoService ?? new GeoService();
    }

    public static function forStatus(Status $status, ?GeoService $geoService = null): LocationController {
        $status->checkin->loadMissing(['originStopover', 'destinationStopover']);

        return new self(
            $status->checkin->trip,
            $status->checkin->originStopover,
            $status->checkin->destinationStopover,
            $status,
            $geoService
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

    public function calculateLivePosition(): ?LivePointDto {
        $newStopovers = $this->filterStopoversFromStatus();
        if (!$newStopovers) {
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
        try {
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
                    $distance += $this->geoService->getDistance($recentPoint, $point);
                    if ($distance >= $meters) {
                        break;
                    }
                }
                $recentPoint = $point ?? $recentPoint;
            }

            $currentPosition = $this->geoService->interpolatePoint(
                $recentPoint,
                $point ?? $recentPoint,
                $distance < 1 ? 0 : $meters / $distance
            );
            if ($currentPosition === null) {
                return null;
            }

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
        } catch (Exception) {
            return null;
        }
    }

    private function getDistanceFromGeoJson(stdClass|FeatureCollection $geoJson): int {
        $fullDistance = 0;
        $lastStopover = null;
        foreach ($geoJson->features as $stopover) {
            $stopover = Coordinate::fromGeoJson($stopover);
            if ($lastStopover === null || $stopover === null) {
                $lastStopover = $stopover;
                continue;
            }
            $fullDistance += $this->geoService->getDistance($lastStopover, $stopover);
            $lastStopover = $stopover;
        }
        return $fullDistance;
    }

    private function emptyGeoJson(): stdClass {
        $geoJson           = new stdClass();
        $geoJson->type     = 'FeatureCollection';
        $geoJson->features = [];
        return $geoJson;
    }

    private function getPolylineWithTimestamps(?string $polyLine = null): stdClass {
        $geoJsonObj = $this->emptyGeoJson();
        $polyLine   = $polyLine ?? $this->trip->polyline?->polyline;

        // Cache currently commented out, so it doesn't cause RAM-overflow.
        // $cacheName      = sprintf('trip_%s_polyline_%s', $this->trip->id, sha1($polyLine));
        // $cachedPolyline = Cache::get($cacheName);
        // if (!empty($cachedPolyline)) {
        //    return $cachedPolyline;
        // }

        if (!empty($polyLine)) {
            // decode GeoJSON object from polyline
            try {
                $geoJsonObj = json_decode($polyLine, false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                // if decoding fails, return empty GeoJSON object
                $geoJsonObj = $this->emptyGeoJson();
            }
        }
        $stopovers = $this->trip->stopovers;

        $stopovers = $stopovers->map(function($stopover) {
            $stopover['passed'] = false;
            return $stopover;
        });

        if (!isset($geoJsonObj->features)) {
            $geoJsonObj->features = [];
        }

        $this->mapStopoversToPolyline($geoJsonObj, $stopovers);

        // Cache currently commented out, so it doesn't cause RAM-overflow.
        // Cache::forget($cacheName);
        // Cache::put($cacheName, $geoJsonObj, 60 * 60 * 24);

        return $geoJsonObj;
    }

    public function getMapLines(bool $invert = false): array {
        try {
            $geoJson = $this->getPolylineBetween();
            if ($geoJson instanceof FeatureCollection) {
                return $geoJson->features->first()->getCoordinates($invert);
            }

            $mapLines = [];
            foreach ($geoJson->features as $feature) {
                if (!empty($feature->geometry->coordinates[0]) && !empty($feature->geometry->coordinates[1])) {
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
                [$this->origin->station->longitude, $this->origin->station->latitude],
                [$this->destination->station->longitude, $this->destination->station->latitude]
            ];
        }
    }

    private function getPolylineFromRouteSegments(): ?FeatureCollection {
        $coordinates   = [];
        $routeSegments = 0;
        $firstStopHit = false;
        foreach ($this->trip->stopovers as $stopover) {
            if (!$firstStopHit) {
                if ($stopover->is($this->origin)) {
                    $firstStopHit = true;
                } else {
                    continue;
                }
            }
            if ($stopover->routeSegment === null) {
                $coordinates[] = new Coordinate(
                    $stopover->station->latitude,
                    $stopover->station->longitude
                );
                if ($stopover->is($this->destination)) {
                    break;
                }
                continue;
            }
            if ($stopover->is($this->destination)) {
                break;
            }
            $coordinates = array_merge($coordinates, $stopover->routeSegment->getCoordinates());
            $routeSegments++;
        }

        if (empty($coordinates) || $routeSegments < 1) {
            return null;
        }

        $features = collect([new Feature($coordinates)]);
        return new FeatureCollection($features);
    }

    private function createPolylineFromStopovers(): FeatureCollection {
        $coordinates = [];
        $firstStop   = null;
        foreach ($this->trip->stopovers as $stopover) {
            if ($firstStop !== null || $stopover->is($this->origin)) {
                $firstStop  = $stopover;
                $coordinate = new Coordinate($stopover->station->latitude, $stopover->station->longitude);
                $feature    = Feature::fromCoordinate($coordinate);
                $feature->setStationId($stopover->station->id);
                $feature->setDeparturePlanned($stopover->departure_planned?->toIso8601ZuluString());
                $feature->setArrivalPlanned($stopover->arrival_planned?->toIso8601ZuluString());
                $coordinates[] = $feature;


                if ($stopover->is($this->destination)) {
                    break;
                }
            }
        }

        $features = collect($coordinates);
        return new FeatureCollection($features);
    }

    public function parseByIbnr(int|string|null $originIndex, mixed $data, int|string $key, int|string|null $destinationIndex): array {
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
        return [$originIndex, $destinationIndex];
    }

    public function parseByStationId(int|string|null $originIndex, mixed $data, int|string $key, int|string|null $destinationIndex): array {
        if ($originIndex === null
            && $this->origin->station->id === (int) $data->properties->stationId
            && isset($data->properties->departure_planned) //Important for ring lines!
            && $this->origin->departure_planned->is($data->properties->departure_planned) //ring lines!
        ) {
            $originIndex = $key;
        }

        if ($destinationIndex === null
            && $this->destination->station->id === (int) $data->properties->stationId
            && isset($data->properties->arrival_planned) //Important for ring lines!
            && $this->destination->arrival_planned->is($data->properties->arrival_planned) //ring lines!
        ) {
            $destinationIndex = $key;
        }
        return [$originIndex, $destinationIndex];
    }

    private function getPolylineBetween(bool $preserveKeys = true): stdClass|FeatureCollection {
        $this->trip->loadMissing(['stopovers.station']);
        $lineString = $this->getPolylineFromRouteSegments();
        if ($lineString) {
            return $lineString;
        }

        // lineString will be null if no route segments are available
        // if null is given to getPolylineWithTimestamps, it will fallback to the trip's polyline
        $geoJson = $this->getPolylineWithTimestamps($lineString);
        if (count((array) $geoJson->features) === 0) {
            $stopoversPolyline = $this->createPolylineFromStopovers();
            $geoJson           = $this->getPolylineWithTimestamps(json_encode($stopoversPolyline));
            if (count((array) $geoJson->features) === 0) {
                return $this->emptyGeoJson();
            }
        }

        $features = $geoJson->features;

        $originIndex      = null;
        $destinationIndex = null;
        foreach ($features as $key => $data) {
            if (isset($data->properties->id)) {
                [$originIndex, $destinationIndex] = $this->parseByIbnr($originIndex, $data, $key, $destinationIndex);
            }
            if (isset($data->properties->stationId)) {
                [$originIndex, $destinationIndex] = $this->parseByStationId($originIndex, $data, $key, $destinationIndex);
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

    private function hasEnoughRouteSegments(): bool {
        $stopovers                = $this->trip->stopovers->sortBy('departure');

        $routeSegments = 0;
        foreach ($stopovers as $stopover) {
            if ($stopover->route_segment_id) {
                $routeSegments++;
            }
        }

        return ($routeSegments / $stopovers->count()) >= 0.5;
    }

    public function calculateDistance(): int {
        if (
            $this->hasEnoughRouteSegments() ||
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

                $distance += $this->geoService->getDistance(
                    new Coordinate(
                        $lastStopover->geometry->coordinates[1],
                        $lastStopover->geometry->coordinates[0]
                    ),
                    new Coordinate($stopover->geometry->coordinates[1], $stopover->geometry->coordinates[0])
                );

                $lastStopover = $stopover;
            }
        } catch (JsonException $e) {
            report($e);
        }

        if ($distance === 0) {
            $distance = $this->calculateDistanceByStopovers();
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
            if ($stopover->route_segment_id) {
                $distance += $stopover->routeSegment->distance;
                $lastStopover = $stopover;
                continue;
            }
            $distance     += $this->geoService->getDistance(
                new Coordinate($lastStopover->station->latitude, $lastStopover->station->longitude),
                new Coordinate($stopover->station->latitude, $stopover->station->longitude)
            );
            $lastStopover = $stopover;
        }
        return $distance;
    }

    public function mapStopoversToPolyline(mixed $geoJsonObj, EloquentCollection|Collection $stopovers): void {
        foreach ($geoJsonObj->features as $polylineFeature) {
            if (isset($polylineFeature->properties->id)) {
                $stopover = $stopovers->where('station.ibnr', $polylineFeature->properties->id)
                                      ->where('passed', false)
                                      ->first();
            }
            if (isset($polylineFeature->properties->stationId)) {
                $stopover = $stopovers->where('station.id', $polylineFeature->properties->stationId)
                                      ->where('passed', false)
                                      ->first();
            }


            if (empty($stopover)) {
                continue;
            }

            $stopover->passed                               = true;
            $polylineFeature->properties->departure_planned = $stopover->departure_planned?->clone();
            $polylineFeature->properties->arrival_planned   = $stopover->arrival_planned?->clone();
        }
    }
}
