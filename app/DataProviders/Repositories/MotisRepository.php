<?php

namespace App\DataProviders\Repositories;

use App\Dto\Coordinate;
use App\Enum\DataProvider;
use App\Helpers\Formatter;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\GeoService;
use Illuminate\Support\Collection;

class MotisRepository
{
    private GeoService $geoService;
    private const string TYPE = 'motis';

    public function __construct(?GeoService $geoService = null)
    {
        $this->geoService = $geoService ?? new GeoService();
    }

    public function createStation(mixed $rawStation, DataProvider $source): Station
    {
        $coordinates = new Coordinate($rawStation['lat'], $rawStation['lon']);
        $bbox = $this->geoService->getBoundingBox($coordinates, 100);

        $stations = Station::whereBetween('latitude', [$bbox->lowerRight->latitude, $bbox->upperLeft->latitude])
            ->whereBetween('longitude', [$bbox->lowerRight->longitude, $bbox->upperLeft->longitude])
            ->get();

        $simplifiedRawStationName = Formatter::simplifyStationName($rawStation['name']);
        $stations = $stations->map(function ($station) use ($simplifiedRawStationName) {
            $stationName = Formatter::simplifyStationName($station->name);

            similar_text($stationName, $simplifiedRawStationName, $percent);
            $station->motisRepositoryTempPercent = $percent;
            return $station;
        });

        $stations = $stations->filter(function ($station) {
            return $station->motisRepositoryTempPercent > 90;
        });
        $stations = $stations->sortBy('ibnr', SORT_ASC);
        $stations = $stations->sortBy('motisRepositoryTempPercent', SORT_ASC);

        if ($stations->isEmpty()) {
            $station = new Station([
                'name' => $rawStation['name'],
                'latitude' => $rawStation['lat'],
                'longitude' => $rawStation['lon']
            ]);
            $station->save();
        } else {
            $station = $stations->first();
        }

        StationIdentifier::updateOrCreate(
            [
                'type' => self::TYPE,
                'origin' => $source->value,
                'identifier' => $rawStation['stopId'],
            ],
            [
                'station_id' => $station->id,
                'name' => $rawStation['name']
            ]
        );
        return $station;
    }

    public function getStationsByIdentifiers(string|array $stationIds, DataProvider $source): Collection
    {
        if (is_string($stationIds)) {
            $stationIds = [$stationIds];
        }

        return Station::whereRelation('stationIdentifiers', function ($query) use ($stationIds, $source) {
            $query->whereIn('identifier', $stationIds)
                ->where('type', static::TYPE)
                ->where('origin', $source->value);
        })->get();
    }
}
