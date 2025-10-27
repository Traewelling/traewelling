<?php

namespace App\DataProviders\Repositories;

use App\Dto\Coordinate;
use App\Enum\DataProvider;
use App\Helpers\Formatter;
use App\Models\Area;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\GeoService;
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

    public function updateStationIdentifier(?Station $station, string $identifier, DataProvider $source, string $type = 'motis', ?float $latitude = null, ?float $longitude = null): void {
        if (!$station) {
            return;
        }

        $payload = [
            'station_id' => $station->id,
            'name'       => $station->name,
        ];

        if ($latitude !== null && $longitude !== null) {
            $payload['latitude']  = $latitude;
            $payload['longitude'] = $longitude;
        }

        StationIdentifier::updateOrCreate(
            [
                'type'       => $type,
                'origin'     => $source->value,
                'identifier' => $identifier,
            ],
            $payload
        );
    }

    public function createMotisStation(mixed $rawStation, DataProvider $source): Station {
        $coordinates = new Coordinate($rawStation['lat'], $rawStation['lon']);
        $bbox        = $this->geoService->getBoundingBox($coordinates, config('trwl.motis.nearby_radius'));

        $stations = Station::whereBetween('latitude', [$bbox->lowerRight->latitude, $bbox->upperLeft->latitude])
                           ->whereBetween('longitude', [$bbox->lowerRight->longitude, $bbox->upperLeft->longitude])
                           ->get();

        $city                     = Formatter::getCityFromAreas($rawStation['areas'] ?? []);
        $simplifiedRawStationName = Formatter::simplifyStationName($rawStation['name'], $city);
        $stations                 = $stations->map(function($station) use ($simplifiedRawStationName, $city) {
            $stationName = Formatter::simplifyStationName($station->name, $city);

            similar_text($stationName, $simplifiedRawStationName, $percent);
            $station->motisRepositoryTempPercent = $percent;
            return $station;
        });

        $stations = $stations->filter(function($station) {
            return $station->motisRepositoryTempPercent > 90;
        });
        $stations = $stations->sortBy([
                                          ['ibnr', 'desc'],
                                          ['relevance', 'desc'],
                                          ['motisRepositoryTempPercent', 'desc']
                                      ]);

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

        if (!empty($rawStation['areas'])) {
            $this->updateStationAreas($station, $rawStation['areas']);
        }

        StationIdentifier::updateOrCreate(
            [
                'type'       => 'motis',
                'origin'     => $source->value,
                'identifier' => $rawStation['stopId'],
            ],
            [
                'station_id' => $station->id,
                'name'       => $rawStation['name'],
                'latitude'   => $coordinates->latitude,
                'longitude'  => $coordinates->longitude,
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

    public function getStationIdentifierByIdentifier(
        string $identifier,
        DataProvider $source,
        string $type = 'motis'
    ): ?StationIdentifier {
        return StationIdentifier::where([
                                            'identifier' => $identifier,
                                            'origin'     => $source->value,
                                            'type'       => $type,
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
            $ifopt   = str_replace('de-DELFI_', '', $stationId);
            $station = $this->getStationByIfopt($ifopt);
            $this->updateStationIdentifier($station, $stationId, $source, 'motis', $latitude, $longitude);
        }
        return $station;
    }

    public function resetRelevance(StationIdentifier $identifier): void {
        $identifier->relevance = 0;
        $identifier->save();
    }
}
