<?php

namespace App\Dto\FriendlyPublicTransportFormat;

use stdClass;

class Stop
{
    public readonly string $type; // Maybe Enum?
    public readonly string $id;
    public readonly string $name;
    public readonly StopLocation $location;
    public readonly stdClass $products; //it's kept at stdClass to reduce useless data in our responses

    public function __construct(string $id, string $name, ?float $lat, ?float $lon)
    {
        $this->type = "stop";
        $this->id = $id;
        $this->name = $name;
        $this->location = new StopLocation($lat, $lon, $id);
        $this->products = new stdClass();
    }
}
