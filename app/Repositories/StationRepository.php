<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\StationUsageDto;
use App\Enum\StationIdentifierType;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class StationRepository
{
    public function getStationByName(string $name, int $limit = 20): Collection
    {
        return Station::with(['areas', 'stationIdentifiers'])
            ->where('name', 'LIKE', "$name")
            ->orWhere('name', 'LIKE', "%$name%")
            ->limit($limit)
            ->orderByDesc('relevance')
            ->orderBy('name')
            ->get();
    }

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier): Collection
    {
        $identifiers = StationIdentifier::with(['station.stationIdentifiers'])
            ->where('type', StationIdentifierType::DE_DB_RIL100)
            ->where('identifier', $rilIdentifier)
            ->get();

        return $identifiers->map(function (StationIdentifier $identifier) {
            return $identifier->station;
        });
    }

    public function getStationsByWikidataId(string $wikidataId): Collection
    {
        $stations = $this->getStationsByIdentifier($wikidataId, StationIdentifierType::WIKIDATA_ID);

        if ($stations->isEmpty() && self::checkGeneralRateLimit() && self::checkWikidataIdRateLimit($wikidataId)) {
            try {
                Log::debug('Lookup Wikidata ID as User searched it', ['wikidataId' => $wikidataId]);
                $station = WikidataImportService::importStation($wikidataId);
                Log::info('Saved Station from Wikidata.', [$station->only(['id', 'name', 'wikidata_id'])]);
                $stations->push($station);
            } catch (\InvalidArgumentException $exception) {
                // ignore in frontend, just log for debugging
                Log::debug('Could not import Station from Wikidata: ' . $exception->getMessage(), ['wikidataId' => $wikidataId]);
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        return $stations;
    }

    private function getStationsByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): Collection
    {
        $query = StationIdentifier::with(['station.stationIdentifiers'])
            ->whereIdentifier($identifier)
            ->whereType($type);
        if ($origin !== null) {
            $query->whereOrigin($origin);
        }

        return $query->get()
            ->map(function (StationIdentifier $identifier) {
                return $identifier->station;
            });
    }

    public function getStationByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): ?Station
    {
        $stations = $this->getStationsByIdentifier($identifier, $type, $origin);

        return $stations->isNotEmpty() ? $stations->first() : null;
    }

    public function getStationByIbnr(string $ibnr): ?Station
    {
        return $this->getStationByIdentifier($ibnr, StationIdentifierType::DE_DB_IBNR);
    }

    private static function checkGeneralRateLimit(): bool
    {
        $key = 'fetch-wikidata-user:' . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return false;
        }
        RateLimiter::increment($key);

        return true;
    }

    private static function checkWikidataIdRateLimit(string $qId): bool
    {
        $key = "fetch-wikidata-qid:$qId";
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }
        RateLimiter::increment($key, 5 * 60);

        return true;
    }

    /**
     * Get the latest Stations the user is arrived.
     */
    public function getLatestArrivalsForUser(User $user, int $maxCount = 5): Collection
    {
        $latestStations = DB::table('train_checkins')
            ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
            ->where('train_checkins.user_id', $user->id)
            ->orderByDesc('train_checkins.arrival')
            ->select(['train_stopovers.train_station_id', 'train_checkins.arrival'])
            ->limit($maxCount * 10)
            ->get()
            ->unique('train_station_id')
            ->take($maxCount);

        return Station::with(['areas', 'stationIdentifiers'])
            ->whereIn('id', $latestStations->pluck('train_station_id'))
            ->get()
            ->sortBy(function (Station $station) use ($latestStations) {
                return $latestStations->firstWhere('train_station_id', $station->id)->arrival;
            }, SORT_REGULAR, true);
    }

    public function getByIdentifier(string $identifier, StationIdentifierType $identifierType): ?Station
    {
        return StationIdentifier::whereIdentifier($identifier)
            ->whereType($identifierType)
            ->with('station')
            ->first()?->station;
    }

    public function getById(int $id): ?Station
    {
        return Station::whereId($id)->first();
    }

    public function getIdentifierForStation(string $identifierId, int $stationId): ?StationIdentifier
    {
        return StationIdentifier::where('id', $identifierId)
            ->where('station_id', $stationId)
            ->first();
    }

    public function moveStopoversToStation(Station $source, Station $target): int
    {
        $merged = 0;
        $potentialDuplicates = Stopover::where('train_station_id', $source->id)->get();
        foreach ($potentialDuplicates as $sourceStopover) {
            $duplicate = Stopover::where('train_station_id', $target->id)
                ->where('trip_id', $sourceStopover->trip_id)
                ->where('departure_planned', $sourceStopover->departure_planned)
                ->where('arrival_planned', $sourceStopover->arrival_planned)
                ->first();

            if ($duplicate) {
                Checkin::where('origin_stopover_id', $sourceStopover->id)->update(['origin_stopover_id' => $duplicate->id]);
                Checkin::where('destination_stopover_id', $sourceStopover->id)->update(['destination_stopover_id' => $duplicate->id]);
                $sourceStopover->delete();
                $merged++;
            }
        }

        return $merged + Stopover::where('train_station_id', $source->id)->update(['train_station_id' => $target->id]);
    }

    public function moveTripTerminalsToStation(Station $source, Station $target): int
    {
        return Trip::where('origin_id', $source->id)->update(['origin_id' => $target->id])
               + Trip::where('destination_id', $source->id)->update(['destination_id' => $target->id]);
    }

    public function moveEventsToStation(Station $source, Station $target): int
    {
        return Event::where('station_id', $source->id)->update(['station_id' => $target->id]);
    }

    public function moveEventSuggestionsToStation(Station $source, Station $target): int
    {
        return EventSuggestion::where('station_id', $source->id)->update(['station_id' => $target->id]);
    }

    public function moveHomeUsersToStation(Station $source, Station $target): int
    {
        return User::where('home_id', $source->id)->update(['home_id' => $target->id]);
    }

    /**
     * @param  bool  $onlyWithoutIdentifier  move only segment sides that are not bound to a station
     *                                       identifier; identifier-bound sides follow their identifier
     *                                       when that identifier is moved
     */
    public function moveRouteSegmentsToStation(Station $source, Station $target, bool $onlyWithoutIdentifier = false): int
    {
        return RouteSegment::where('from_station_id', $source->id)
            ->when($onlyWithoutIdentifier, fn ($query) => $query->whereNull('from_identifier_id'))
            ->update(['from_station_id' => $target->id])
               + RouteSegment::where('to_station_id', $source->id)
                   ->when($onlyWithoutIdentifier, fn ($query) => $query->whereNull('to_identifier_id'))
                   ->update(['to_station_id' => $target->id]);
    }

    public function getUsageCounts(Station $station): StationUsageDto
    {
        return new StationUsageDto(
            stopovers: Stopover::where('train_station_id', $station->id)->count(),
            trips: Trip::where('origin_id', $station->id)->orWhere('destination_id', $station->id)->count(),
            events: Event::where('station_id', $station->id)->count(),
            eventSuggestions: EventSuggestion::where('station_id', $station->id)->count(),
            identifiers: StationIdentifier::where('station_id', $station->id)->count(),
            routeSegments: RouteSegment::where('from_station_id', $station->id)->orWhere('to_station_id', $station->id)->count(),
            homeUsers: User::where('home_id', $station->id)->count(),
        );
    }

    public function moveIdentifierToStation(StationIdentifier $identifier, Station $targetStation): int
    {
        $identifier->update(['station_id' => $targetStation->id]);

        return RouteSegment::where('from_identifier_id', $identifier->id)
            ->update(['from_station_id' => $targetStation->id])
               + RouteSegment::where('to_identifier_id', $identifier->id)
                   ->update(['to_station_id' => $targetStation->id]);
    }

    /**
     * Moves all stopovers created via the given identifier to the target station.
     * Stopovers that would collide with an already existing stopover on the target station
     * (leftover duplicates from earlier moves) are skipped and can be cleaned up manually.
     *
     * @return array{moved: int, skipped: int, tripIds: array<int, string>}
     */
    public function moveStopoversOfIdentifier(StationIdentifier $identifier, Station $targetStation): array
    {
        $baseQuery = fn () => Stopover::where('station_identifier_id', $identifier->id)
            ->where('train_station_id', '!=', $targetStation->id);

        $conflictingIds = $baseQuery()
            ->whereExists(function ($query) use ($targetStation) {
                $query->select(DB::raw(1))
                    ->from('train_stopovers as duplicates')
                    ->whereColumn('duplicates.trip_id', 'train_stopovers.trip_id')
                    ->whereColumn('duplicates.arrival_planned', 'train_stopovers.arrival_planned')
                    ->whereColumn('duplicates.departure_planned', 'train_stopovers.departure_planned')
                    ->where('duplicates.train_station_id', $targetStation->id);
            })
            ->pluck('id');

        $tripIds = $baseQuery()->whereNotIn('id', $conflictingIds)->distinct()->pluck('trip_id');
        $moved = $baseQuery()->whereNotIn('id', $conflictingIds)->update(['train_station_id' => $targetStation->id]);

        return ['moved' => $moved, 'skipped' => $conflictingIds->count(), 'tripIds' => $tripIds->all()];
    }

    /**
     * Updates origin_id/destination_id of the given trips when their first/last stopover
     * was created via the given identifier and now points to a different station.
     *
     * @param  array<int, string>  $tripIds
     */
    public function updateTripTerminalStations(array $tripIds, StationIdentifier $identifier): int
    {
        $updated = 0;

        foreach (Trip::with('stopovers')->whereIn('trip_id', $tripIds)->cursor() as $trip) {
            $firstStopover = $trip->stopovers->first();
            $lastStopover = $trip->stopovers->last();

            if ($firstStopover?->station_identifier_id === $identifier->id) {
                $trip->origin_id = $firstStopover->train_station_id;
            }
            if ($lastStopover?->station_identifier_id === $identifier->id) {
                $trip->destination_id = $lastStopover->train_station_id;
            }

            if ($trip->isDirty()) {
                $trip->save();
                $updated++;
            }
        }

        return $updated;
    }

    public function createIdentifier(Station $station, StationIdentifierType $type, string $value): StationIdentifier
    {
        return StationIdentifier::create([
            'station_id' => $station->id,
            'type' => $type,
            'identifier' => $value,
            'origin' => null,
        ]);
    }

    public function updateIdentifierValues(StationIdentifier $identifier, StationIdentifierType $type, string $value): void
    {
        $identifier->update(['type' => $type, 'identifier' => $value]);
    }
}
