<?php

declare(strict_types=1);

namespace App\DataProviders\Hydrators;

use App\DataProviders\Repositories\MotisLicenseRepository;
use App\DataProviders\Repositories\StationRepository;
use App\Dto\Internal\BahnTrip;
use App\Dto\Internal\Departure;
use App\Dto\Internal\FilteredDepartures;
use App\Enum\DataProvider;
use App\Enum\MotisCategory;
use App\Http\Controllers\TransportController;
use App\Hydrators\DepartureHydrator;
use App\Models\Operator;
use App\Models\PolyLine;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\LicenseService;
use App\Services\OperatorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Traewelling\GooglePolyline\PolylineTranscoder;

class MotisHydrator
{

    private MotisLicenseRepository $motisRepository;
    private StationRepository      $stationRepository;
    private OperatorService        $operatorService;
    private LicenseService         $licenseService;

    public function __construct(
        ?MotisLicenseRepository $motisRepository = null,
        ?StationRepository      $stationRepository = null,
        ?OperatorService        $operatorService = null,
        ?LicenseService         $licenseService = null
    ) {
        $this->motisRepository   = $motisRepository ?? new MotisLicenseRepository();
        $this->stationRepository = $stationRepository ?? new StationRepository();
        $this->operatorService   = $operatorService ?? new OperatorService();
        $this->licenseService    = $licenseService ?? new LicenseService();
    }

    public function parseLegToNewStopovers(mixed $leg, DataProvider $source): Collection {
        $rawStopovers        = $leg['intermediateStops'];
        $stopoverCacheFromDB = $this->stationRepository->getStationsByIdentifiers(array_column($rawStopovers, 'stopId'), $source);

        // add origin and destination to stopovers
        $rawStopovers[] = $leg['from'];
        $rawStopovers[] = $leg['to'];
        $realTime       = $leg['realTime'] ?? false;

        $stopovers = collect();
        foreach ($rawStopovers as $rawStop) {
            $station = $stopoverCacheFromDB->where('stationIdentifiers', function($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                      ->where('type', 'motis')
                      ->where('origin', $source->value);
            })->first();

            $stopover = new Stopover($this->getStopoverData($station, $rawStop, $source, $realTime));
            $stopovers->push($stopover);
        }
        return $stopovers;
    }

    public function parseLegToUpdateStopovers(mixed $leg, Trip $trip, DataProvider $source): Collection {
        $rawStopovers        = $leg['intermediateStops'];
        $stopoverCacheFromDB = $this->stationRepository->getStationsByIdentifiers(array_column($rawStopovers, 'stopId'), $source);

        // add origin and destination to stopovers
        $rawStopovers[] = $leg['from'];
        $rawStopovers[] = $leg['to'];
        $realTime       = $leg['realTime'] ?? false;

        $stopovers = collect();
        $key       = ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'];
        foreach ($rawStopovers as $rawStop) {
            $station                 = $stopoverCacheFromDB->where('stationIdentifiers', function($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                      ->where('type', 'motis')
                      ->where('origin', $source->value);
            })->first();
            $stopoverData            = $this->getStopoverData($station, $rawStop, $source, $realTime);
            $stopoverData['trip_id'] = $trip->trip_id;

            try {
                $stopover = Stopover::upsert(
                    $stopoverData,
                    $key,
                    [
                        'arrival_real',
                        'departure_real',
                        'arrival_platform_real',
                        'departure_platform_real',
                        'cancelled',
                    ]
                );
                $stopovers->push($stopover);
            } catch (Exception $exception) {
                Log::error('Failed to upsert stopover', [
                    'stopover' => $rawStop,
                    'error'    => $exception->getMessage(),
                ]);
            }
        }

        return $stopovers;
    }

    private function getStopoverData($station, mixed $rawStop, DataProvider $source, bool $realTime = false): array {
        $station    = $station ?? $this->stationRepository->updateOrCreateByIfopt($rawStop['stopId'], $source, $rawStop['lat'], $rawStop['lon']);
        $station    = $station ?? $this->stationRepository->createMotisStationIdentifier($rawStop, $source);
        $identifier = $this->stationRepository->getStationIdentifierByIdentifier($rawStop['stopId'], $source);

        $departurePlanned = isset($rawStop['scheduledDeparture']) ? Carbon::parse($rawStop['scheduledDeparture']) : null;
        $departureReal    = isset($rawStop['departure']) ? Carbon::parse($rawStop['departure']) : null;
        $arrivalPlanned   = isset($rawStop['scheduledArrival']) ? Carbon::parse($rawStop['scheduledArrival']) : null;
        $arrivalReal      = isset($rawStop['arrival']) ? Carbon::parse($rawStop['arrival']) : null;
        // new API does not differ between departure and arrival platform
        $platformPlanned = $rawStop['scheduledTrack'] ?? null;
        $platformReal    = $rawStop['track'] ?? $platformPlanned;

        return [
            'train_station_id'           => $station->id,
            'arrival_planned'            => $arrivalPlanned ?? $departurePlanned,
            'arrival_real'               => $realTime ? $arrivalReal ?? $departureReal ?? null : null,
            'departure_planned'          => $departurePlanned ?? $arrivalPlanned,
            'departure_real'             => $realTime ? $departureReal ?? $arrivalReal ?? null : null,
            'arrival_platform_planned'   => $platformPlanned,
            'departure_platform_planned' => $platformPlanned,
            'arrival_platform_real'      => $realTime ? $platformReal : null,
            'departure_platform_real'    => $realTime ? $platformReal : null,
            'station_identifier_id'      => $identifier?->id ?? null,
        ];
    }

    private function getCategoryFromLeg(mixed $leg): MotisCategory {
        $motisCategory = MotisCategory::tryFrom($leg['mode']);
        if ($motisCategory === null) {
            Log::error('Unknown Motis category', [
                'mode' => $leg['mode'],
            ]);
            $motisCategory = MotisCategory::REGIONAL_RAIL;
        }

        return $motisCategory;
    }

    private function getPolylineFromLeg(mixed $leg): PolyLine {
        $polylineModel = null;

        if (!empty($leg['legGeometry']['points']) && !empty($leg['legGeometry']['precision'])) {
            $precision   = $leg['legGeometry']['precision'];
            $transcoder  = new PolylineTranscoder();
            $coordinates = $transcoder->decodePolyline($leg['legGeometry']['points'], $precision);

            $features = [];
            foreach ($coordinates as $coord) {
                $features[] = [
                    'type'       => 'Feature',
                    'geometry'   => [
                        'type'        => 'Point',
                        'coordinates' => [$coord->getLongitude(), $coord->getLatitude()],
                    ],
                    'properties' => new \stdClass(),
                ];
            }

            // map stopovers to the closest point feature
            $allStops = array_merge([$leg['from']], $leg['intermediateStops'], [$leg['to']]);
            $stopIds  = array_column($allStops, 'stopId');
            $stations = $this->stationRepository->getStationsByIdentifiers($stopIds, DataProvider::TRANSITOUS)->keyBy('motis_id');

            foreach ($allStops as $stop) {
                if (!isset($stop['lon']) || !isset($stop['lat']) || !isset($stop['stopId'])) continue;

                // Find the internal station
                $station = $stations->get($stop['stopId'])
                           ?? $this->stationRepository->updateOrCreateByIfopt($stop['stopId'], DataProvider::TRANSITOUS)
                              ?? $this->stationRepository->createMotisStationIdentifier($stop, DataProvider::TRANSITOUS);

                if (!$station) continue;

                // Find closest polyline point
                $minDist    = null;
                $closestKey = null;
                foreach ($features as $key => $feature) {
                    $dist = pow($feature['geometry']['coordinates'][0] - $stop['lon'], 2)
                            + pow($feature['geometry']['coordinates'][1] - $stop['lat'], 2);
                    if ($minDist === null || $dist < $minDist) {
                        $minDist    = $dist;
                        $closestKey = $key;
                    }
                }
                if ($closestKey !== null) {
                    $features[$closestKey]['properties'] = [
                        'stationId'         => $station->id,
                        'id'                => $station->ibnr ?? null,
                        'name'              => $station->name ?? $stop['name'] ?? null,
                        'arrival_planned'   => $stop['scheduledArrival'] ?? null,
                        'departure_planned' => $stop['scheduledDeparture'] ?? null,
                    ];
                }
            }

            $geoJson = [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];

            $polylineModel = TransportController::getPolylineHash(json_encode($geoJson), 'motis');
        }

        return $polylineModel;
    }

    public function getTripData(mixed $leg, string $lineName, DataProvider $source): array {
        $originStation      = $this->stationRepository->getStationsByIdentifiers($leg['from']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['from']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStationIdentifier($leg['from'], $source);
        $destinationStation = $this->stationRepository->getStationsByIdentifiers($leg['to']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['to']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStationIdentifier($leg['to'], $source);
        $departure          = isset($leg['from']['departure']) ? Carbon::parse($leg['from']['departure']) : null;
        $arrival            = isset($leg['to']['arrival']) ? Carbon::parse($leg['to']['arrival']) : null;
        $mode               = $this->getCategoryFromLeg($leg);
        $category           = $mode->getHTT()->value;
        $tripLineName       = !empty($leg['displayName']) ? $leg['displayName'] : $lineName;
        $license            = $this->motisRepository->getActiveLicense($leg['source'], $source);
        $operator           = $this->parseOperator($leg, $source);
        $polyline           = $this->getPolylineFromLeg($leg);
        $shortTripName      = !empty($leg['tripShortName']) ? $leg['tripShortName'] : null;
        $shortTripName      = $shortTripName !== null ? preg_replace('/\D/', '', $shortTripName) : null;

        $payload = [
            'category'                => $category,
            'mode'                    => $mode,
            'number'                  => $tripLineName,
            'linename'                => $tripLineName,
            'route_color'             => $leg['routeColor'] ?? null,
            'route_text_color'        => $leg['routeTextColor'] ?? null,
            'operator_id'             => $operator?->id,
            'origin_id'               => $originStation->id,
            'destination_id'          => $destinationStation->id,
            'polyline_id'             => $polyline->id,
            'departure'               => $departure,
            'arrival'                 => $arrival,
            'source'                  => $source->value,
            'motis_source'            => $source->value . '/' . $leg['source'],
            'motis_source_license_id' => $license?->id ?? null,
        ];

        if (is_numeric($shortTripName)) {
            $payload['journey_number'] = $shortTripName;
        }

        return $payload;
    }

    private function parseOperator(array $leg, DataProvider $source): ?Operator {
        return $this->operatorService->parseTransitousOperator(
            motisAgencyId:   $leg['agencyId'] ?? null,
            motisAgencyName: $leg['agencyName'] ?? null,
            source:          $source,
        );
    }

    private function checkLicenseData($rawDeparture, DataProvider $source, array &$removedEntries, int &$removedCount): bool {
        if (config('trwl.motis.filter_licenses')) {
            // Check if the source is licensed under an acceptable license
            $license = $this->motisRepository->getLicense($rawDeparture['source'], $source);
            if (empty($license) || !$license->active) {
                [$country, $name] = $this->motisRepository->getCountryAndLicense($rawDeparture['source']);
                $licenseIdentifier = $name;
                $licenseName       = sprintf('%s: %s', strtoupper($country), $name);

                $license                            = $license ? $this->licenseService->getLicenseDataForSource($license) : null;
                $removedEntries[$licenseIdentifier] = $license ?? $licenseName;
                $removedCount++;
                return true;
            }
        }
        return false;
    }

    public function mapDepartures(mixed $entries, Station $station, DataProvider $source): FilteredDepartures {
        $departures     = collect();
        $removedEntries = [];
        $removedCount   = 0;

        foreach ($entries as $rawDeparture) {
            if ($this->checkLicenseData($rawDeparture, $source, $removedEntries, $removedCount)) {
                continue; // skip this entry if it does not have a valid license
            }

            //trip
            $tripId              = $rawDeparture['tripId'];
            $tripShortName       = $rawDeparture['tripShortName'] ?? '';
            $rawDepartureStation = $rawDeparture['place'];
            $tripLineName        = $rawDeparture['displayName'] ?? '';
            $mode                = $this->getCategoryFromLeg($rawDeparture);
            $hafasTravelType     = $mode->getHTT()->value;

            $platformPlanned = $rawDepartureStation['scheduledTrack'] ?? '';
            $platformReal    = $rawDepartureStation['track'] ?? $platformPlanned;
            try {
                $departureStation = $this->stationRepository->getStationsByIdentifiers([$rawDepartureStation['stopId']], $source)->first();
                if ($departureStation === null) {
                    $stationId        = $rawDepartureStation['stopId'];
                    $departureStation = $this->stationRepository->updateOrCreateByIfopt($stationId, $source, $rawDepartureStation['lat'], $rawDepartureStation['lon']);
                    // if station does not exist, request it from API
                    $departureStation = $departureStation ?? $this->stationRepository->createMotisStationIdentifier($rawDepartureStation, $source);
                }
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                $departureStation = $station;
            }

            $departure = new Departure(
                station:          $departureStation,
                plannedDeparture: Carbon::parse($rawDepartureStation['scheduledDeparture']),
                realDeparture:    !empty($rawDeparture['realTime']) ? Carbon::parse($rawDepartureStation['departure']) : null,
                trip:             new BahnTrip(
                                      tripId:            $tripId,
                                      direction:         $rawDeparture['headsign'],
                                      lineName:          $tripLineName,
                                      number:            $tripShortName,
                                      category:          $hafasTravelType,
                                      journeyNumber:     $tripShortName,
                                      operator:          $this->parseOperator($rawDeparture, $source),
                                      routeColor:        $rawDeparture['routeColor'] ?? null,
                                      routeTextColor:    $rawDeparture['routeTextColor'] ?? null,
                                      mode:              $mode
                                  ),
                plannedPlatform:  $platformPlanned,
                realPlatform:     $platformReal,
            );

            $departures->push($departure);
        }

        return new FilteredDepartures(DepartureHydrator::map($departures), collect(array_values($removedEntries)), $removedCount);
    }

}
