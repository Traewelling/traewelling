<?php

namespace App\DataProviders;

use App\Dto\Internal\Departure;
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
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
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

    /**
     * @throws HafasException
     * @throws JsonException
     */
    private function fetchDepartures(
        Station     $station,
        Carbon      $when,
        int         $duration = 15,
        ?TravelType $type = null,
        bool        $skipTimeShift = false
    ) {
        $timezone = "Europe/Berlin";
        $when     = clone $when;
        $when->tz($timezone);
        $response   = Http::get("https://www.bahn.de/web/api/reiseloesung/abfahrten", [
            'ortExtId' => $station->ibnr,
            'datum'    => $when->format('Y-m-d'),
            'zeit'     => $when->format('H:i'),
        ]);
        $departures = collect();
        foreach ($response->json('entries') as $rawDeparture) {
            $journey = Trip::where('trip_id', $rawDeparture['journeyId'])->first();
            if ($journey) {
                $departures->push(new Departure(
                                      station:          $station,
                                      plannedDeparture: Carbon::parse($rawDeparture['zeit'], $timezone),
                                      realDeparture:    isset($rawDeparture['ezZeit']) ? Carbon::parse($rawDeparture['ezZeit'], $timezone) : null,
                                      trip:             $journey,
                                  ));
                continue;
            }

            $rawJourney = $this->fetchJourney($rawDeparture['journeyId']);
            if ($rawJourney === null) {
                // sorry
                continue;
            }
            $stopoverCacheFromDB = Station::whereIn('ibnr', collect($rawJourney['halte'])->pluck('extId'))->get();

            $originStation      = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][0]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][0]);
            $destinationStation = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][count($rawJourney['halte']) - 1]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][count($rawJourney['halte']) - 1]);
            $departure          = isset($rawJourney['halte'][0]['abfahrtsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][0]['abfahrtsZeitpunkt'], $timezone) : null;
            $arrival            = isset($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt'], $timezone) : null;
            $category           = isset($rawDeparture['verkehrmittel']['produktGattung']) ? ReiseloesungCategory::tryFrom($rawDeparture['verkehrmittel']['produktGattung']) : ReiseloesungCategory::UNKNOWN;
            $category           = $category ?? ReiseloesungCategory::UNKNOWN;

            //trip
            $tripLineName      = $rawDeparture['verkehrmittel']['name'] ?? '';
            $tripNumber        = preg_replace('/\s/', '-', strtolower($tripLineName)) ?? '';
            $tripJourneyNumber = preg_replace('/\D/', '', $rawDeparture['verkehrmittel']['name']);

            $journey = Trip::create([
                                        'trip_id'        => $rawDeparture['journeyId'],
                                        'category'       => $category->getHTT(),
                                        'number'         => $tripNumber,
                                        'linename'       => $tripLineName,
                                        'journey_number' => !empty($tripJourneyNumber) ? $tripJourneyNumber : 0,
                                        'operator_id'    => null, //TODO
                                        'origin_id'      => $originStation->id,
                                        'destination_id' => $destinationStation->id,
                                        'polyline_id'    => null,
                                        'departure'      => $departure,
                                        'arrival'        => $arrival,
                                        'source'         => TripSource::BAHN_WEB_API,
                                    ]);


            $stopovers = collect();
            foreach ($rawJourney['halte'] as $rawHalt) {
                $station = $stopoverCacheFromDB->where('ibnr', $rawHalt['extId'])->first() ?? $this->getStationFromHalt($rawHalt);

                $departurePlanned = isset($rawHalt['abfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['abfahrtsZeitpunkt'], $timezone) : null;
                $departureReal    = isset($rawHalt['ezAbfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['ezAbfahrtsZeitpunkt'], $timezone) : null;
                $arrivalPlanned   = isset($rawHalt['ankunftsZeitpunkt']) ? Carbon::parse($rawHalt['ankunftsZeitpunkt'], $timezone) : null;
                $arrivalReal      = isset($rawHalt['ezAnkunftsZeitpunkt']) ? Carbon::parse($rawHalt['ezAnkunftsZeitpunkt'], $timezone) : null;

                $stopover = new Stopover([
                                             'train_station_id'  => $station->id,
                                             'arrival_planned'   => $arrivalPlanned ?? $departurePlanned,
                                             'arrival_real'      => $arrivalReal ?? $departureReal ?? null,
                                             'departure_planned' => $departurePlanned ?? $arrivalPlanned,
                                             'departure_real'    => $departureReal ?? $arrivalReal ?? null,
                                         ]);
                $stopovers->push($stopover);
            }
            $journey->stopovers()->saveMany($stopovers);

            $departures->push(new Departure(
                                  station:          $station,
                                  plannedDeparture: Carbon::parse($rawDeparture['zeit'], $timezone),
                                  realDeparture:    isset($rawDeparture['ezZeit']) ? Carbon::parse($rawDeparture['ezZeit'], $timezone) : null,
                                  trip:             $journey,
                              ));
        }
        return $departures;
    }


    private function getStationFromHalt(array $rawHalt) {
        //$station = Station::where('ibnr', $rawHalt['extId'])->first();
        //if($station !== null) {
        //    return $station;
        // }

        //urgh, there is no lat/lon - extract it from id
        // example id: A=1@O=Druseltal, Kassel@X=9414484@Y=51301106@U=81@L=714800@
        $matches = [];
        preg_match('/@X=(\d+)@Y=(\d+)/', $rawHalt['id'], $matches);
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
            $response = Http::get("https://www.bahn.de/web/api/reiseloesung/abfahrten", [
                'ortExtId' => $station->ibnr,
                'datum'    => $when->format('Y-m-d'),
                'zeit'     => $when->format('H:i'),
            ]);

            if (!$response->ok()) {
                CacheKey::increment(HCK::DEPARTURES_NOT_OK);
                throw new HafasException(__('messages.exception.generalHafas'));
            }

            $departures = collect();
            $entries    = $response->json('entries');
            CacheKey::increment(HCK::DEPARTURES_SUCCESS);
            foreach ($entries as $rawDeparture) {
                $journey = Trip::where('trip_id', $rawDeparture['journeyId'])->first();
                if ($journey) {
                    $departures->push(new Departure(
                                          station:          $station,
                                          plannedDeparture: Carbon::parse($rawDeparture['zeit'], $timezone),
                                          realDeparture:    isset($rawDeparture['ezZeit']) ? Carbon::parse($rawDeparture['ezZeit'], $timezone) : null,
                                          trip:             $journey,
                                      ));
                    continue;
                }

                $rawJourney = $this->fetchJourney($rawDeparture['journeyId']);
                if ($rawJourney === null) {
                    // sorry
                    continue;
                }
                $stopoverCacheFromDB = Station::whereIn('ibnr', collect($rawJourney['halte'])->pluck('extId'))->get();

                $originStation      = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][0]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][0]);
                $destinationStation = $stopoverCacheFromDB->where('ibnr', $rawJourney['halte'][count($rawJourney['halte']) - 1]['extId'])->first() ?? self::getStationFromHalt($rawJourney['halte'][count($rawJourney['halte']) - 1]);
                $departure          = isset($rawJourney['halte'][0]['abfahrtsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][0]['abfahrtsZeitpunkt'], $timezone) : null;
                $arrival            = isset($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt']) ? Carbon::parse($rawJourney['halte'][count($rawJourney['halte']) - 1]['ankunftsZeitpunkt'], $timezone) : null;
                $category           = isset($rawDeparture['verkehrmittel']['produktGattung']) ? ReiseloesungCategory::tryFrom($rawDeparture['verkehrmittel']['produktGattung']) : ReiseloesungCategory::UNKNOWN;
                $category           = $category ?? ReiseloesungCategory::UNKNOWN;

                //trip
                $tripLineName      = $rawDeparture['verkehrmittel']['name'] ?? '';
                $tripNumber        = preg_replace('/\s/', '-', strtolower($tripLineName)) ?? '';
                $tripJourneyNumber = preg_replace('/\D/', '', $rawDeparture['verkehrmittel']['name']);

                $journey = Trip::create([
                                            'trip_id'        => $rawDeparture['journeyId'],
                                            'category'       => $category->getHTT(),
                                            'number'         => $tripNumber,
                                            'linename'       => $tripLineName,
                                            'journey_number' => !empty($tripJourneyNumber) ? $tripJourneyNumber : 0,
                                            'operator_id'    => null, //TODO
                                            'origin_id'      => $originStation->id,
                                            'destination_id' => $destinationStation->id,
                                            'polyline_id'    => null,
                                            'departure'      => $departure,
                                            'arrival'        => $arrival,
                                            'source'         => TripSource::BAHN_WEB_API,
                                        ]);


                $stopovers = collect();
                foreach ($rawJourney['halte'] as $rawHalt) {
                    $station = $stopoverCacheFromDB->where('ibnr', $rawHalt['extId'])->first() ?? self::getStationFromHalt($rawHalt);

                    $departurePlanned = isset($rawHalt['abfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['abfahrtsZeitpunkt'], $timezone) : null;
                    $departureReal    = isset($rawHalt['ezAbfahrtsZeitpunkt']) ? Carbon::parse($rawHalt['ezAbfahrtsZeitpunkt'], $timezone) : null;
                    $arrivalPlanned   = isset($rawHalt['ankunftsZeitpunkt']) ? Carbon::parse($rawHalt['ankunftsZeitpunkt'], $timezone) : null;
                    $arrivalReal      = isset($rawHalt['ezAnkunftsZeitpunkt']) ? Carbon::parse($rawHalt['ezAnkunftsZeitpunkt'], $timezone) : null;

                    $stopover = new Stopover([
                                                 'train_station_id'  => $station->id,
                                                 'arrival_planned'   => $arrivalPlanned ?? $departurePlanned,
                                                 'arrival_real'      => $arrivalReal ?? $departureReal ?? null,
                                                 'departure_planned' => $departurePlanned ?? $arrivalPlanned,
                                                 'departure_real'    => $departureReal ?? $arrivalReal ?? null,
                                             ]);
                    $stopovers->push($stopover);
                }
                $journey->stopovers()->saveMany($stopovers);

                $departures->push(new Departure(
                                      station:          $station,
                                      plannedDeparture: Carbon::parse($rawDeparture['zeit'], $timezone),
                                      realDeparture:    isset($rawDeparture['ezZeit']) ? Carbon::parse($rawDeparture['ezZeit'], $timezone) : null,
                                      trip:             $journey,
                                  ));
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
        return $this->fetchJourney($tripId);
    }

    /**
     * @param string $tripID
     * @param string $lineName
     *
     * @return Trip
     * @throws HafasException|JsonException
     */
    public function fetchHafasTrip(string $tripID, string $lineName): Trip {
        $tripJson    = $this->fetchRawHafasTrip($tripID, $lineName);
        $origin      = Repositories\StationRepository::parseHafasStopObject($tripJson->origin);
        $destination = Repositories\StationRepository::parseHafasStopObject($tripJson->destination);
        $operator    = null;

        if (isset($tripJson->line->operator->id)) {
            $operator = HafasOperator::updateOrCreate([
                                                          'hafas_id' => $tripJson->line->operator->id,
                                                      ], [
                                                          'name' => $tripJson->line->operator->name,
                                                      ]);
        }

        if ($tripJson->line->name === null) {
            $tripJson->line->name = $tripJson->line->fahrtNr;
        }

        if ($tripJson->line->id === null) {
            $tripJson->line->id = '';
        }

        $polyline = TransportController::getPolylineHash(json_encode($tripJson->polyline));

        $trip = Trip::updateOrCreate([
                                         'trip_id' => $tripID
                                     ], [
                                         'category'       => $tripJson->line->product,
                                         'number'         => $tripJson->line->id,
                                         'linename'       => $tripJson->line->name,
                                         'journey_number' => $tripJson->line?->fahrtNr === "0" ? null : $tripJson->line?->fahrtNr,
                                         'operator_id'    => $operator?->id,
                                         'origin_id'      => $origin->id,
                                         'destination_id' => $destination->id,
                                         'polyline_id'    => $polyline->id,
                                         'departure'      => $tripJson->plannedDeparture,
                                         'arrival'        => $tripJson->plannedArrival,
                                         'delay'          => $tripJson->arrivalDelay ?? null,
                                         'source'         => TripSource::HAFAS,
                                     ]);

        //Save Stations
        $payload = [];
        foreach ($tripJson->stopovers as $stopover) {
            $payload[] = [
                'ibnr'      => $stopover->stop->id,
                'name'      => $stopover->stop->name,
                'latitude'  => $stopover->stop->location?->latitude,
                'longitude' => $stopover->stop->location?->longitude,
            ];
        }
        $stations = Repositories\StationRepository::upsertStations($payload);

        foreach ($tripJson->stopovers as $stopover) {
            //TODO: make this better 🤯

            //This array is a workaround because Hafas doesn't give
            //us delay-data if the train already passed this station
            //so... just save data we really got. :)
            $updatePayload = [
                'arrival_platform_planned'   => $stopover->plannedArrivalPlatform,
                'departure_platform_planned' => $stopover->plannedDeparturePlatform,
                'cancelled'                  => $stopover?->cancelled ?? false,
            ];
            //remove "null" values
            $updatePayload = array_filter($updatePayload, 'strlen'); //TODO: This is deprecated, find a better way

            //the arrival and departure attributes are always included, so to recognize whether we have realtime data,
            // arrivalDelay and departureDelay are checked for being null or not.
            if ($stopover->arrival !== null && isset($stopover->arrivalDelay)) {
                $updatePayload['arrival_real'] = Carbon::parse($stopover->arrival);
                if ($stopover->arrivalPlatform !== null) {
                    $updatePayload['arrival_platform_real'] = $stopover->arrivalPlatform;
                }
            }
            if ($stopover->departure !== null && isset($stopover->departureDelay)) {
                $updatePayload['departure_real'] = Carbon::parse($stopover->departure);
                if ($stopover->departurePlatform !== null) {
                    $updatePayload['departure_platform_real'] = $stopover->departurePlatform;
                }
            }
            try {
                //If there is no arrival set, we need to set the departure as arrival and vice versa
                // -> this is for checkins for trips were no entry/exit is planned.
                $plannedArrival   = Carbon::parse($stopover->plannedArrival);
                $plannedDeparture = Carbon::parse($stopover->plannedDeparture);

                Stopover::updateOrCreate(
                    [
                        'trip_id'           => $tripID,
                        'train_station_id'  => $stations->where('ibnr', $stopover->stop->id)->first()->id,
                        'arrival_planned'   => isset($stopover->plannedArrival) ? $plannedArrival : $plannedDeparture,
                        'departure_planned' => isset($stopover->plannedDeparture) ? $plannedDeparture : $plannedArrival,
                    ],
                    $updatePayload
                );
            } catch (PDOException) {
                //do nothing: updateOrCreate will handle duplicate keys, but if the database is a bit laggy
                // it can be thrown an error here. But that's not a big deal.
            }
        }
        return $trip;
    }
}
