<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Controllers\API\v1\ExperimentalController;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\Wikidata\WikidataImportService;
use App\StationIdentifierType;
use Deprecated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StationRepository
{
    public function getStationByName(string $name, string $lang, bool $invertLanguage = false, int $limit = 20): Collection {
        $invertLanguage = $invertLanguage ? '!=' : '=';

        return Station::with('areas')
                      ->leftJoin('station_names', 'station_names.station_id', '=', 'train_stations.id')
                      ->where('station_names.name', 'LIKE', "$name")
                      ->where('station_names.language', $invertLanguage, $lang)
                      ->orWhere('train_stations.name', 'LIKE', "$name")
                      ->orWhere(function($query) use ($name, $invertLanguage, $lang) {
                          $query->where('station_names.name', 'LIKE', "%$name%")
                                ->where('station_names.language', $invertLanguage, $lang)
                                ->orWhere('train_stations.name', 'LIKE', "%$name%");
                      })
                      ->select('train_stations.*')
                      ->distinct()
                      ->limit($limit)
                      ->orderBy('relevance', 'desc')
                      ->orderBy('train_stations.name', 'asc')
                      ->get();
    }

    #[Deprecated]
    public function getStationsByFuzzyRilIdentifierDeprecated(string $rilIdentifier): Collection {
        $stations = Station::where('rilIdentifier', 'LIKE', "$rilIdentifier%")
                           ->orderBy('rilIdentifier')
                           ->get();
        if ($stations->count() === 0) {
            $station = $this->getStationByRilIdentifierDeprecated(rilIdentifier: $rilIdentifier);
            if ($station !== null) {
                $stations->push($station);
            }
        }
        return $stations;
    }

    private function getStationByRilIdentifierDeprecated(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }
        return null;
    }

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier): Collection {
        $identifiers = StationIdentifier::with('station')
                                        ->where('type', StationIdentifierType::DE_DB_RIL100)
                                        ->where('identifier', $rilIdentifier)
                                        ->get();
        if ($identifiers->count() === 0) {
            $station = $this->getStationByRilIdentifierDeprecated(rilIdentifier: $rilIdentifier);
            return collect($station);
        }

        return $identifiers->map(function($identifier) {
            /** @var StationIdentifier $identifier */
            return $identifier->station;
        });
    }

    private function getStationByRilIdentifier(string $rilIdentifier): Station {
        $station = StationIdentifier::with('station')
                                    ->where('type', StationIdentifierType::DE_DB_RIL100)
                                    ->where('identifier', $rilIdentifier)
                                    ->first()->station;
    }

    /**
     * @deprecated Needs to be replaced with StationIdentifier-Search
     */
    public function getStationsByWikidataId(string $wikidataId): Collection {
        $stations = Station::where('wikidata_id', $wikidataId)->get();

        if ($stations->isEmpty() && ExperimentalController::checkGeneralRateLimit() && ExperimentalController::checkWikidataIdRateLimit($wikidataId)) {
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

    public function getStationByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): ?Station {
        $query = StationIdentifier::with('station')
                                  ->whereIdentifier($identifier)
                                  ->whereType($type);
        if ($origin !== null) {
            $query->whereOrigin($origin);
        }

        return $query->first()?->station;
    }

    public function getStationByIbnr(string $ibnr): ?Station {
        return Station::where('ibnr', $ibnr)->first();
    }
}
