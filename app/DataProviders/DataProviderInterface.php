<?php

namespace App\DataProviders;

use App\Enum\TravelType;
use App\Models\Station;
use Carbon\Carbon;

interface DataProviderInterface
{
    public function fetchHafasTrip(string $tripID, string $lineName);

    public function fetchRawHafasTrip(string $tripId, string $lineName);

    public function getStations(string $query, int $results);

    public function getDepartures(Station $station, Carbon $when, int $duration = 15, ?TravelType $type = null, bool $localtime = false);

    public function getNearbyStations(float $latitude, float $longitude, int $results);

    public function getStationByRilIdentifier(string $rilIdentifier);

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier);
}
