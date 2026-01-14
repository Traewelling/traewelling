<?php

namespace App\DataProviders;

use App\Dto\Internal\FilteredDepartures;
use App\Enum\TravelType;
use App\Models\Station;
use Carbon\Carbon;

interface DataProviderInterface
{
    public function fetchHafasTrip(string $tripID, string $lineName);

    public function fetchRawHafasTrip(string $tripId, string $lineName);

    public function getStations(string $query, int $results);

    /** @deprecated use getFilteredDepartures*/
    public function getDepartures(Station $station, Carbon $when, int $duration = 15, ?TravelType $type = null, bool $localtime = false);

    public function getFilteredDepartures(Station $station, Carbon $when, int $duration = 15, ?TravelType $type = null, bool $localtime = false): FilteredDepartures;

    public function getNearbyStations(float $latitude, float $longitude, int $results);

    /**
     * @deprecated only needed for HAFAS. Use StationRepository->getStationByName() instead
     */
    public function getStationByRilIdentifier(string $rilIdentifier);
}
