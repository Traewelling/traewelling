<?php

namespace App\DataProviders;

use App\DataProviders\Repositories\StationRepository;
use App\Enum\HafasTravelType as HTT;
use App\Enum\TravelType;
use App\Enum\TripSource;
use App\Exceptions\HafasException;
use App\Helpers\CacheKey;
use App\Helpers\HCK;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransportController;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use PDOException;

class Hafas extends Controller implements DataProviderInterface
{

    private function client(): PendingRequest {
        throw new Exception('Stop Hafas from Hafassing us.');

        return Http::baseUrl(config('trwl.db_rest'))
                   ->timeout(config('trwl.db_rest_timeout'));
    }

    public function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }

        try {
            $response = $this->client()->get("/stations/$rilIdentifier");
            if ($response->ok() && !empty($response->body()) && $response->body() !== '[]') {
                $data    = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
                $station = StationRepository::parseHafasStopObject($data);
                CacheKey::increment(HCK::STATIONS_SUCCESS);
            } else {
                CacheKey::increment(HCK::STATIONS_NOT_OK);
            }
        } catch (Exception $exception) {
            CacheKey::increment(HCK::STATIONS_FAILURE);
            report($exception);
        }
        return $station;
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
            $response = $this->client()->get(
                "/locations",
                [
                    'query'     => $query,
                    'fuzzy'     => 'true',
                    'stops'     => 'true',
                    'addresses' => 'false',
                    'poi'       => 'false',
                    'results'   => $results
                ]
            );

            $data = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            if (!$response->ok()) {
                CacheKey::increment(HCK::LOCATIONS_NOT_OK);
            }

            if (empty($data) || !$response->ok()) {
                return Collection::empty();
            }

            CacheKey::increment(HCK::LOCATIONS_SUCCESS);
            return Repositories\StationRepository::parseHafasStops($data);
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
        try {
            $response = $this->client()->get(
                "/stops/nearby",
                [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                    'results'   => $results
                ]
            );

            if (!$response->ok()) {
                CacheKey::increment(HCK::NEARBY_NOT_OK);
                throw new HafasException(__('messages.exception.generalHafas'));
            }

            $data     = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            $stations = Repositories\StationRepository::parseHafasStops($data);

            foreach ($data as $hafasStation) {
                $station           = $stations->where('ibnr', $hafasStation->id)->first();
                $station->distance = $hafasStation->distance;
            }

            CacheKey::increment(HCK::NEARBY_SUCCESS);
            return $stations;
        } catch (JsonException $exception) {
            CacheKey::increment(HCK::NEARBY_FAILURE);
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    private function fetchDepartures(
        Station    $station,
        Carbon     $when,
        int        $duration = 15,
        ?TravelType $type = null,
        bool       $skipTimeShift = false
    ) {
        $time  = $skipTimeShift ? $when : (clone $when)->shiftTimezone("Europe/Berlin");
        $query = [
            'when'                       => $time->toIso8601String(),
            'duration'                   => $duration,
            HTT::NATIONAL_EXPRESS->value => FptfHelper::checkTravelType($type, TravelType::EXPRESS),
            HTT::NATIONAL->value         => FptfHelper::checkTravelType($type, TravelType::EXPRESS),
            HTT::REGIONAL_EXP->value     => FptfHelper::checkTravelType($type, TravelType::EXPRESS),
            HTT::REGIONAL->value         => FptfHelper::checkTravelType($type, TravelType::REGIONAL),
            HTT::SUBURBAN->value         => FptfHelper::checkTravelType($type, TravelType::SUBURBAN),
            HTT::BUS->value              => FptfHelper::checkTravelType($type, TravelType::BUS),
            HTT::FERRY->value            => FptfHelper::checkTravelType($type, TravelType::FERRY),
            HTT::SUBWAY->value           => FptfHelper::checkTravelType($type, TravelType::SUBWAY),
            HTT::TRAM->value             => FptfHelper::checkTravelType($type, TravelType::TRAM),
            HTT::TAXI->value             => FptfHelper::checkTravelType($type, TravelType::TAXI),
        ];
        try {
            $response = $this->client()->get('/stops/' . $station->ibnr . '/departures', $query);
        } catch (Exception $exception) {
            CacheKey::increment(HCK::DEPARTURES_FAILURE);
            throw new HafasException($exception->getMessage());
        }

        if (!$response->ok()) {
            CacheKey::increment(HCK::DEPARTURES_NOT_OK);
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        CacheKey::increment(HCK::DEPARTURES_SUCCESS);
        return json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
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
        Station    $station,
        Carbon     $when,
        int        $duration = 15,
        ?TravelType $type = null,
        bool       $localtime = false
    ): Collection {
        try {
            $requestTime = is_null($station->time_offset) || $localtime
                ? $when : (clone $when)->subHours($station->time_offset);
            $data        = $this->fetchDepartures(
                $station,
                $requestTime,
                $duration,
                $type,
                !$station->shift_time && !$localtime
            );
            if (!$localtime) {
                foreach ($data as $departure) {
                    if ($departure?->when) {
                        $time     = Carbon::parse($departure->when);
                        $timezone = $time->tz->toOffsetName();

                        // check for an offset between results
                        $offset = $time->tz('UTC')->hour - $when->tz('UTC')->hour;
                        if ($offset !== 0) {
                            // Check if the timezone for this station is equal in its offset to Europe/Berlin.
                            // If so, fetch again **without** adjusting the timezone
                            if ($timezone === CarbonTimeZone::create("Europe/Berlin")->toOffsetName()) {
                                $data = $this->fetchDepartures($station, $when, $duration, $type, true);

                                $station->shift_time = false;
                                $station->save();
                                break;
                            }
                            // if the timezone is not equal to Europe/Berlin, fetch the offset
                            $data = $this->fetchDepartures($station, (clone $when)->subHours($offset), $duration, $type);

                            $station->time_offset = $offset;
                            $station->save();
                        }
                        break;
                    }
                }
            }

            //First fetch all stations in one request
            $stationPayload = [];
            foreach ($data as $departure) {
                if (in_array($departure->stop->id, array_column($stationPayload, 'ibnr'), true)) {
                    continue;
                }
                $stationPayload[] = [
                    'ibnr'      => $departure->stop->id,
                    'name'      => $departure->stop->name,
                    'latitude'  => $departure->stop?->location?->latitude,
                    'longitude' => $departure->stop?->location?->longitude,
                ];
            }
            $stations = Repositories\StationRepository::upsertStations($stationPayload);

            //Then match the stations to the departures
            $departures = collect();
            foreach ($data as $departure) {
                $departure->station = $stations->where('ibnr', $departure->stop->id)->first();
                $departures->push($departure);
            }

            return $departures;
        } catch (JsonException $exception) {
            throw new HafasException($exception->getMessage());
        }
    }

    /**
     * @throws HafasException|JsonException
     */
    public function fetchRawHafasTrip(string $tripId, string $lineName) {
        try {
            $tripResponse = $this->client()->get("trips/" . rawurlencode($tripId), [
                'lineName'  => $lineName,
                'polyline'  => 'true',
                'stopovers' => 'true'
            ]);
        } catch (Exception $exception) {
            CacheKey::increment(HCK::TRIPS_FAILURE);
            throw new HafasException(__('messages.exception.generalHafas'));
        }

        if ($tripResponse->ok()) {
            CacheKey::increment(HCK::TRIPS_SUCCESS);
            return json_decode($tripResponse->body(), false, 512, JSON_THROW_ON_ERROR);
        }
        //sometimes HAFAS returnes 502 Bad Gateway
        if ($tripResponse->status() === 502) {
            CacheKey::increment(HCK::TRIPS_502);
            Log::error('Cannot fetch trip with id: ' . $tripId);
            throw new HafasException(__('messages.exception.hafas.502'));
        }
        CacheKey::increment(HCK::TRIPS_NOT_OK);
        Log::error('Unknown HAFAS Error (fetchRawHafasTrip)', [
            'status' => $tripResponse->status(),
            'body'   => $tripResponse->body()
        ]);
        throw new HafasException(__('messages.exception.generalHafas'));
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
