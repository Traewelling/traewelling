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

    public function __construct(?GeoService $geoService = null) {
        $this->geoService = $geoService ?? new GeoService();
    }

    /**
     * @param stdClass $hafasStop
     *
     * @return Station
     * @throws PDOException
     */
    public static function parseHafasStopObject(stdClass $hafasStop): Station {

        $data = [
            'name'      => $hafasStop->name,
            'latitude'  => $hafasStop->location?->latitude,
            'longitude' => $hafasStop->location?->longitude,
        ];

        if (isset($hafasStop->ril100)) {
            $data['rilIdentifier'] = $hafasStop->ril100;
        }

        return Station::updateOrCreate(
            ['ibnr' => $hafasStop->id],
            $data
        );
    }

    public static function parseHafasStops(array $hafasResponse): Collection {
        $payload = [];
        foreach ($hafasResponse as $hafasStation) {
            $payload[] = [
                'ibnr'      => $hafasStation->id,
                'name'      => $hafasStation->name,
                'latitude'  => $hafasStation?->location?->latitude,
                'longitude' => $hafasStation?->location?->longitude,
            ];
        }
        return self::upsertStations($payload);
    }

    public static function upsertStations(array $payload) {
        $ibnrs = array_column($payload, 'ibnr');
        if (empty($ibnrs)) {
            return new Collection();
        }
        Station::upsert($payload, ['ibnr'], ['name', 'latitude', 'longitude']);
        return Station::whereIn('ibnr', $ibnrs)->get()
                      ->sortBy(function(Station $station) use ($ibnrs) {
                          return array_search($station->ibnr, $ibnrs);
                      })
                      ->values();
    }

    /**
     * @return Collection|Station[]
     */
    public function getStationsByIdentifiers(string|array $stationIds, DataProvider $source, string $type = 'motis'): Collection {
        if (is_string($stationIds)) {
            $stationIds = [$stationIds];
        }

        return Station::with('areas')
                      ->whereRelation('stationIdentifiers', function($query) use ($stationIds, $source, $type) {
                          $query->whereIn('identifier', $stationIds)
                                ->where('type', $type)
                                ->where('origin', $source->value);
                      })->get();
    }

    public function getStationByIfopt(string $ifopt): ?Station {
        $ifoptParts = explode(':', $ifopt);
        if (count($ifoptParts) < 3) {
            return null;
        }
        return Station::with(['areas', 'stationIdentifiers'])->where([
                                                                         'ifopt_a' => $ifoptParts[0],
                                                                         'ifopt_b' => $ifoptParts[1],
                                                                         'ifopt_c' => $ifoptParts[2],
                                                                     ])->first();
    }

    public function updateStationIdentifier(?Station $station, string $identifier, ?DataProvider $source = null, StationIdentifierType $type = StationIdentifierType::MOTIS): void {
        if (!$station) {
            return;
        }
        StationIdentifier::updateOrCreate(
            [
                'type'       => $type,
                'origin'     => $source?->value ?? null,
                'identifier' => $identifier,
            ],
            [
                'station_id' => $station->id,
                'name'       => $station->name
            ]
        );
    }

    public function createMotisStationIdentifier(mixed $rawStation, DataProvider $source): Station {
        $areas = $rawStation['areas'] ?? [];
        $coordinates = new Coordinate($rawStation['lat'], $rawStation['lon']);

        $stations = $this->getStationsByNameBias($coordinates, $rawStation['name'], $areas);

        if ($stations->isEmpty()) {
            $station = new Station([
                                       'name'      => $rawStation['name'],
                                       'latitude'  => $rawStation['lat'],
                                       'longitude' => $rawStation['lon']
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
                'type'       => StationIdentifierType::MOTIS,
                'origin'     => $source->value,
                'identifier' => $rawStation['stopId'],
            ],
            [
                'station_id' => $station->id,
                'name'       => $rawStation['name']
            ]
        );
        return $station;
    }

    public function getStationByrilIdentifier(string $rilIdentifier): ?Station {
        return Station::where('rilIdentifier', $rilIdentifier)->first();
    }

    public function updateStationAreas(Station $station, $areas): void {
        $station->load('areas');
        $newAreas = [];
        foreach ($areas as $area) {
            if (!$area['default'] && (int) $area['adminLevel'] !== 2) {
                continue;
            }
            $areaModel = Area::updateOrCreate([
                                                  'name'       => $area['name'],
                                                  'adminLevel' => $area['adminLevel'] ?? 0
                                              ]);

            $newAreas[$areaModel->id] = ['default' => $area['default'] ?? 0];
        }

        $station->areas()->sync($newAreas);
    }

    public function updateOrCreateByIfopt(mixed $stationId, DataProvider $source): ?Station {
        $station = null;
        // currently we can only handle DELFI, because other providers don't seem to use (real) ifopt ids
        if (str_starts_with($stationId, 'de-DELFI_')) {
            $ifopt   = str_replace('de-DELFI_', '', $stationId);
            $station = $this->getStationByIfopt($ifopt);
            $this->updateStationIdentifier($station, $stationId, $source);
        }
        return $station;
    }

    public function resetRelevance(StationIdentifier $identifier): void {
        $identifier->relevance = 0;
        $identifier->save();
    }

    /**
     *
     * @return DbCollection<Station>|Collection<int,Station>
     */
    public function getStationsByNameBias(Coordinate $coordinates, string $requestStationName, array $motisAreas = []): Collection|DbCollection {
        $bbox        = $this->geoService->getBoundingBox($coordinates, config('trwl.motis.nearby_radius'));

        $stations = Station::whereBetween('latitude', [$bbox->lowerRight->latitude, $bbox->upperLeft->latitude])
                           ->whereBetween('longitude', [$bbox->lowerRight->longitude, $bbox->upperLeft->longitude])
                           ->get();

        $city                     = Formatter::getCityFromAreas($motisAreas);
        $simplifiedRequestStationName = Formatter::simplifyStationName($requestStationName, $city);
        $stations                 = $stations->map(function($station) use ($simplifiedRequestStationName, $city) {
            $stationName = Formatter::simplifyStationName($station->name, $city);

            similar_text($stationName, $simplifiedRequestStationName, $percent);
            $station->tempNameSimilarityPercent = $percent;
            return $station;
        });

        $stations = $stations->filter(function($station) {
            return $station->tempNameSimilarityPercent > 90;
        });
        return $stations->sortBy([
                                          ['ibnr', 'desc'],
                                          ['relevance', 'desc'],
                                          ['tempNameSimilarityPercent', 'desc']
                                      ]);
    }
}
