<?php

namespace App\Dto\FriendlyPublicTransportFormat;

use App\Traits\FriendlyPublicTransportFormat\DepartureArrivalTimes;

class Stopover
{
    use DepartureArrivalTimes;
    public readonly Stop $stop;
    public readonly array $remarks;

    /**
     * @param string $id
     * @param string $name
     * @param float $lat
     * @param float $lon
     * @param DTM|null $arrival
     * @param DTM|null $departure
     * @param string|null $arrivalPlatform
     * @param string|null $departurePlatform
     * @SuppressWarnings("php:S107")
     */
    public function __construct(
        string $id,
        string $name,
        float $lat,
        float $lon,
        ?DTM $arrival=null,
        ?DTM $departure=null,
        ?string $platform=null
    )
    {
        $this->stop = new Stop($id, $name, $lat, $lon);
        $this->remarks = [];
        $this->arrival = $arrival;
        $this->plannedArrival = $arrival;
        $this->arrivalDelay = null;
        $this->arrivalPlatform = $platform;
        $this->plannedArrivalPlatform = $platform;
        $this->arrivalPrognosisType = null;
        $this->departure = $departure;
        $this->plannedDeparture = $departure;
        $this->departureDelay = null;
        $this->departurePlatform = $platform;
        $this->plannedDeparturePlatform = $platform;
        $this->departurePrognosisType = null;
    }
}
