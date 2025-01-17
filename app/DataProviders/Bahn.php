<?php

namespace App\DataProviders;

use App\Dto\Coordinate;
use App\Dto\Internal\BahnTrip;
use App\Dto\Internal\Departure;
use App\Enum\HafasTravelType;
use App\Enum\ReiseloesungCategory;
use App\Enum\TravelType;
use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransportController;
use App\Hydrators\DepartureHydrator;
use App\Models\HafasOperator;
use App\Models\PolyLine;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Objects\LineSegment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use PDOException;

class Bahn extends Controller implements DataProviderInterface
{
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
            $url      = "https://www.bahn.de/web/api/reiseloesung/orte?suchbegriff=" . urlencode($query) . "&typ=ALL&limit=" . $results;
            $response = Http::get($url);

            if (!$response->ok()) {
                CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            }

            $json   = $response->json();
            $extIds = [];
            foreach ($json as $rawStation) {
                if (!isset($rawStation['extId'])) {
                    continue;
                }
                $extIds[] = $rawStation['extId'];
            }
            $stationCache = Station::whereIn('ibnr', $extIds)->get();

            $stations = collect();
            foreach ($json as $rawStation) {
                if (!isset($rawStation['extId'])) {
                    continue;
                }
                $station = $stationCache->where('ibnr', $rawStation['extId'])->first();
                if ($station === null) {
                    $station = Station::create([
                                                   'name'      => $rawStation['name'],
                                                   'latitude'  => $rawStation['lat'],
                                                   'longitude' => $rawStation['lon'],
                                                   'ibnr'      => $rawStation['extId'],
                                                   'source'    => 'bahn-web-api',
                                               ]);
                }
                $stations->push($station);
            }

            CacheKey::increment(HCK::LOCATIONS_SUCCESS);
            return $stations;
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        } catch (Exception $exception) {
            CacheKey::increment(HCK::LOCATIONS_FAILURE);
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @throws HafasException
     */
    public function getNearbyStations(float $latitude, float $longitude, int $results = 8): Collection {
        throw new HafasException("Method currently not supported");
    }

    private function getStationFromHalt(array $rawHalt) {
        //$station = Station::where('ibnr', $rawHalt['extId'])->first();
        //if($station !== null) {
        //    return $station;
        // }

        //urgh, there is no lat/lon - extract it from id
        // example id: A=1@O=Druseltal, Kassel@X=9414484@Y=51301106@U=81@L=714800@
        $matches = [];
        preg_match('/@X=(-?\d+)@Y=(-?\d+)/', $rawHalt['id'], $matches);
        $latitude  = $matches[2] / 1000000;
        $longitude = $matches[1] / 1000000;

        return Station::updateOrCreate([
                                           'ibnr' => $rawHalt['extId'],
                                       ], [
                                           'name'      => $rawHalt['name'],
                                           'latitude'  => $latitude ?? 0, // Hello Null-Island
                                           'longitude' => $longitude ?? 0, // Hello Null-Island
                                           'source'    => TripSource::BAHN_WEB_API->value,
                                       ]);
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
    ) {
        try {
            $timezone = "Europe/Berlin";

            $when = clone $when;

            $when->tz($timezone);

            $params = '?ortExtId=' . $station->ibnr . '&datum=' . $when->format('Y-m-d') . '&zeit=' . $when->format('H:i');

            $filterCategory = ReiseloesungCategory::fromTravelType($type);
            if (isset($filterCategory)) {
                foreach($filterCategory as $category) {
                    $params = $params . '&verkehrsmittel[]=' . $category->value;
                }
            }

            $requestUrl = "https://www.bahn.de/web/api/reiseloesung/abfahrten" . $params;
            $response = Http::get($requestUrl);

            if (!$response->ok()) {
                CacheKey::increment(HCK::DEPARTURES_NOT_OK);
                Log::error('Unknown HAFAS Error (fetchDepartures)', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new HafasException(__('messages.exception.generalHafas'));
            }

            $departures = collect();
            $entries    = $response->json('entries');
            CacheKey::increment(HCK::DEPARTURES_SUCCESS);
            foreach ($entries as $rawDeparture) {
                //trip
                $journeyId       = $rawDeparture['journeyId'];
                $departureStopId = $rawDeparture['bahnhofsId'];
                $tripLineName    = $rawDeparture['verkehrmittel']['mittelText'] ?? '';
                $category        = isset($rawDeparture['verkehrmittel']['produktGattung']) ? ReiseloesungCategory::tryFrom($rawDeparture['verkehrmittel']['produktGattung']) : ReiseloesungCategory::UNKNOWN;
                $category        = $category ?? ReiseloesungCategory::UNKNOWN;
                $hafasTravelType = $category->getHTT()->value;

                $platformPlanned = $rawDeparture['gleis'] ?? '';
                $platformReal    = $rawDeparture['ezGleis'] ?? $platformPlanned;
                try {
                    $departureStation = Station::where('ibnr', [$departureStopId])->first();
                    if ($departureStation === null) {
                        // if station does not exist, request it from API
                        $stationsFromApi  = $this->getStations($departureStopId, 1);
                        $departureStation = $stationsFromApi->first();
                    }
                } catch (Exception $exception) {
                    Log::error($exception->getMessage());
                    $departureStation = $station;
                }

                preg_match('/#ZE#(\d+)/', $journeyId, $matches);
                $journeyNumber = 0;
                if (count($matches) > 1) {
                    $journeyNumber = $matches[1];

                    if (empty($tripLineName)) {
                        $tripLineName = $matches[1];
                    }
                }

                // Cache data used for trip creation since another endpoints do not provide them
                Cache::add($journeyId, [
                    'category' => $hafasTravelType,
                    'lineName' => $tripLineName
                ],         now()->addMinutes(30));

                $departure = new Departure(
                    station:          $departureStation,
                    plannedDeparture: Carbon::parse($rawDeparture['zeit'], $timezone),
                    realDeparture:    isset($rawDeparture['ezZeit']) ? Carbon::parse($rawDeparture['ezZeit'], $timezone) : null,
                    trip:             new BahnTrip(
                                          tripId:        $journeyId,
                                          direction:     $rawDeparture['terminus'] ?? '',
                                          lineName:      $tripLineName,
                                          number:        $journeyNumber,
                                          category:      $hafasTravelType,
                                          journeyNumber: $journeyNumber,
                                      ),
                    plannedPlatform:  $platformPlanned,
                    realPlatform:     $platformReal,
                );

                $departures->push($departure);
            }

            return DepartureHydrator::map($departures);

        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        } catch (Exception $exception) {
            CacheKey::increment(HCK::DEPARTURES_FAILURE);
            throw new HafasException($exception->getMessage());
        }
    }


    /**
     * @throws HafasException
     */
    private function fetchJourney(string $journeyId, bool $poly = false): array|null {
        try {
            $response = Http::get("https://www.bahn.de/web/api/reiseloesung/fahrt", [
                'journeyId' => $journeyId,
                'poly'      => $poly ? 'true' : 'false',
            ]);

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
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        CacheKey::increment(HCK::TRIPS_NOT_OK);
        Log::error('Unknown HAFAS Error (fetchRawHafasTrip)', [
            'status' => $response->status(),
            'body'   => $response->body()
        ]);
        throw new HafasException(__('messages.exception.generalHafas'));
    }

    /**
     * @throws HafasException|JsonException
     */
    public function fetchRawHafasTrip(string $tripId, string $lineName) {
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
        $timezone = "Europe/Berlin";

        $rawJourney = $this->fetchJourney($tripID, true);
        if ($rawJourney === null) {
            // sorry
            throw new HafasException(__('messages.exception.generalHafas'));
        }
        // get cached data from departure board
        $cachedData          = Cache::get($tripID);
        $stopoverCacheFromDB = Station::whereIn('ibnr', collect($rawJourney['halte'])->pluck('extId'))->get();

        $originStation      = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][0]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][0]);
        $destinationStation = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][count($rawJourney['halte']) - 1]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][count($rawJourney['halte']) - 1]);
        $departure          = isset($rawJourney['halte'][0]['abfahrtsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][0]['abfahrtsZeitpunkt'], $timezone) : null;
        $arrival            = isset($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt'], $timezone) : null;

        foreach ($rawJourney['halte'] as $halt) {
            if (!empty($halt['kategorie'])) {
                $category = ReiseloesungCategory::tryFrom($halt['kategorie']);
                break;
            }
        }
        if (empty($category)) {
            // get cached category since Bahn API does not reveal that on the journey endpoint?!
            $category = $cachedData['category'] ?? HafasTravelType::REGIONAL->value;
        } else {
            $category = $category->getHTT()->value;
        }

        $tripLineName = $cachedData['lineName'] ?? '';

        // get trip number from first stop
        $tripNumber = isset($rawJourney['halte'][0]['nummer']) ? (int) $rawJourney['halte'][0]['nummer'] : 0;
        if ($tripNumber === 0) {
            preg_match('/#ZE#(\d+)/', $tripID, $matches);
            if (count($matches) > 1) {
                $tripNumber = $matches[1];
            }
        }

        $stopovers = collect();
        foreach ($rawJourney['halte'] as $rawHalt) {
            $station = $stopoverCacheFromDB->where('ibnr', $rawHalt['extId'])->first() ?? self::getStationFromHalt($rawHalt);

            $departurePlanned = isset($rawHalt['abfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['abfahrtsZeitpunkt'], $timezone) : null;
            $departureReal    = isset($rawHalt['ezAbfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['ezAbfahrtsZeitpunkt'], $timezone) : null;
            $arrivalPlanned   = isset($rawHalt['ankunftsZeitpunkt']) ? Carbon::parse($rawHalt['ankunftsZeitpunkt'], $timezone) : null;
            $arrivalReal      = isset($rawHalt['ezAnkunftsZeitpunkt']) ? Carbon::parse($rawHalt['ezAnkunftsZeitpunkt'], $timezone) : null;
            // new API does not differ between departure and arrival platform
            $platformPlanned = $rawHalt['gleis'] ?? null;
            $platformReal    = $rawHalt['ezGleis'] ?? $platformPlanned;

            $stopover = new Stopover([
                                         'train_station_id'           => $station->id,
                                         'arrival_planned'            => $arrivalPlanned ?? $departurePlanned,
                                         'arrival_real'               => $arrivalReal ?? $departureReal ?? null,
                                         'departure_planned'          => $departurePlanned ?? $arrivalPlanned,
                                         'departure_real'             => $departureReal ?? $arrivalReal ?? null,
                                         'arrival_platform_planned'   => $platformPlanned,
                                         'departure_platform_planned' => $platformPlanned,
                                         'arrival_platform_real'      => $platformReal,
                                         'departure_platform_real'    => $platformReal,
                                     ]);
            $stopovers->push($stopover);
        }

        $polyLine = isset($rawJourney['polylineGroup']) ? $this->getPolyLineFromTrip($rawJourney, $stopovers) : null;

        $journey = Trip::updateOrCreate([
                                            'trip_id' => $tripID,
                                        ], [
                                            'category'       => $category,
                                            'number'         => $tripNumber,
                                            'linename'       => $tripLineName,
                                            'journey_number' => $tripNumber,
                                            'operator_id'    => null, //TODO
                                            'origin_id'      => $originStation->id,
                                            'destination_id' => $destinationStation->id,
                                            'polyline_id'    => $polyLine?->id,
                                            'departure'      => $departure,
                                            'arrival'        => $arrival,
                                            'source'         => TripSource::BAHN_WEB_API,
                                        ]);
        $journey->stopovers()->saveMany($stopovers);

        return $journey;
    }

    private function getPolyLineFromTrip($journey, Collection $stopovers): PolyLine {
        $polyLine = $journey['polylineGroup'];
        $features = [];
        foreach($polyLine['polylineDescriptions'] as $description) {
            foreach ($description['coordinates'] as $coordinate) {
                $feature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $coordinate['lng'],
                            $coordinate['lat']
                        ]
                    ],
                    'properties' => new \stdclass()
                ];
                $features[] = $feature;
            }
        }
        $geoJson = ['type' => 'FeatureCollection', 'features' => $features];

        // TODO DUPLICATED FROM BROUTERCONTROLLER
        $highestMappedKey = null;
        foreach ($stopovers as $stopover) {
            $properties = [
                'id'   => $stopover->station->ibnr,
                'name' => $stopover->station->name,
            ];

            //Get feature with the lowest distance to station
            $minDistance       = null;
            $closestFeatureKey = null;
            foreach ($geoJson['features'] as $key => $feature) {
                if (($highestMappedKey !== null && $key <= $highestMappedKey) || !isset($feature['geometry']['coordinates'])) {
                    //Don't look again at the same stations.
                    //This is required and very important to prevent bugs for ring lines!
                    continue;
                }
                $distance = (new LineSegment(
                    new Coordinate($feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0]),
                    new Coordinate($stopover->station->latitude, $stopover->station->longitude)
                ))->calculateDistance();

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance       = $distance;
                    $closestFeatureKey = $key;
                }
            }
            $highestMappedKey                                      = $closestFeatureKey;
            $geoJson['features'][$closestFeatureKey]['properties'] = $properties;
        }

        // Make features to array again, if they get broken by the code above
        $geoJson['features'] = array_values($geoJson['features']);

        $geoJsonString = json_encode($geoJson);
        $polyline = PolyLine::create([
                                        'hash' =>       md5($geoJsonString),
                                        'polyline' =>   $geoJsonString,
                                        'source' =>     'hafas', // maybe add a new one?
                                        'parent_id' =>  null
                                     ]);
        return $polyline;
    }
}
