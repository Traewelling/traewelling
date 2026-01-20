<?php

namespace App\Dto\OpenRailRouting;

use App\Dto\GeoJson\Feature;

class RouteDto
{
    public Feature $feature;

    public float $distanceInMeters;

    public float $durationInSeconds;

    public function __construct(Feature $feature, float $distanceInMeters, float $durationInSeconds)
    {
        $this->feature = $feature;
        $this->distanceInMeters = $distanceInMeters;
        $this->durationInSeconds = $durationInSeconds;
    }
}
