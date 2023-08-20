<?php

namespace App\Dto\FriendlyPublicTransportFormat;

class StopLocation extends Location
{
    public function __construct(float $latitude, float $longitude, string $id)
    {
        $this->id = $id;
        parent::__construct($latitude, $longitude);
    }
}
