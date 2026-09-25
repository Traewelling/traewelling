<?php

namespace App\DataProviders\Repositories;

use App\Dto\Coordinate;
use App\Enum\DataProvider;
use App\Enum\StationIdentifierType;
use App\Helpers\Formatter;
use App\Models\Area;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\GeoService;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Collection;

class StationRepository
{
    private GeoService $geoService;

    public function __construct(?GeoService $geoService = null)
    {
        $this->geoService = $geoService ?? new GeoService();
    }

    public function getStationsByIdentifiers(string|array $stationIds, DataProvider $source, string $type = 'motis'): Collection
    {
        if (is_string($stationIds)) {
            $stationIds = [$stationIds];
        }

        return Station::with(['areas', 'stationIdentifiers'])
            ->whereRelation('stationIdentifiers', function ($query) use ($stationIds, $source, $type) {
                $query->whereIn('identifier', $stationIds)
                    ->where('type', $type)
                    ->where('origin', $source->value);
            })->get();
    }

    /**
     * Some feeds reuse a stop id for a different stop in a later export. When the fresh coordinates are far
     * away from the station the stop id is mapped to, the identifier row is retired, so that the stop id is
     * treated as unknown and gets a new row. The retired row keeps its station and coordinates, because
     * stopovers and route segments refer to it.
     *
     * @return bool whether the stop id belongs to another stop now
     */
    public function retireMotisIdentifierIfReused(Station $station, string $stopId, DataProvider $source, ?float $latitude, ?float $longitude): bool
    {
        if (!$this->isStationFarFrom($station, $latitude, $longitude)) {
            return false;
        }

        // Guarded by the old identifier, so that concurrent requests retire the row only once
        StationIdentifier::where('type', StationIdentifierType::MOTIS)
            ->where('origin', $source->value)
            ->where('identifier', $stopId)
            ->where('station_id', $station->id)
            ->update([
                'identifier' => $stopId . StationIdentifier::RETIRED_MARKER . now()->timestamp,
                'relevance' => -9_000,
            ]);

        return true;
    }

    /**
     * Feeds with non-static stop ids (e.g. CZ, or the purely numeric DELFI ids) assign the
     * same stop id to a different physical stop in a later export. A cached station farther away than the
     * configured threshold from the fresh coordinates therefore belongs to another stop.
     */
    private function isStationFarFrom(Station $station, ?float $latitude, ?float $longitude): bool
    {
        if ($station->latitude === null || $station->longitude === null || $latitude === null || $longitude === null) {
            return false;
        }

        $distance = $this->geoService->getDistance(
            new Coordinate((float) $station->latitude, (float) $station->longitude),
            new Coordinate($latitude, $longitude),
        );

        return $distance > config('trwl.motis.max_cache_distance');
    }

    public function getStationByIfopt(string $ifopt): ?Station
    {
        // Check direct IFOPT identifier first
        $station = StationIdentifier::with(['station.areas', 'station.stationIdentifiers'])
            ->where('type', StationIdentifierType::IFOPT->value)
            ->where('identifier', $ifopt)
            ->first()
            ?->station;

        if ($station !== null) {
            return $station;
        }

        // Fall back to DELFI MOTIS identifier (stored as "de-DELFI_{ifopt}")
        return StationIdentifier::with(['station.areas', 'station.stationIdentifiers'])
            ->where('type', StationIdentifierType::MOTIS->value)
            ->where('identifier', 'de-DELFI_' . $ifopt)
            ->first()
            ?->station;
    }

    public function updateStationIdentifier(?Station $station, string $identifier, ?DataProvider $source = null, StationIdentifierType $type = StationIdentifierType::MOTIS, ?float $latitude = null, ?float $longitude = null): void
    {
        if (!$station) {
            return;
        }

        $payload = [
            'station_id' => $station->id,
            'name' => $station->name,
        ];

        if ($latitude !== null && $longitude !== null) {
            $payload['latitude'] = $latitude;
            $payload['longitude'] = $longitude;
        }

        StationIdentifier::updateOrCreate(
            [
                'type' => $type,
                'origin' => $source?->value ?? null,
                'identifier' => $identifier,
            ],
            $payload
        );
    }

    public function createMotisStationIdentifier(mixed $rawStation, DataProvider $source): Station
    {
        $areas = $rawStation['areas'] ?? [];
        $coordinates = new Coordinate($rawStation['lat'], $rawStation['lon']);

        $stations = $this->getStationsByNameBias($coordinates, $rawStation['name'], $areas);

        if ($stations->isEmpty()) {
            $station = new Station([
                'name' => $rawStation['name'],
                'latitude' => $rawStation['lat'],
                'longitude' => $rawStation['lon'],
            ]);
            $station->save();
        } else {
            $station = $stations->first();
        }

        if (!empty($areas ?? null)) {
            $this->updateStationAreas($station, $areas);
        }

        StationIdentifier::updateOrCreate(
            [
                'type' => StationIdentifierType::MOTIS,
                'origin' => $source->value,
                'identifier' => $rawStation['stopId'],
            ],
            [
                'station_id' => $station->id,
                'name' => $rawStation['name'],
                'latitude' => $coordinates->latitude,
                'longitude' => $coordinates->longitude,
            ]
        );

        $ifopt = $this->extractIfoptFromMotisId($rawStation['stopId']);
        if ($ifopt !== null) {
            $this->ensureIfoptIdentifier($station, $ifopt);
        }

        return $station;
    }

    /**
     * Extracts the base IFOPT (country:area:stop, 3 parts) from a MOTIS stop identifier.
     * MOTIS identifiers follow the pattern "{source}_{ifopt}[:{extra}...]".
     * Works for de-DELFI, at-PTA-*, at-Railway-*, and similar sources.
     * Returns null if no IFOPT pattern is found.
     */
    private function extractIfoptFromMotisId(string $motisId): ?string
    {
        $underscorePos = strpos($motisId, '_');
        if ($underscorePos === false) {
            return null;
        }

        $afterUnderscore = substr($motisId, $underscorePos + 1);
        $parts = explode(':', $afterUnderscore);

        if (count($parts) < 3 || !preg_match('/^[a-z]{2}$/i', $parts[0]) || !is_numeric($parts[2])) {
            return null;
        }

        return implode(':', array_slice($parts, 0, 3));
    }

    public function getStationByrilIdentifier(string $rilIdentifier): ?Station
    {
        return StationIdentifier::with('station')
            ->where('type', StationIdentifierType::DE_DB_RIL100->value)
            ->where('identifier', $rilIdentifier)
            ->first()
            ?->station;
    }

    public function updateStationAreas(Station $station, $areas): void
    {
        $station->load('areas');
        $newAreas = [];
        foreach ($areas as $area) {
            if (!$area['default'] && (int) $area['adminLevel'] !== 2) {
                continue;
            }
            $areaModel = Area::updateOrCreate([
                'name' => $area['name'],
                'adminLevel' => $area['adminLevel'] ?? 0,
            ]);

            $newAreas[$areaModel->id] = ['default' => $area['default'] ?? 0];
        }

        $station->areas()->sync($newAreas);
    }

    public function getStationIdentifierByIdentifier(
        string $identifier,
        DataProvider $source,
        string $type = 'motis'
    ): ?StationIdentifier {
        return StationIdentifier::where([
            'identifier' => $identifier,
            'origin' => $source->value,
            'type' => $type,
        ])->first();
    }

    public function updateOrCreateByIfopt(
        mixed $stationId,
        DataProvider $source,
        ?float $latitude = null,
        ?float $longitude = null
    ): ?Station {
        $station = null;
        // currently we can only handle DELFI, because other providers don't seem to use (real) ifopt ids
        if (str_starts_with($stationId, 'de-DELFI_')) {
            $ifopt = str_replace('de-DELFI_', '', $stationId);
            $station = $this->getStationByIfopt($ifopt);
            $this->updateStationIdentifier($station, $stationId, $source, StationIdentifierType::MOTIS, $latitude, $longitude);

            if ($station !== null) {
                $baseIfopt = $this->extractIfoptFromMotisId($stationId);
                if ($baseIfopt !== null) {
                    $this->ensureIfoptIdentifier($station, $baseIfopt);
                }
            }
        }

        return $station;
    }

    private function ensureIfoptIdentifier(Station $station, string $ifopt): void
    {
        $existing = StationIdentifier::where('type', StationIdentifierType::IFOPT)
            ->where('identifier', $ifopt)
            ->first();

        if ($existing !== null) {
            return;
        }

        StationIdentifier::create([
            'type' => StationIdentifierType::IFOPT,
            'identifier' => $ifopt,
            'station_id' => $station->id,
        ]);
    }

    public function resetRelevance(StationIdentifier $identifier): void
    {
        $identifier->relevance = 0;
        $identifier->save();
    }

    /**
     * @return DbCollection<Station>|Collection<int,Station>
     */
    public function getStationsByNameBias(Coordinate $coordinates, string $requestStationName, array $motisAreas = []): Collection|DbCollection
    {
        $bbox = $this->geoService->getBoundingBox($coordinates, config('trwl.motis.nearby_radius'));

        $stations = Station::whereBetween('latitude', [$bbox->lowerRight->latitude, $bbox->upperLeft->latitude])
            ->whereBetween('longitude', [$bbox->lowerRight->longitude, $bbox->upperLeft->longitude])
            ->get();

        $city = Formatter::getCityFromAreas($motisAreas);
        $simplifiedRequestStationName = Formatter::simplifyStationName($requestStationName, $city);
        $stations = $stations->map(function ($station) use ($simplifiedRequestStationName, $city) {
            $stationName = Formatter::simplifyStationName($station->name, $city);

            similar_text($stationName, $simplifiedRequestStationName, $percent);
            $station->tempNameSimilarityPercent = $percent;

            return $station;
        });

        $stations = $stations->filter(function ($station) {
            return $station->tempNameSimilarityPercent > 90;
        });

        return $stations->sortBy([
            ['relevance', 'desc'],
            ['tempNameSimilarityPercent', 'desc'],
        ]);
    }
}
