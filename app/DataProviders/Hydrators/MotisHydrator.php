<?php

declare(strict_types=1);

namespace App\DataProviders\Hydrators;

use App\DataProviders\Repositories\MotisLicenseRepository;
use App\DataProviders\Repositories\StationRepository;
use App\Dto\Internal\BahnTrip;
use App\Dto\Internal\Departure;
use App\Enum\DataProvider;
use App\Enum\HafasTravelType;
use App\Enum\MotisCategory;
use App\Hydrators\DepartureHydrator;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
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

    public function __construct(
        ?MotisLicenseRepository $motisRepository = null,
        ?StationRepository      $stationRepository = null,
        ?OperatorService        $operatorService = null
    )
    {
        $this->motisRepository = $motisRepository ?? new MotisLicenseRepository();
        $this->stationRepository = $stationRepository ?? new StationRepository();
        $this->operatorService = $operatorService ?? new OperatorService();
    }

    public function parseLegToNewStopovers(mixed $leg, DataProvider $source): Collection
    {
        $rawStopovers = $leg['intermediateStops'];
        $stopoverCacheFromDB = $this->stationRepository->getStationsByIdentifiers(array_column($rawStopovers, 'stopId'), $source);

        // add origin and destination to stopovers
        $rawStopovers[] = $leg['from'];
        $rawStopovers[] = $leg['to'];

        $stopovers = collect();
        foreach ($rawStopovers as $rawStop) {
            $station = $stopoverCacheFromDB->where('stationIdentifiers', function ($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                    ->where('type', 'motis')
                    ->where('origin', $source->value);
            })->first();

            $stopover = new Stopover($this->getStopoverData($station, $rawStop, $source));
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

        $stopovers = collect();
        $key = ['trip_id', 'train_station_id', 'departure_planned', 'arrival_planned'];
        foreach ($rawStopovers as $rawStop) {
            $station = $stopoverCacheFromDB->where('stationIdentifiers', function ($query) use ($rawStop, $source) {
                $query->where('identifier', $rawStop['stopId'])
                    ->where('type', 'motis')
                    ->where('origin', $source->value);
            })->first();
            $stopoverData = $this->getStopoverData($station, $rawStop, $source);
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
                dump($exception->getMessage());
                Log::error('Failed to upsert stopover', [
                    'stopover' => $rawStop,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $stopovers;
    }

    public function getStopoverData($station, mixed $rawStop, DataProvider $source): array
    {
        $station = $station ?? $this->stationRepository->createMotisStation($rawStop, $source);

        $departurePlanned = isset($rawStop['scheduledDeparture']) ? Carbon::parse($rawStop['scheduledDeparture']) : null;
        $departureReal = isset($rawStop['departure']) ? Carbon::parse($rawStop['departure']) : null;
        $arrivalPlanned = isset($rawStop['scheduledArrival']) ? Carbon::parse($rawStop['scheduledArrival']) : null;
        $arrivalReal = isset($rawStop['arrival']) ? Carbon::parse($rawStop['arrival']) : null;
        // new API does not differ between departure and arrival platform
        $platformPlanned = $rawStop['scheduledTrack'] ?? null;
        $platformReal = $rawStop['track'] ?? $platformPlanned;

        return [
            'train_station_id' => $station->id,
            'arrival_planned' => $arrivalPlanned ?? $departurePlanned,
            'arrival_real' => $arrivalReal ?? $departureReal ?? null,
            'departure_planned' => $departurePlanned ?? $arrivalPlanned,
            'departure_real' => $departureReal ?? $arrivalReal ?? null,
            'arrival_platform_planned' => $platformPlanned,
            'departure_platform_planned' => $platformPlanned,
            'arrival_platform_real' => $platformReal,
            'departure_platform_real' => $platformReal,
        ];
    }

    public function getTripData(mixed $leg, string $lineName, DataProvider $source): array
    {
        $originStation = $this->stationRepository->getStationsByIdentifiers($leg['from']['stopId'], $source)->first() ?? $this->stationRepository->createMotisStation($leg['from'], $source);
        $destinationStation = $this->stationRepository->getStationsByIdentifiers($leg['to']['stopId'], $source)->first() ?? $this->stationRepository->createMotisStation($leg['to'], $source);
        $departure = isset($leg['from']['departure']) ? Carbon::parse($leg['from']['departure']) : null;
        $arrival = isset($leg['to']['arrival']) ? Carbon::parse($leg['to']['arrival']) : null;
        $category = MotisCategory::tryFrom($leg['mode'])?->getHTT()->value ?? HafasTravelType::REGIONAL;
        $tripLineName = !empty($leg['routeShortName']) ? $leg['routeShortName'] : $lineName;
        $license = $this->motisRepository->getLicense($leg['source'], $source);
        $operator = $this->parseOperator($leg);


        return [
            'category' => $category,
            'number' => $tripLineName,
            'linename' => $tripLineName,
            'journey_number' => null,
            'operator_id' => $operator?->id,
            'origin_id' => $originStation->id,
            'destination_id' => $destinationStation->id,
            'polyline_id' => null, //TODO
            'departure' => $departure,
            'arrival' => $arrival,
            'source' => $source->value,
            'motis_source' => $source->value . '/' . $leg['source'],
            'motis_source_license_id' => $license?->id ?? null,
        ];
    }

    public function parseOperator(array $leg): ?HafasOperator
    {
        return $this->operatorService->parseTransitousOperator(
            agencyId: $leg['agencyId'] ?? null,
            agencyName: $leg['agencyName'] ?? null,
        );
    }

    public function mapDepartures(mixed $entries, Station $station, Collection $departures, DataProvider $source): Collection
    {
        foreach ($entries as $rawDeparture) {
            if (config('trwl.motis.filter_licenses')) {
                // Check if the source is licensed under an acceptable license
                $license = $this->motisRepository->getLicense($rawDeparture['source'], $source);
                if (empty($license)) {
                    continue;
                }
            }

            //trip
            $tripId = $rawDeparture['tripId'];
            $rawDepartureStation = $rawDeparture['place'];
            $tripLineName = $rawDeparture['routeShortName'] ?? '';
            $category = MotisCategory::tryFrom($rawDeparture['mode']);
            $hafasTravelType = $category->getHTT()->value;

            $platformPlanned = $rawDepartureStation['scheduledTrack'] ?? '';
            $platformReal = $rawDepartureStation['track'] ?? $platformPlanned;
            try {
                $departureStation = $this->stationRepository->getStationsByIdentifiers([$rawDepartureStation['stopId']], $source)->first();
                if ($departureStation === null) {
                    // if station does not exist, request it from API
                    $departureStation = $this->stationRepository->createMotisStation($rawDepartureStation, $source);
                }
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                $departureStation = $station;
            }

            $departure = new Departure(
                station: $departureStation,
                plannedDeparture: Carbon::parse($rawDepartureStation['scheduledDeparture']),
                realDeparture: !empty($rawDeparture['realTime']) ? Carbon::parse($rawDepartureStation['departure']) : null,
                trip: new BahnTrip(
                    tripId: $tripId,
                    direction: $rawDeparture['headsign'],
                    lineName: $tripLineName,
                    number: $tripId,
                    category: $hafasTravelType,
                    journeyNumber: $tripId,
                    operator: $this->parseOperator($rawDeparture),
                ),
                plannedPlatform: $platformPlanned,
                realPlatform: $platformReal,
            );

            $departures->push($departure);
        }

        return DepartureHydrator::map($departures);
    }
}
