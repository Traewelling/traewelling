<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Controllers\API\v1\ExperimentalController;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Services\Wikidata\WikidataImportService;
use App\StationIdentifierType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StationRepository
{
    public function getStationByName(string $name, string $lang, bool $invertLanguage = false, int $limit = 20): Collection {
        $invertLanguage = $invertLanguage ? '!=' : '=';

        return Station::with(['areas', 'stationIdentifiers'])
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
                      ->orderByDesc('relevance')
                      ->orderBy('train_stations.name')
                      ->get();
    }

    public function getStationsByFuzzyRilIdentifier(string $rilIdentifier): Collection {
        $identifiers = StationIdentifier::with(['station.stationIdentifiers'])
                                        ->where('type', StationIdentifierType::DE_DB_RIL100)
                                        ->where('identifier', $rilIdentifier)
                                        ->get();

        return $identifiers->map(function(StationIdentifier $identifier) {
            return $identifier->station;
        });
    }

    public function getStationsByWikidataId(string $wikidataId): Collection {
        $stations = $this->getStationsByIdentifier($wikidataId, StationIdentifierType::WIKIDATA_ID);

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

    public function getStationsByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): Collection {
        $query = StationIdentifier::with(['station.stationIdentifiers'])
                                  ->whereIdentifier($identifier)
                                  ->whereType($type);
        if ($origin !== null) {
            $query->whereOrigin($origin);
        }

        return $query->get()
                     ->map(function(StationIdentifier $identifier) {
                         return $identifier->station;
                     });
    }

    public function getStationByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $origin = null): ?Station {
        $stations = $this->getStationsByIdentifier($identifier, $type, $origin);
        return $stations->isNotEmpty() ? $stations->first() : null;
    }

    public function getStationByIbnr(string $ibnr): ?Station {
        return $this->getStationByIdentifier($ibnr, StationIdentifierType::DE_DB_IBNR);
    }
}
