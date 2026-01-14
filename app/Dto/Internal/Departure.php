<?php

namespace App\Dto\Internal;

use App\Models\Station;
use Carbon\Carbon;

readonly class Departure
{
    public Station $station;

    public Carbon $plannedDeparture;

    public ?Carbon $realDeparture;

    public BahnTrip $trip;

    public ?string $plannedPlatform;

    public ?string $realPlatform;

    public function __construct(Station $station, Carbon $plannedDeparture, ?Carbon $realDeparture, BahnTrip $trip, ?string $plannedPlatform, ?string $realPlatform)
    {
        $this->station = $station;
        $this->plannedDeparture = $plannedDeparture;
        $this->realDeparture = $realDeparture;
        $this->trip = $trip;
        $this->plannedPlatform = $plannedPlatform;
        $this->realPlatform = $realPlatform;
    }

    public function getDelay(): ?int
    {
        if ($this->realDeparture === null) {
            return null;
        }

        return $this->plannedDeparture->diffInMinutes($this->realDeparture);
    }
}
