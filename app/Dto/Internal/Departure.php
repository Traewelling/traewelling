<?php

namespace App\Dto\Internal;

use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;

readonly class Departure
{

    public Station     $station;
    public Carbon      $plannedDeparture;
    public Carbon|null $realDeparture;
    public BahnTrip $trip;
    public string|null $plannedPlatform;
    public string|null $realPlatform;

    public function __construct(Station $station, Carbon $plannedDeparture, Carbon|null $realDeparture, BahnTrip $trip, string|null $plannedPlatform, string|null $realPlatform) {
        $this->station          = $station;
        $this->plannedDeparture = $plannedDeparture;
        $this->realDeparture    = $realDeparture;
        $this->trip             = $trip;
        $this->plannedPlatform  = $plannedPlatform;
        $this->realPlatform     = $realPlatform;
    }

    public function getDelay(): ?int {
        if ($this->realDeparture === null) {
            return null;
        }
        return $this->plannedDeparture->diffInMinutes($this->realDeparture);
    }
}
