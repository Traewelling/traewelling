<?php

namespace App\Http\Controllers\Locations;

use App\Dto\Coordinate;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\LineRun;
use App\Models\LineSegment;
use App\Models\LineSegmentBetween;
use App\Models\LineSegmentPoint;
use App\Models\Status;
use App\Models\TrainStation;

class LineRunController
{
    private ?array  $features;
    private array   $curSegment;
    private ?int    $firstStop  = null;
    private ?int    $secondStop = null;
    private ?string $hash;

    public function __construct(mixed $json = null, string $hash = null) {
        $this->features = $json?->features;
        $this->hash     = $hash;
    }

    public static function forStatus(Status $status): array {
        $ctrl = new LineRunController(
            null,
            $status->trainCheckin->HafasTrip->polyline->hash
        );
        $run  = $ctrl->getLineRun($status->trainCheckin->originStation, $status->trainCheckin->destinationStation);

        return $run ?? LocationController::forStatus($status)->getMapLines();
    }

    public function getLineRun(?TrainStation $origin = null, ?TrainStation $destination = null): ?array {
        $segments = LineRun::where('hash', $this->hash)->get();
        if ($segments->count() === 0) {
            return null;
        }
        $lineSegmentIds  = $segments->map(fn($segment) => $segment->line_segment_id);
        $segmentsBetween = LineSegmentBetween::whereIn('id', $lineSegmentIds)->get();

        $routeStarted    = false;
        $segmentsBetween = $segmentsBetween->filter(function ($segment) use (&$origin, &$destination, &$routeStarted) {
            if (empty($origin) || empty($destination)) {
                return true;
            }
            if (!$routeStarted && $segment->origin_id == $origin->id) {
                $routeStarted = true;
            }
            if ($routeStarted && $segment->destination_id == $destination->id) {
                $routeStarted = false;
                return true;
            }
            return $routeStarted;
        })->map(fn($segment) => $segment->segment_id);

        $points = LineSegmentPoint::whereIn('segment_id', $segmentsBetween)->get();

        return $points->map(fn($point) => new Coordinate($point->latitude, $point->longitude))->toArray();
    }

    public function splitAndSaveLineRun(): void {
        foreach ($this->features as $key => $item) {
            if (!empty($item->properties->id)) {
                $this->setDelimitStation($key);
            }
        }

        $this->cutLineSegment();
        $this->createLineSegment();
    }

    private function setDelimitStation(int $id): void {
        if ($this->firstStop !== null && $this->secondStop !== null) {
            $this->cutLineSegment();
            $this->createLineSegment();
            $this->firstStop  = $this->secondStop;
            $this->secondStop = null;
        }

        if ($this->firstStop === null) {
            $this->firstStop = $id;
        } elseif ($this->secondStop === null) {
            $this->secondStop = $id;
        }
    }

    private function cutLineSegment(): void {
        $this->curSegment = array_slice($this->features, $this->firstStop, $this->secondStop - $this->firstStop + 1);
    }

    private function createLineSegment(): void {
        $destination = end($this->curSegment);
        $destination = TrainStation::updateOrCreate(
            ['ibnr' => $destination->properties->id],
            [
                'name'      => $destination->properties->name,
                'latitude'  => $destination->geometry->coordinates[1],
                'longitude' => $destination->geometry->coordinates[0],
            ]
        );

        $origin = reset($this->curSegment);
        $origin = TrainStation::updateOrCreate(
            ['ibnr' => $origin->properties->id],
            [
                'name'      => $origin->properties->name,
                'latitude'  => $origin->geometry->coordinates[1],
                'longitude' => $origin->geometry->coordinates[0],
            ]
        );

        /** @var LineSegmentBetween $exists */
        $exists   = LineSegmentBetween::where(
            ['origin_id' => $origin->id, 'destination_id' => $destination->id]
        )->with('segment')->first();
        $distance = LocationController::getDistanceFromLineSegment($this->curSegment);

        //check if segment already exists and segments distance is less than 10% different of calculated distance
        if (
            empty($exists)
            || ($distance / $exists->segment->distance < 0.9 || $distance / $exists->segment->distance > 1.15)
        ) {
            $segmentHead = LineSegment::create(['reversible' => true, 'distance' => $distance]);

            foreach ($this->curSegment as $item) {
                LineSegmentPoint::create([
                                             'segment_id' => $segmentHead->id,
                                             'latitude'   => $item->geometry->coordinates[1],
                                             'longitude'  => $item->geometry->coordinates[0],
                                         ]);

            }

            $exists = LineSegmentBetween::create([
                                                     'origin_id'      => $origin->id,
                                                     'destination_id' => $destination->id,
                                                     'segment_id'     => $segmentHead->id,
                                                     'reversed'       => false
                                                 ]);
        }

        LineRun::create([
                            'hash'            => $this->hash,
                            'line_segment_id' => $exists->id
                        ]);
    }

}
