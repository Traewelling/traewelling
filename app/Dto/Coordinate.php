<?php
declare(strict_types=1);

namespace App\Dto;

class Coordinate
{

    public readonly float $latitude;
    public readonly float $longitude;

    public function __construct(float $latitude, float $longitude) {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromGeoJson(\stdClass $point): ?self {
        if (isset($point->geometry->coordinates)) {
            return new self($point->geometry->coordinates[1], $point->geometry->coordinates[0]);
        }
        return null;
    }

    public function toGeoJsonPoint(): array {
        return [
            "type"       => "Feature",
            "properties" => [],
            "geometry"   => [
                "type"        => "Point",
                "coordinates" => [
                    $this->longitude,
                    $this->latitude
                ]
            ]
        ];
    }

}
