<?php

namespace App\Dto\FriendlyPublicTransportFormat;

class Location
{
    public readonly string $type; // Maybe ENUM?
    public readonly float $latitude;
    public readonly float $longitude;

    public function __construct(float $longitude, float $latitude)
    {
        $this->type = "location";
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
