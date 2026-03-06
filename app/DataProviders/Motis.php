<?php

namespace App\DataProviders;

use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Repositories\StationRepository;
use App\DataProviders\Repositories\TripRepository;
use App\Dto\Coordinate;
use App\Dto\Internal\Departure;
use App\Dto\Internal\FilteredDepartures;
use App\Enum\DataProvider;
use App\Enum\MotisCategory;
use App\Enum\StationIdentifierType;
use App\Enum\TravelType;
use App\Exceptions\DataProviderException;
use App\Exceptions\TimetableLocationNotFoundException;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Http\Controllers\Backend\VersionController;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Trip;
use App\Services\GeoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

class Motis extends Controller implements DataProviderInterface
{
    private GeoService $geoService;

    private StationRepository $stationRepository;

    private TripRepository $tripRepository;

    private DataProvider $source;

    private MotisHydrator $hydrator;

    private const string API_URL = 'https://api.transitous.org/api';

    public function __construct(
        DataProvider $source,
        ?GeoService $geoService = null,
        ?TripRepository $tripRepository = null,
        ?MotisHydrator $hydrator = null,
        ?StationRepository $stationRepository = null
    ) {
        $this->source = $source;
        $this->geoService = $geoService ?? new GeoService();
        $this->tripRepository = $tripRepository ?? new TripRepository();
        $this->hydrator = $hydrator ?? new MotisHydrator();
        $this->stationRepository = $stationRepository ?? new StationRepository();
    }

    public function getStationByRilIdentifier(string $rilIdentifier): ?Station
    {
        return $this->stationRepository->getStationByrilIdentifier($rilIdentifier);
    }

    /**
     * @throws DataProviderException
     */
    public function getStations(string $query, int $results = 10): Collection
    {
        try {
            $url = sprintf(self::API_URL . '/v1/geocode?text=%s&limit=%d&type=STOP', urlencode($query), $results);
            $response = Http::withUserAgent(VersionController::getUserAgent())->get($url);

            if (!$response->ok()) {
                CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            }

            $stations = $this->filterStopsFromResults($response);

            CacheKey::increment(HCK::LOCATIONS_SUCCESS);

            return $stations;
        } catch (Exception $exception) {
            CacheKey::increment(HCK::LOCATIONS_FAILURE);
            throw new DataProviderException($exception->getMessage()); // TODO: Throw a more specific exception instead of HAFAS
        }
    }

    /**
     * @throws DataProviderException
     */
    public function getNearbyStations(float $latitude, float $longitude, int $results = 8): Collection
    {
        $center = new Coordinate($latitude, $longitude);
        $bbox = $this->geoService->getBoundingBox($center, config('trwl.motis.nearby_radius'));

        $response = Http::withUserAgent(VersionController::getUserAgent())->get(self::API_URL . '/v1/map/stops', [
            'min' => (string) $bbox->lowerRight,
            'max' => (string) $bbox->upperLeft,
        ]);

        if (!$response->ok()) {
            CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            Log::error('Unknown MOTIS Error (fetchNearbyStations)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        $stations = $this->filterStopsFromResults($response, 'stopId');

        $stations = $stations->sortBy(function ($station) use ($center) {
            return $this->geoService->getDistance($center, new Coordinate($station->latitude, $station->longitude));
        });

        CacheKey::increment(HCK::LOCATIONS_SUCCESS);

        return $stations;
    }

    /**
     * @throws DataProviderException
     */
    public function getDepartures(Station $station, Carbon $when, int $duration = 15, ?TravelType $type = null, bool $localtime = false): array|Collection
    {
        return $this->getFilteredDepartures($station, $when, $duration, $type, $localtime)->departures;
    }

    /**
     * @throws DataProviderException
     */
    public function getFilteredDepartures(
        Station $station,
        Carbon $when,
        int $duration = 15,
        ?TravelType $type = null,
        bool $localtime = false
    ): FilteredDepartures {
        $station->loadMissing('stationIdentifiers');

        // Increase station relevance for improved station search
        $station->increment('relevance');

        // Try to get a MOTIS-compatible identifier for the station from the current source
        /** @var StationIdentifier[]|Collection $transitousIdentifiers */
        $transitousIdentifiers = StationIdentifier::where('station_id', $station->id)
            ->where('type', StationIdentifierType::MOTIS)
            ->where('origin', $this->source->value)
            ->where('relevance', '>', -9000)
            ->orderBy('relevance', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // If no identifier found, try to find a nearby station instead
        if (!$transitousIdentifiers || $transitousIdentifiers?->count() === 0) {
            $nearbyStation = $this->getNearbyStations($station->latitude, $station->longitude)->first();
            if ($nearbyStation !== null) {
                $station = $nearbyStation;
                $transitousIdentifiers = $station->stationIdentifiers->where('type', 'motis')->where('origin', $this->source->value);
            }
        }

        // Still no identifier found...? We can't continue here.
        if (!$transitousIdentifiers || $transitousIdentifiers->count() === 0) {
            Log::debug('No MOTIS identifier found for station', ['station' => $station->only(['id', 'name'])]);
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        $count = 0;
        $exceptions = 0;
        foreach ($transitousIdentifiers as $identifier) {
            $count++;
            try {
                $filtered = $this->getDeparturesFromApi($station, $identifier, $when, $type);
                $filtered = $this->dedupeDeparturesByStation($filtered, $station->id); // remove duplicates by tripId, keep the one that stops at the requested station (if any)
            } catch (DataProviderException $exception) {
                // If we get an exception, we can try the next identifier
                Log::error('MOTIS Error (getDepartures)', [
                    'status' => $exception->getMessage(),
                    'body' => $exception->getMessage(),
                    'identifier' => $identifier,
                    'when' => $when,
                    'type' => $type,
                    'station_id' => $station->id,
                ]);
                $exceptions++;
                $filtered = new FilteredDepartures(collect(), collect());
            } catch (TimetableLocationNotFoundException $exception) {
                $identifier->relevance = -9_000; // Set relevance OVER 9000 (to a very low value) to avoid future queries
                $identifier->save();

                continue;
            }

            if ($filtered->departures->count() === 0 && $filtered->removedEntries->count() === 0) {
                $identifier->decrement('relevance');
                $identifier->save();

                continue;
            }

            // If we have a result, we can stop searching
            if ($count > 1) {
                $identifier->increment('relevance');
                $identifier->save();
            }

            return $filtered;
        }

        if ($exceptions > 0) {
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        // No departures found for any identifier
        Log::debug('No departures found for station', ['station' => $station->only(['id', 'name'])]);

        return new FilteredDepartures(collect(), $filtered->removedEntries ?? collect());
    }

    /**
     * Remove duplicate trips on nearby stations.
     * If a trip has multiple departures at a station (different platforms/times) keep it.
     */
    private function dedupeDeparturesByStation(FilteredDepartures $filtered, int $requestedStationId): FilteredDepartures
    {
        $trackKey = static function (Departure $it): string {
            $scheduled = strtoupper(trim((string) ($it->plannedPlatform ?? '')));
            $live = strtoupper(trim((string) ($it->realPlatform ?? '')));

            return $scheduled . '|' . $live;
        };

        $timeKey = static function (Departure $it): string {
            return $it->plannedDeparture->toIso8601String();
        };

        $groups = $filtered->departures->groupBy(fn (Departure $it) => $it->trip->tripId);

        $kept = $groups->flatMap(function ($group) use ($requestedStationId, $trackKey, $timeKey) {
            $sameStation = $group->filter(function (Departure $it) use ($requestedStationId) {
                return $it->station->id === $requestedStationId;
            });

            if ($sameStation->isNotEmpty()) {
                // 1a) If there are several different tracks, keep exactly one (the first in time) for each track combination.
                $byTrack = $sameStation->groupBy($trackKey);
                $pickedPerTrack = $byTrack->map(function ($items) use ($timeKey) {
                    return $items->sortBy($timeKey)->first();
                })->values();

                // 1b) if there are no track infos at all, there should be just one...
                return $pickedPerTrack;
            }

            // 2) No departure on searched station -> use the first departure in time
            return collect([$group->sortBy($timeKey)->first()]);
        })
            ->sortBy($timeKey)
            ->values();

        return new FilteredDepartures($kept, $filtered->removedEntries);
    }

    public function fetchStationFromApi(
        string $identifier,
    ): Station {
        $params = [
            'stopId' => $identifier,
            'n' => 0,
        ];
        $response = Http::withUserAgent(VersionController::getUserAgent())
            ->get(self::API_URL . '/v5/stoptimes', $params);

        if (!$response->ok()) {
            CacheKey::increment(HCK::STATIONS_NOT_OK);
            Log::error('Unknown MOTIS Error (fetchStationFromApi)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        $station = $response->json('place');

        if (empty($station)) {
            Log::debug('No station found for identifier', ['identifier' => $identifier]);
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        return $this->stationRepository->createMotisStationIdentifier($station, $this->source);
    }

    private function getDeparturesFromApi(
        Station $station,
        StationIdentifier $transitousIdentifier,
        Carbon $when,
        ?TravelType $type
    ): FilteredDepartures {
        try {
            $params = [
                'stopId' => $transitousIdentifier->identifier,
                'radius' => config('trwl.motis.radius'),
                'time' => $when->toIso8601String(),
                'n' => config('trwl.motis.results'),
            ];

            // Apply travel type filter if set
            $filterCategory = MotisCategory::fromTravelType($type);
            if (isset($filterCategory)) {
                foreach ($filterCategory as $category) {
                    $params['mode'][] = $category->value;
                }
                $params['mode'] = implode(',', $params['mode']);
            }

            $response = Http::withUserAgent(VersionController::getUserAgent())
                ->get(self::API_URL . '/v5/stoptimes', $params);

            if (!$response->ok()) {
                CacheKey::increment(HCK::DEPARTURES_NOT_OK);
                Log::error('Unknown MOTIS Error (getDepartures)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if (str_contains($response->body(), 'could not find timetable location')) {
                    throw new TimetableLocationNotFoundException();
                }

                throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
            }

            $entries = $response->json('stopTimes');
            CacheKey::increment(HCK::DEPARTURES_SUCCESS);

            return $this->hydrator->mapDepartures($entries, $station, $this->source);
        } catch (JsonException $exception) {
            Log::debug('JSON Error (getDepartures)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new DataProviderException($exception->getMessage()); // TODO: Throw a more specific exception instead of HAFAS
        } catch (TimetableLocationNotFoundException $exception) {
            throw $exception; // This exception is handled separately
        } catch (Exception $exception) {
            CacheKey::increment(HCK::DEPARTURES_FAILURE);
            Log::debug('Unknown Error (getDepartures)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new DataProviderException($exception->getMessage()); // TODO: Throw a more specific exception instead of HAFAS
        }
    }

    /**
     * @throws DataProviderException
     */
    private function fetchJourney(string $tripId): ?array
    {
        try {
            $response = Http::withUserAgent(VersionController::getUserAgent())->get(self::API_URL . '/v5/trip', ['tripId' => $tripId]);

            if ($response->ok()) {
                CacheKey::increment(HCK::TRIPS_SUCCESS);

                return $response->json();
            }

        } catch (Exception $exception) {
            if (!empty($response) && $exception->getCode() === 404) {
                Log::debug('MOTIS Trip not found', ['tripId' => $tripId]);

                return null;
            }

            CacheKey::increment(HCK::TRIPS_FAILURE);
            Log::warning('Unknown MOTIS Error (fetchJourney)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            report($exception);
            throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
        }

        CacheKey::increment(HCK::TRIPS_NOT_OK);
        Log::error('Unknown MOTIS Error (fetchRawHafasTrip)', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        throw new DataProviderException(__('messages.exception.generalHafas')); // TODO: Throw a more specific exception instead of HAFAS
    }

    /**
     * @throws DataProviderException|JsonException
     */
    public function fetchRawHafasTrip(string $tripId, string $lineName): ?array
    {
        return $this->fetchJourney($tripId, true);
    }

    /**
     * @throws DataProviderException|JsonException
     */
    public function fetchHafasTrip(string $tripID, string $lineName): Trip
    {
        $rawJourney = $this->fetchJourney($tripID);
        if ($rawJourney === null) {
            // TODO: Throw a more specific exception instead of HAFAS
            throw new DataProviderException(__('messages.exception.generalHafas'));
        }
        // get cached data from departure board
        $leg = $rawJourney['legs'][0];

        $journey = Trip::updateOrCreate(['trip_id' => $tripID], $this->hydrator->getTripData($leg, $lineName, $this->source));
        $this->tripRepository->tryToSaveStopovers($journey, $this->hydrator->parseLegToNewStopovers($leg, $this->source));

        return $journey;
    }

    /**
     * @return Collection|Station[]
     */
    private function filterStopsFromResults(Response $response, string $identifier = 'id'): Collection
    {
        $json = $response->json();
        $rawStations = [];
        foreach ($json as $stationEntry) {
            if (isset($stationEntry['type']) && $stationEntry['type'] !== 'STOP') {
                continue;
            }
            $rawStations[] = $stationEntry;
        }
        $stationIds = array_column($rawStations, $identifier);
        $stationCache = $this->stationRepository->getStationsByIdentifiers($stationIds, $this->source);
        /** @var Collection|StationIdentifier[] $stationIdentifiers */
        $stationIdentifiers = $stationCache->pluck('stationIdentifiers')->flatten();

        $stations = collect();
        foreach ($rawStations as $rawStation) {
            /** @var StationIdentifier $stationIdentifier */
            $stationIdentifier = $stationIdentifiers->where('identifier', $rawStation[$identifier])->first();
            $station = $stationCache->where('id', $stationIdentifier?->station_id)->first();
            $areas = $rawStation['areas'] ?? [];

            if ($station === null) {
                $rawStation['stopId'] = $rawStation[$identifier];
                $stationId = $rawStation[$identifier];

                $station = $this->stationRepository->updateOrCreateByIfopt($stationId, $this->source);
                $station = $station ?? $this->stationRepository->createMotisStationIdentifier($rawStation, $this->source);
            } else {
                if (!empty($areas)) {
                    $this->stationRepository->updateStationAreas($station, $areas);
                }
                if ($stationIdentifier->relevance <= -9000) {
                    $this->stationRepository->resetRelevance($stationIdentifier);
                }
            }
            $stations->push($station);
        }

        return $stations;
    }
}
