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
use App\Models\Operator;
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

    private StationRepository $stationRepository;

    private OperatorService $operatorService;

    private LicenseService $licenseService;

    public function __construct(
        ?MotisLicenseRepository $motisRepository = null,
        ?StationRepository $stationRepository = null,
        ?OperatorService $operatorService = null,
        ?LicenseService $licenseService = null
    ) {
        $this->motisRepository = $motisRepository ?? new MotisLicenseRepository();
        $this->stationRepository = $stationRepository ?? new StationRepository();
        $this->operatorService = $operatorService ?? new OperatorService();
        $this->licenseService = $licenseService ?? new LicenseService();
    }

    public function parseLegToNewStopovers(mixed $leg, DataProvider $source): Collection
    {
        $rawStopovers = $leg['intermediateStops'];
        $stopoverCacheFromDB = $this->stationRepository->getStationsByIdentifiers(array_column($rawStopovers, 'stopId'), $source);

        // add origin and destination to stopovers
        $rawStopovers[] = $leg['from'];
        $rawStopovers[] = $leg['to'];
        $realTime = $leg['realTime'] ?? false;

        $stopovers = collect();
        foreach ($rawStopovers as $rawStop) {
            $station = $stopoverCacheFromDB->where('stationIdentifiers', function ($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                    ->where('type', 'motis')
                    ->where('origin', $source->value);
            })->first();

            $stopover = new Stopover($this->getStopoverData($station, $rawStop, $source, $realTime));
            $stopovers->push($stopover);
        }

        return $stopovers;
    }

    public function parseLegToUpdateStopovers(mixed $leg, Trip $trip, DataProvider $source): Collection
    {
        $rawStopovers = $leg['intermediateStops'];
        $stopoverCacheFromDB = $this->stationRepository->getStationsByIdentifiers(array_column($rawStopovers, 'stopId'), $source);

        // add origin and destination to stopovers
        $rawStopovers[] = $leg['from'];
        $rawStopovers[] = $leg['to'];
        $realTime = $leg['realTime'] ?? false;

        $stopovers = collect();
        $key = ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'];
        foreach ($rawStopovers as $rawStop) {
            $station = $stopoverCacheFromDB->where('stationIdentifiers', function ($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                    ->where('type', 'motis')
                    ->where('origin', $source->value);
            })->first();
            $stopoverData = $this->getStopoverData($station, $rawStop, $source, $realTime);
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
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $stopovers;
    }

    private function getStopoverData($station, mixed $rawStop, DataProvider $source, bool $realTime = false): array
    {
        $station = $station ?? $this->stationRepository->updateOrCreateByIfopt($rawStop['stopId'], $source, $rawStop['lat'], $rawStop['lon']);
        $station = $station ?? $this->stationRepository->createMotisStationIdentifier($rawStop, $source);
        $identifier = $this->stationRepository->getStationIdentifierByIdentifier($rawStop['stopId'], $source);

        $isCancelled = (bool) ($rawStop['cancelled'] ?? false);
        $departurePlanned = isset($rawStop['scheduledDeparture']) ? Carbon::parse($rawStop['scheduledDeparture'])->utc() : null;
        $departureReal = isset($rawStop['departure']) ? Carbon::parse($rawStop['departure'])->utc() : null;
        $arrivalPlanned = isset($rawStop['scheduledArrival']) ? Carbon::parse($rawStop['scheduledArrival'])->utc() : null;
        $arrivalReal = isset($rawStop['arrival']) ? Carbon::parse($rawStop['arrival'])->utc() : null;
        // new API does not differ between departure and arrival platform
        $platformPlanned = $rawStop['scheduledTrack'] ?? null;
        $platformReal = $rawStop['track'] ?? $platformPlanned;

        return [
            'train_station_id' => $station->id,
            'arrival_planned' => $arrivalPlanned ?? $departurePlanned,
            'arrival_real' => !$isCancelled && $realTime ? $arrivalReal ?? $departureReal ?? null : null,
            'departure_planned' => $departurePlanned ?? $arrivalPlanned,
            'departure_real' => !$isCancelled && $realTime ? $departureReal ?? $arrivalReal ?? null : null,
            'arrival_platform_planned' => $platformPlanned,
            'departure_platform_planned' => $platformPlanned,
            'arrival_platform_real' => !$isCancelled && $realTime ? $platformReal : null,
            'departure_platform_real' => !$isCancelled && $realTime ? $platformReal : null,
            'cancelled' => $isCancelled,
            'station_identifier_id' => $identifier?->id ?? null,
        ];
    }

    private function getCategoryFromLeg(mixed $leg): MotisCategory
    {
        $motisCategory = MotisCategory::tryFrom($leg['mode']);
        if ($motisCategory === null) {
            Log::error('Unknown Motis category', [
                'mode' => $leg['mode'],
            ]);
            $motisCategory = MotisCategory::REGIONAL_RAIL;
        }

        return $motisCategory;
    }

    public function getTripData(mixed $leg, string $lineName, DataProvider $source): array
    {
        $originStation = $this->stationRepository->getStationsByIdentifiers($leg['from']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['from']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStationIdentifier($leg['from'], $source);
        $destinationStation = $this->stationRepository->getStationsByIdentifiers($leg['to']['stopId'], $source)->first()
                              ?? $this->stationRepository->updateOrCreateByIfopt($leg['to']['stopId'], $source)
                                 ?? $this->stationRepository->createMotisStationIdentifier($leg['to'], $source);
        $departure = isset($leg['from']['departure']) ? Carbon::parse($leg['from']['departure'])->utc() : null;
        $arrival = isset($leg['to']['arrival']) ? Carbon::parse($leg['to']['arrival'])->utc() : null;
        $mode = $this->getCategoryFromLeg($leg);
        $category = $mode->getHTT()->value;
        $tripLineName = !empty($leg['displayName']) ? $leg['displayName'] : $lineName;
        $license = $this->motisRepository->getActiveLicense($leg['source'], $source);
        $operator = $this->parseOperator($leg, $source);
        $shortTripName = !empty($leg['tripShortName']) ? $leg['tripShortName'] : null;
        $shortTripName = $shortTripName !== null ? preg_replace('/\D/', '', $shortTripName) : null;

        $payload = [
            'category' => $category,
            'mode' => $mode,
            'number' => $tripLineName,
            'linename' => $tripLineName,
            'route_color' => $leg['routeColor'] ?? null,
            'route_text_color' => $this->ensureReadableTextColor($leg['routeColor'] ?? null, $leg['routeTextColor'] ?? null),
            'operator_id' => $operator?->id,
            'origin_id' => $originStation->id,
            'destination_id' => $destinationStation->id,
            'departure' => $departure,
            'arrival' => $arrival,
            'source' => $source->value,
            'motis_source' => $source->value . '/' . $leg['source'],
            'motis_source_license_id' => $license?->id ?? null,
        ];

        if (is_numeric($shortTripName)) {
            $payload['journey_number'] = $shortTripName;
        }

        return $payload;
    }

    private function parseOperator(array $leg, DataProvider $source): ?Operator
    {
        return $this->operatorService->parseTransitousOperator(
            motisAgencyId: $leg['agencyId'] ?? null,
            motisAgencyName: $leg['agencyName'] ?? null,
            source: $source,
        );
    }

    private function checkLicenseData($rawDeparture, DataProvider $source, array &$removedEntries, int &$removedCount): bool
    {
        if (config('trwl.motis.filter_licenses')) {
            // Check if the source is licensed under an acceptable license
            $license = $this->motisRepository->getLicense($rawDeparture['source'], $source);
            if (empty($license) || !$license->active) {
                [$country, $name] = $this->motisRepository->getCountryAndLicense($rawDeparture['source']);
                $licenseIdentifier = $name;
                $licenseName = sprintf('%s: %s', strtoupper($country), $name);

                $license = $license ? $this->licenseService->getLicenseDataForSource($license) : null;
                $removedEntries[$licenseIdentifier] = $license ?? $licenseName;
                $removedCount++;

                return true;
            }
        }

        return false;
    }

    public function mapDepartures(mixed $entries, Station $station, DataProvider $source, ?string $queriedStopId = null): FilteredDepartures
    {
        $departures = collect();
        $removedEntries = [];
        $removedCount = 0;

        foreach ($entries as $rawDeparture) {
            if ($this->checkLicenseData($rawDeparture, $source, $removedEntries, $removedCount)) {
                continue; // skip this entry if it does not have a valid license
            }

            // trip
            $tripId = $rawDeparture['tripId'];
            $tripShortName = $rawDeparture['tripShortName'] ?? '';
            $rawDepartureStation = $rawDeparture['place'];
            $tripLineName = $rawDeparture['displayName'] ?? '';
            $mode = $this->getCategoryFromLeg($rawDeparture);
            $hafasTravelType = $mode->getHTT()->value;

            $platformPlanned = $rawDepartureStation['scheduledTrack'] ?? '';
            $platformReal = $rawDepartureStation['track'] ?? $platformPlanned;
            try {
                // If the stop in the response matches the queried stop, use the requested station directly.
                if ($queriedStopId !== null && $rawDepartureStation['stopId'] === $queriedStopId) {
                    $departureStation = $station;
                } else {
                    $departureStation = $this->stationRepository->getStationsByIdentifiers([$rawDepartureStation['stopId']], $source)->first();
                    if ($departureStation === null) {
                        $stationId = $rawDepartureStation['stopId'];
                        $departureStation = $this->stationRepository->updateOrCreateByIfopt($stationId, $source, $rawDepartureStation['lat'], $rawDepartureStation['lon']);
                        // if station does not exist, request it from API
                        $departureStation = $departureStation ?? $this->stationRepository->createMotisStationIdentifier($rawDepartureStation, $source);
                    }
                }
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                $departureStation = $station;
            }

            $isCancelled = (bool) ($rawDepartureStation['cancelled'] ?? false);

            $departure = new Departure(
                station: $departureStation,
                plannedDeparture: Carbon::parse($rawDepartureStation['scheduledDeparture'])->utc(),
                realDeparture: !$isCancelled && !empty($rawDeparture['realTime']) ? Carbon::parse($rawDepartureStation['departure'])->utc() : null,
                trip: new BahnTrip(
                    tripId: $tripId,
                    direction: $rawDeparture['headsign'],
                    lineName: $tripLineName,
                    number: $tripShortName,
                    category: $hafasTravelType,
                    journeyNumber: $tripShortName,
                    operator: $this->parseOperator($rawDeparture, $source),
                    routeColor: $rawDeparture['routeColor'] ?? null,
                    routeTextColor: $this->ensureReadableTextColor($rawDeparture['routeColor'] ?? null, $rawDeparture['routeTextColor'] ?? null),
                    mode: $mode
                ),
                plannedPlatform: $platformPlanned,
                realPlatform: $platformReal,
                cancelled: $isCancelled,
            );

            $departures->push($departure);
        }

        return new FilteredDepartures($departures, collect(array_values($removedEntries)), $removedCount);
    }

    private function ensureReadableTextColor(?string $bgHex, ?string $textHex): ?string
    {
        if ($bgHex === null) {
            return $textHex;
        }

        $bgLuminance = $this->relativeLuminance($bgHex);

        if ($textHex !== null) {
            $textLuminance = $this->relativeLuminance($textHex);
            $lighter = max($bgLuminance, $textLuminance);
            $darker = min($bgLuminance, $textLuminance);
            $contrast = ($lighter + 0.05) / ($darker + 0.05);

            if ($contrast >= 4.5) {
                return $textHex;
            }
        }

        // WCAG: pick black or white based on which has better contrast against the background
        $contrastWithBlack = ($bgLuminance + 0.05) / 0.05;
        $contrastWithWhite = 1.05 / ($bgLuminance + 0.05);

        return $contrastWithBlack >= $contrastWithWhite ? '000000' : 'ffffff';
    }

    private function relativeLuminance(string $hexColor): float
    {
        $hex = ltrim($hexColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $toLinear = static fn (float $c): float => $c <= 0.04045
            ? $c / 12.92
            : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $toLinear($r) + 0.7152 * $toLinear($g) + 0.0722 * $toLinear($b);
    }
}
