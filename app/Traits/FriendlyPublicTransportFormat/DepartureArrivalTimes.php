<?php

namespace App\Traits\FriendlyPublicTransportFormat;

use App\Dto\FriendlyPublicTransportFormat\DTM;

trait DepartureArrivalTimes
{
    public readonly ?DTM $arrival;
    public readonly ?DTM $plannedArrival;
    public readonly ?int $arrivalDelay;
    public readonly ?string $arrivalPlatform;
    public readonly ?string $arrivalPrognosisType;
    public readonly ?string $plannedArrivalPlatform;
    public readonly ?DTM $departure;
    public readonly ?DTM $plannedDeparture;
    public readonly ?int $departureDelay;
    public readonly ?string $departurePlatform;
    public readonly ?string $departurePrognosisType;
    public readonly ?string $plannedDeparturePlatform;
}
