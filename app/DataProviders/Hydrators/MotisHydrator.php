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
use App\Hydrators\DepartureHydrator;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\LicenseService;
use App\Services\OperatorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        $station = $station ?? $this->stationRepository->updateOrCreateByIfopt($rawStop['stopId'], $source);
        $station = $station ?? $this->stationRepository->createMotisStation($rawStop, $source);

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
        ];
    }

    private function getCategoryFromLeg(mixed $leg): string {
        $motisCategory = MotisCategory::tryFrom($leg['mode']);
        if ($motisCategory === null) {
            Log::error('Unknown Motis category', [
                'mode' => $leg['mode'],
            ]);
            $motisCategory = MotisCategory::REGIONAL_RAIL;
        }

        return $motisCategory->getHTT()->value;
    }

    public function getTripData(mixed $leg, string $lineName, DataProvider $source): array {
        $originStation      = $this->stationRepository->getStationsByIdentifiers($leg['from']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['from']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStation($leg['from'], $source);
        $destinationStation = $this->stationRepository->getStationsByIdentifiers($leg['to']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['to']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStation($leg['to'], $source);
        $departure          = isset($leg['from']['departure']) ? Carbon::parse($leg['from']['departure']) : null;
        $arrival            = isset($leg['to']['arrival']) ? Carbon::parse($leg['to']['arrival']) : null;
        $category           = $this->getCategoryFromLeg($leg);
        $tripLineName       = !empty($leg['routeShortName']) ? $leg['routeShortName'] : $lineName;
        $license            = $this->motisRepository->getActiveLicense($leg['source'], $source);
        $operator           = $this->parseOperator($leg);

        return [
            'category'                => $category,
            'number'                  => $tripLineName,
            'linename'                => $tripLineName,
            'journey_number'          => null,
            'operator_id'             => $operator?->id,
            'origin_id'               => $originStation->id,
            'destination_id'          => $destinationStation->id,
            'polyline_id'             => null, //TODO
            'departure'               => $departure,
            'arrival'                 => $arrival,
            'source'                  => $source->value,
            'motis_source'            => $source->value . '/' . $leg['source'],
            'motis_source_license_id' => $license?->id ?? null,
        ];
    }

    private function parseOperator(array $leg): ?HafasOperator {
        return $this->operatorService->parseTransitousOperator(
            agencyId:   $leg['agencyId'] ?? null,
            agencyName: $leg['agencyName'] ?? null,
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
            $rawDepartureStation = $rawDeparture['place'];
            $tripLineName        = $rawDeparture['routeShortName'] ?? '';
            $hafasTravelType     = $this->getCategoryFromLeg($rawDeparture);

            $platformPlanned = $rawDepartureStation['scheduledTrack'] ?? '';
            $platformReal    = $rawDepartureStation['track'] ?? $platformPlanned;
            try {
                $departureStation = $this->stationRepository->getStationsByIdentifiers([$rawDepartureStation['stopId']], $source)->first();
                if ($departureStation === null) {
                    $stationId        = $rawDepartureStation['stopId'];
                    $departureStation = $this->stationRepository->updateOrCreateByIfopt($stationId, $source, $this);
                    // if station does not exist, request it from API
                    $departureStation = $departureStation ?? $this->stationRepository->createMotisStation($rawDepartureStation, $source);
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
                                      tripId:        $tripId,
                                      direction:     $rawDeparture['headsign'],
                                      lineName:      $tripLineName,
                                      number:        $tripId,
                                      category:      $hafasTravelType,
                                      journeyNumber: $tripId,
                                      operator:      $this->parseOperator($rawDeparture),
                                  ),
                plannedPlatform:  $platformPlanned,
                realPlatform:     $platformReal,
            );

            $departures->push($departure);
        }

        return new FilteredDepartures(DepartureHydrator::map($departures), collect(array_values($removedEntries)), $removedCount);
    }

}
