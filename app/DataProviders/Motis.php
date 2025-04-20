<?php

namespace App\DataProviders;

use App\DataProviders\Hydrators\MotisHydrator;
use App\DataProviders\Repositories\StationRepository;
use App\DataProviders\Repositories\TripRepository;
use App\Dto\Coordinate;
use App\Enum\DataProvider;
use App\Enum\MotisCategory;
use App\Enum\TravelType;
use App\Exceptions\HafasException;
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

    private GeoService        $geoService;
    private StationRepository $stationRepository;
    private TripRepository    $tripRepository;
    private DataProvider      $source;
    private MotisHydrator     $hydrator;

    private const string API_URL = 'https://api.transitous.org/api/v1';

    public function __construct(
        DataProvider       $source,
        ?GeoService        $geoService = null,
        ?TripRepository    $tripRepository = null,
        ?MotisHydrator     $hydrator = null,
        ?StationRepository $stationRepository = null
    ) {
        $this->source            = $source;
        $this->geoService        = $geoService ?? new GeoService();
        $this->tripRepository    = $tripRepository ?? new TripRepository();
        $this->hydrator          = $hydrator ?? new MotisHydrator();
        $this->stationRepository = $stationRepository ?? new StationRepository();
    }

    public function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }
        return null;
    }

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier): Collection {
        $stations = Station::where('rilIdentifier', 'LIKE', "$rilIdentifier%")
                           ->orderBy('rilIdentifier')
                           ->get();
        if ($stations->count() === 0) {
            $station = $this->getStationByRilIdentifier(rilIdentifier: $rilIdentifier);
            if ($station !== null) {
                $stations->push($station);
            }
        }
        return $stations;
    }

    /**
     * @throws HafasException
     */
    public function getStations(string $query, int $results = 10): Collection {
        try {
            $url      = sprintf(self::API_URL . "/geocode?text=%s&limit=%d&type=STOP", urlencode($query), $results);
            $response = Http::withUserAgent(VersionController::getUserAgent())->get($url);

            if (!$response->ok()) {
                CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            }

            $stations = $this->filterStopsFromResults($response);

            CacheKey::increment(HCK::LOCATIONS_SUCCESS);
            return $stations;
        } catch (Exception $exception) {
            CacheKey::increment(HCK::LOCATIONS_FAILURE);
            throw new HafasException($exception->getMessage()); //TODO: Throw a more specific exception instead of HAFAS
        }
    }


    /**
     * @throws HafasException
     */
    public function getNearbyStations(float $latitude, float $longitude, int $results = 8): Collection {
        $center = new Coordinate($latitude, $longitude);
        $bbox   = $this->geoService->getBoundingBox($center, config('trwl.motis.nearby_radius'));

        $response = Http::withUserAgent(VersionController::getUserAgent())->get(self::API_URL . '/map/stops', [
            'min' => (string) $bbox->lowerRight,
            'max' => (string) $bbox->upperLeft,
        ]);

        if (!$response->ok()) {
            CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            Log::error('Unknown HAFAS Error (fetchNearbyStations)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
        }

        $stations = $this->filterStopsFromResults($response, 'stopId');

        $stations = $stations->sortBy(function($station) use ($center) {
            return $this->geoService->getDistance($center, new Coordinate($station->latitude, $station->longitude));
        });

        CacheKey::increment(HCK::LOCATIONS_SUCCESS);
        return $stations;
    }

    /**
     * @param Station         $station
     * @param Carbon          $when
     * @param int             $duration
     * @param TravelType|null $type
     * @param bool            $localtime
     *
     * @return Collection
     * @throws HafasException
     */
    public function getDepartures(
        Station     $station,
        Carbon      $when,
        int         $duration = 15,
        ?TravelType $type = null,
        bool        $localtime = false
    ): Collection {
        $station->loadMissing('stationIdentifiers');

        // Increase station relevance for improved station search
        $station->increment('relevance');

        // Try to get a MOTIS-compatible identifier for the station from the current source
        $transitousIdentifiers = StationIdentifier::where('station_id', $station->id)
                                                  ->where('type', 'motis')
                                                  ->where('origin', $this->source->value)
                                                  ->orderBy('relevance', 'desc')
                                                  ->orderBy('updated_at', 'desc')
                                                  ->orderBy('created_at', 'desc')
                                                  ->get();

        // If no identifier found, try to find a nearby station instead
        if (empty($transitousIdentifiers)) {
            $station               = $this->getNearbyStations($station->latitude, $station->longitude)->first();
            $transitousIdentifiers = $station->stationIdentifiers->where('type', 'motis')->where('origin', $this->source->value)->get();
        }

        // Still no identifier found...? We can't continue here.
        if (empty($transitousIdentifiers)) {
            Log::debug('No MOTIS identifier found for station', ['station' => $station->only(['id', 'name'])]);
            throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
        }

        $count      = 0;
        $exceptions = 0;
        foreach ($transitousIdentifiers as $identifier) {
            $count++;
            try {
                $departures = $this->getDeparturesFromApi($station, $identifier, $when, $type);
            } catch (HafasException $exception) {
                // If we get an exception, we can try the next identifier
                Log::debug('MOTIS Error (getDepartures)', [
                    'status' => $exception->getMessage(),
                    'body'   => $exception->getMessage()
                ]);
                $exceptions++;
                $departures = collect();
            }

            if ($departures->count() === 0) {
                $identifier->decrement('relevance');
                $identifier->save();
                continue;
            }

            // If we have a result, we can stop searching
            if ($count > 1) {
                $identifier->increment('relevance');
                $identifier->save();
            }
            return $departures;
        }

        if ($exceptions > 0) {
            throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
        }

        // No departures found for any identifier
        Log::debug('No departures found for station', ['station' => $station->only(['id', 'name'])]);
        return collect();
    }

    private function getDeparturesFromApi(
        Station           $station,
        StationIdentifier $transitousIdentifier,
        Carbon            $when,
        ?TravelType       $type
    ): Collection {
        try {
            $params = [
                'stopId' => $transitousIdentifier->identifier,
                'radius' => config('trwl.motis.radius'),
                'time'   => $when->toIso8601String(),
                'n'      => config('trwl.motis.results'),
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
                            ->get(self::API_URL . '/stoptimes', $params);

            if (!$response->ok()) {
                CacheKey::increment(HCK::DEPARTURES_NOT_OK);
                Log::error('Unknown MOTIS Error (getDepartures)', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
            }

            $departures = collect();
            $entries    = $response->json('stopTimes');
            CacheKey::increment(HCK::DEPARTURES_SUCCESS);
            return $this->hydrator->mapDepartures($entries, $station, $departures, $this->source);
        } catch (JsonException $exception) {
            Log::debug('JSON Error (getDepartures)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            throw new HafasException($exception->getMessage()); //TODO: Throw a more specific exception instead of HAFAS
        } catch (Exception $exception) {
            CacheKey::increment(HCK::DEPARTURES_FAILURE);
            Log::debug('Unknown Error (getDepartures)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            throw new HafasException($exception->getMessage()); //TODO: Throw a more specific exception instead of HAFAS
        }
    }

    /**
     * @throws HafasException
     */
    private function fetchJourney(string $tripId): array|null {
        try {
            $response = Http::withUserAgent(VersionController::getUserAgent())->get(self::API_URL . "/trip", ['tripId' => $tripId,]);

            if ($response->ok()) {
                CacheKey::increment(HCK::TRIPS_SUCCESS);
                return $response->json();
            }

        } catch (Exception $exception) {
            CacheKey::increment(HCK::TRIPS_FAILURE);
            Log::error('Unknown HAFAS Error (fetchJourney)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            report($exception);
            throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
        }

        CacheKey::increment(HCK::TRIPS_NOT_OK);
        Log::error('Unknown HAFAS Error (fetchRawHafasTrip)', [
            'status' => $response->status(),
            'body'   => $response->body()
        ]);
        throw new HafasException(__('messages.exception.generalHafas')); //TODO: Throw a more specific exception instead of HAFAS
    }

    /**
     * @throws HafasException|JsonException
     */
    public function fetchRawHafasTrip(string $tripId, string $lineName): ?array {
        return $this->fetchJourney($tripId, true);
    }

    /**
     * @param string $tripID
     * @param string $lineName
     *
     * @return Trip
     * @throws HafasException|JsonException
     */
    public function fetchHafasTrip(string $tripID, string $lineName): Trip {
        $rawJourney = $this->fetchJourney($tripID);
        if ($rawJourney === null) {
            //TODO: Throw a more specific exception instead of HAFAS
            throw new HafasException(__('messages.exception.generalHafas'));
        }
        // get cached data from departure board
        $leg = $rawJourney['legs'][0];

        $journey = Trip::updateOrCreate(['trip_id' => $tripID], $this->hydrator->getTripData($leg, $lineName, $this->source));
        $this->tripRepository->tryToSaveStopovers($journey, $this->hydrator->parseLegToNewStopovers($leg, $this->source));

        return $journey;
    }

    private function filterStopsFromResults(Response $response, string $identifier = 'id'): Collection {
        $json        = $response->json();
        $rawStations = [];
        foreach ($json as $stationEntry) {
            if (isset($stationEntry['type']) && $stationEntry['type'] !== 'STOP') {
                continue;
            }
            $rawStations[] = $stationEntry;
        }
        $stationIds         = array_column($rawStations, $identifier);
        $stationCache       = $this->stationRepository->getStationsByIdentifiers($stationIds, $this->source);
        $stationIdentifiers = $stationCache->pluck('stationIdentifiers')->flatten();

        $stations = collect();
        foreach ($rawStations as $rawStation) {
            $stationIdentifier = $stationIdentifiers->where('identifier', $rawStation[$identifier])->first();
            $station           = $stationCache->where('id', $stationIdentifier?->station_id)->first();

            if ($station === null) {
                $rawStation['stopId'] = $rawStation[$identifier];
                $stationId            = $rawStation[$identifier];

                $station = $this->stationRepository->updateOrCreateByIfopt($stationId, $this->source);
                $station = $station ?? $this->stationRepository->createMotisStation($rawStation, $this->source);
            } else {
                if (!empty($rawStation['areas'])) {
                    $this->stationRepository->updateStationAreas($station, $rawStation['areas']);
                }
            }
            $stations->push($station);
        }
        return $stations;
    }
}
