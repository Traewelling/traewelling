<?php

namespace App\DataProviders\Repositories;

use App\Dto\Coordinate;
use App\Enum\DataProvider;
use App\Helpers\Formatter;
use App\Models\Area;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\GeoService;
use App\StationIdentifierType;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Collection;
use PDOException;
use stdClass;

class StationRepository
{
    private GeoService $geoService;

    public function __construct(?GeoService $geoService = null)
    {
        $this->geoService = $geoService ?? new GeoService();
    }

    /**
     * @throws PDOException
     */
    public static function parseHafasStopObject(stdClass $hafasStop): Station
    {
        // Try to find existing station by IBNR identifier
        $existingStation = Station::with(['areas', 'stationIdentifiers'])
            ->whereHas('stationIdentifiers', function ($query) use ($hafasStop) {
                $query->where('type', StationIdentifierType::DE_DB_IBNR)
                    ->where('identifier', $hafasStop->id);
            })
            ->first();

        $data = [
            'name' => $hafasStop->name,
            'latitude' => $hafasStop->location?->latitude,
            'longitude' => $hafasStop->location?->longitude,
        ];

        if ($existingStation) {
            $existingStation->update($data);
            $station = $existingStation;
        } else {
            $station = Station::create($data);
        }

        // Update IBNR identifier
        StationIdentifier::updateOrCreate(
            [
                'type' => StationIdentifierType::DE_DB_IBNR,
                'identifier' => $hafasStop->id,
            ],
            [
                'station_id' => $station->id,
                'name' => $station->name,
                'origin' => 'hafas',
            ]
        );

        // Update RIL100 identifier if present
        if (isset($hafasStop->ril100)) {
            StationIdentifier::updateOrCreate(
                [
                    'type' => StationIdentifierType::DE_DB_RIL100,
                    'identifier' => $hafasStop->ril100,
                ],
                [
                    'station_id' => $station->id,
                    'name' => $station->name,
                    'origin' => 'hafas',
                ]
            );
        }

        return $station;
    }

    public static function parseHafasStops(array $hafasResponse): Collection
    {
        $stations = new Collection();

        foreach ($hafasResponse as $hafasStation) {
            $station = self::parseHafasStopObject($hafasStation);
            $stations->push($station);
        }

        return $stations;
    }

    public static function upsertStations(array $payload)
    {
        // This method is deprecated and should use parseHafasStops instead
        // Keeping for backwards compatibility but refactoring to use identifiers
        $stations = new Collection();

        foreach ($payload as $stationData) {
            if (!isset($stationData['ibnr'])) {
                continue;
            }

            // Convert to stdClass to match parseHafasStopObject signature
            $hafasStop = (object) [
                'id' => $stationData['ibnr'],
                'name' => $stationData['name'],
                'location' => (object) [
                    'latitude' => $stationData['latitude'] ?? null,
                    'longitude' => $stationData['longitude'] ?? null,
                ],
            ];

            $station = self::parseHafasStopObject($hafasStop);
            $stations->push($station);
        }

        return $stations;
    }

    /**
     * @return Collection|Station[]
     */
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

    public function getStationByIfopt(string $ifopt): ?Station
    {
        return Station::with(['areas', 'stationIdentifiers'])
            ->whereHas('stationIdentifiers', function ($query) use ($ifopt) {
                $query->where('type', StationIdentifierType::DE_DB_IFOPT)
                    ->where('identifier', $ifopt);
            })
            ->first();
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

        return $station;
    }

    public function getStationByrilIdentifier(string $rilIdentifier): ?Station
    {
        return Station::with(['areas', 'stationIdentifiers'])
            ->whereHas('stationIdentifiers', function ($query) use ($rilIdentifier) {
                $query->where('type', StationIdentifierType::DE_DB_RIL100)
                    ->where('identifier', $rilIdentifier);
            })
            ->first();
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
        }

        return $station;
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
            function ($station) {
                // Stations with IBNR identifier should come first (lower value = higher priority)
                return $station->stationIdentifiers->where('type', StationIdentifierType::DE_DB_IBNR)->isEmpty() ? 1 : 0;
            },
            ['relevance', 'desc'],
            ['tempNameSimilarityPercent', 'desc'],
        ]);
    }
}
