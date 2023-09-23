<?php

namespace App\Http\Controllers\Locations;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Models\LineRun;
use App\Models\LineSegment;
use App\Models\LineSegmentBetween;
use App\Models\LineSegmentPoint;
use App\Models\Status;
use App\Models\TrainStation;
use App\Virtual\Models\Train;
use Illuminate\Http\Request;
use App\Models\PolyLine;

class LineRunController
{
    private ?array   $features;
    private array   $curSegment;
    private ?int    $a = null;
    private ?int    $b = null;
    private ?string $hash;

    public static function fromFile(): self {
        $file = file_get_contents(__DIR__ . '/demoFile.json');
        $json = json_decode($file);
        return new self($json, md5($file));
    }

    public function __construct(mixed $json = null, string $hash = null) {
        $this->features = $json?->features;
        $this->hash     = $hash;
    }

    public static function demoTwo(Request $request) {
        if (!empty($request['old'])) {
            print_r(Polyline::where('hash', $request['hash'])->first()->polyline);
            return;
        }
        return json_encode(new Feature((new self(null, $request['hash']))->getLinerun()));
    }

    public static function forStatus(Status $status): array {
        $ctrl = new LineRunController(
            null,
            $status->trainCheckin->HafasTrip->polyline->hash
        );
        $run = $ctrl->getLinerun($status->trainCheckin->originStation, $status->trainCheckin->destinationStation);

        return $run ?? LocationController::forStatus($status)->getMapLines();
    }

    public function getLinerun(?TrainStation $origin = null, ?TrainStation $destination = null): ?array {
        $segments        = LineRun::where('hash', $this->hash)->get();
        if ($segments->count() === 0) {
            return null;
        }
        $lineSegmentIds  = $segments->map(fn($segment) => $segment->line_segment_id);
        $segmentsBetween = LineSegmentBetween::whereIn('id', $lineSegmentIds)->get();

        $routeStarted    = false;
        $segmentsBetween = $segmentsBetween->filter(function($segment) use (&$origin, &$destination, &$routeStarted) {
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

        $points        = LineSegmentPoint::whereIn('segment_id', $segmentsBetween)->get();

        return $points->map(fn($point) => new Coordinate($point->latitude, $point->longitude))->toArray();
    }

    public function demo(): void {
        foreach ($this->features as $key => $item) {
            if (!empty($item->properties->id)) {
                $this->setDelimitStation($key);
            }
        }

        $this->cutLineSegment();
        $this->createLineSegment();
    }

    private function setDelimitStation(int $id) {
        if ($this->a !== null && $this->b !== null) {
            $this->cutLineSegment();
            $this->createLineSegment();
            $this->a = $this->b;
            $this->b = null;
        }

        if ($this->a === null) {
            $this->a = $id;
        } elseif ($this->b === null) {
            $this->b = $id;
        }
    }

    private function cutLineSegment(): void {
        $this->curSegment = array_slice($this->features, $this->a, $this->b - $this->a + 1);
    }

    private function createLineSegment() {
        $destination = end($this->curSegment);
        $destination = TrainStation::updateOrCreate(
            ['ibnr' => $destination->properties->id],
            [
                'name'      => $destination->properties->name,
                'latitude'  => $destination->geometry->coordinates[1],
                'longitude' => $destination->geometry->coordinates[0],
            ]
        );

        $origin      = reset($this->curSegment);
        $origin      = TrainStation::updateOrCreate(
            ['ibnr' => $origin->properties->id],
            [
                'name'      => $origin->properties->name,
                'latitude'  => $origin->geometry->coordinates[1],
                'longitude' => $origin->geometry->coordinates[0],
            ]
        );

        $exists   = LineSegmentBetween::where(
            ['origin_id' => $origin->id, 'destination_id' => $destination->id]
        )->first();
        $distance = LocationController::getDistanceFromLineSegment($this->curSegment);

        //check if segment already exists and segments distance is less than 10% different of calculated distance
        if (empty($exists) || ($exists->distance * 0.9 > $distance || $exists->distance * 1.1 < $distance)) {
            $segmentHead = LineSegment::create(['reversible' => true, 'distance' => $distance ?? 0]);

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
