<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Controllers\API\v1\ExperimentalController;
use App\Models\Station;
use App\Services\Wikidata\WikidataImportService;
use App\StationIdentifierType;
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

    private function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }
        return null;
    }

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

    public function getStationByIdentifier(string $identifier, StationIdentifierType $type = StationIdentifierType::MOTIS, ?string $provider = null): ?Station {
        $query = StationIdentifier::with('station')
                                  ->whereIdentifier($identifier)
                                  ->whereType($type);
        if ($provider !== null) {
            $query->whereProvider($provider);
        }

        return $query->first()?->station;
    }

    public function getStationByIbnr(string $ibnr): ?Station {
        return Station::where('ibnr', $ibnr)->first();
    }
}
