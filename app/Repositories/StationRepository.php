<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\User;
use App\Services\Wikidata\WikidataImportService;
use App\StationIdentifierType;
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

    public function getStationsByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): Collection
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
            ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
            ->where('train_checkins.user_id', $user->id)
            ->groupBy('train_stations.id')
            ->select(['train_stations.id', DB::raw('MAX(train_checkins.arrival) as last_arrival')])
            ->orderByDesc(DB::raw('MAX(train_checkins.arrival)'))
            ->limit($maxCount)
            ->get();

        return Station::with(['areas', 'stationIdentifiers'])
            ->whereIn('id', $latestStations->pluck('id'))
            ->get()
            ->sortBy(function (Station $station) use ($latestStations) {
                return $latestStations->firstWhere('id', $station->id)->last_arrival;
            }, SORT_REGULAR, true);
    }
}
