<?php

namespace App\Http\Controllers\Locations;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Models\LineRun;
use App\Models\LineSegment;
use App\Models\LineSegmentBetween;
use App\Models\LineSegmentPoint;
use App\Models\TrainStation;

class LineRunController
{
    private array $features;
    private array $curSegment;
    private ?int  $a = null;
    private ?int  $b = null;
    private ?string $hash = null;

    public function __construct() {
        $file = file_get_contents(__DIR__ . '/demoFile.json');

        $this->hash = md5($file);

        $json           = json_decode($file);
        $this->features = $json->features;
    }

    public function showDemo() {
        $segments = LineRun::where('hash', $this->hash)->get();

        $coordinates = [];
        foreach ($segments as $segment) {
            $segment = LineSegmentBetween::where('id', $segment->line_segment_id)->first();
            $points = LineSegmentPoint::where('segment_id', $segment->segment_id)->get();
            foreach ($points as $point) {
                $coordinates[] = new Coordinate($point->latitude, $point->longitude);
            }
        }

        print_r(json_encode(new Feature($coordinates)));
    }

    public function demo(): void {
        foreach ($this->features as $key => $item) {
            if (!empty($item->properties->id)) {
                var_dump($key);
                $this->setDelimitStation($key);
            }
        }
    }

    private function setDelimitStation(int $id) {
        if ($this->a && $this->b) {
            $this->cutLineSegment();
            $this->createLineSegment();
            $this->a = $this->b;
            $this->b = null;
        }

        if (!$this->a) {
            $this->a = $id;
        } elseif (!$this->b) {
            $this->b = $id;
        }
    }

    private function cutLineSegment(): void {
        $this->curSegment = array_slice($this->features, $this->a, $this->b - $this->a + 1);
    }

    private function createLineSegment() {
        $origin      = array_shift($this->curSegment);
        $origin      = TrainStation::updateOrCreate(
            ['ibnr' => $origin->properties->id],
            [
                'name'      => $origin->properties->name,
                'latitude'  => $origin->geometry->coordinates[1],
                'longitude' => $origin->geometry->coordinates[0],
            ]
        );
        $destination = array_pop($this->curSegment);
        $destination = TrainStation::updateOrCreate(
            ['ibnr' => $destination->properties->id],
            [
                'name'      => $destination->properties->name,
                'latitude'  => $destination->geometry->coordinates[1],
                'longitude' => $destination->geometry->coordinates[0],
            ]
        );

        $exists = LineSegmentBetween::where(['origin_id' => $origin->id, 'destination_id' => $destination->id])->first();
        if (empty($exists)) {
            $segmentHead = LineSegment::create(['reversible' => true]);

            foreach ($this->curSegment as $item) {
                LineSegmentPoint::create([
                                             'segment_id' => $segmentHead->id,
                                             'latitude'   => $item->geometry->coordinates[1],
                                             'longitude'  => $item->geometry->coordinates[0],
                                         ]);

            }

            $segment = LineSegmentBetween::create([
                                                             'origin_id'      => $origin->id,
                                                             'destination_id' => $destination->id,
                                                             'segment_id'     => $segmentHead->id,
                                                             'reversed'       => false
                                                         ]);
            LineRun::create([
                                'hash' => $this->hash,
                                'line_segment_id' => $segment->id
                            ]);
        }
    }

}
