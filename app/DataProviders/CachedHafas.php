<?php

namespace App\DataProviders;

use App\Enum\TravelType;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CachedHafas extends Hafas implements DataProviderInterface
{
    public function fetchHafasTrip(string $tripID, string $lineName): Trip {
        $key = CacheKey::getHafasTripKey($tripID, $lineName);

        return $this->remember(
            $key,
            now()->addMinutes(15),
            function() use ($tripID, $lineName) {
                return parent::fetchHafasTrip($tripID, $lineName);
            },
            HCK::TRIPS_SUCCESS
        );
    }

    public function getStations(string $query, int $results = 10): Collection {
        $key = CacheKey::getHafasStationsKey($query);

        return $this->remember(
            $key,
            now()->addMinutes(15),
            function() use ($query, $results) {
                return parent::getStations($query, $results);
            },
            HCK::LOCATIONS_SUCCESS
        );
    }

    public function getDepartures(Station $station, Carbon $when, int $duration = 15, TravelType|null $type = null, bool $localtime = false): Collection {
        $filterWhen = clone $when;
        $when       = clone $when;
        $when->subMinutes(2);
        // set cache when minutes to 0, 15, 30 or 45
        $when->minute = (int) (floor($when->minute / 15) * 15);
        $when->second = 0;

        // set duration longer than 15 minutes
        $duration = $duration < 15 ? 30 : $duration;

        $key = CacheKey::getHafasDeparturesKey($station->id, $when, $localtime, $type);

        $departures = $this->remember(
            $key,
            now()->addMinutes(15),
            function() use ($station, $when, $duration, $type, $localtime) {
                return parent::getDepartures($station, $when, $duration, $type, $localtime);
            },
            HCK::DEPARTURES_SUCCESS
        );

        // filter entries by when and duration
        return $departures->filter(function($departure) use ($filterWhen, $duration) {
            $depWhen = Carbon::parse($departure->when);
            return $depWhen->between($filterWhen, $filterWhen->copy()->addMinutes($duration));
        });
    }

    public function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $key = CacheKey::getHafasByRilIdentifierKey($rilIdentifier);

        return $this->remember(
            $key,
            now()->addMinutes(15),
            function() use ($rilIdentifier) {
                return parent::getStationByRilIdentifier($rilIdentifier);
            },
            HCK::STATIONS_SUCCESS
        );
    }

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier): Collection {
        $key = CacheKey::getHafasStationsFuzzyKey($rilIdentifier);

        return $this->remember(
            $key,
            now()->addMinutes(15),
            function() use ($rilIdentifier) {
                return parent::getStationsByFuzzyRilIdentifier($rilIdentifier);
            },
            HCK::STATIONS_SUCCESS
        );
    }

    private function remember(string $key, Carbon $expires, callable $callback, ?string $ident = null): mixed {
        if (Cache::has($key)) {
            CacheKey::increment(CacheKey::getHafasCacheHitKey($ident));
            return Cache::get($key);
        }

        try {
            $result = $callback();
            CacheKey::increment(CacheKey::getHafasCacheSetKey($ident));
            Cache::put($key, $result, $expires);
            return $result;
        } catch (Throwable $e) {
            Cache::put($key, null, $expires);
            throw $e;
        }
    }
}
