<?php

namespace App\Dto\Internal;

readonly class BahnTrip
{
    public string $tripId;
    public string $direction;
    public string $lineName;
    public string $number;
    public string $category;
    public string $journeyNumber;

    public function __construct(string $tripId, string $direction, string $lineName, string $number, string $category, string $journeyNumber) {
        $this->tripId        = $tripId;
        $this->direction     = $direction;
        $this->lineName      = $lineName;
        $this->number        = $number;
        $this->category      = $category;
        $this->journeyNumber = $journeyNumber;
    }
}
