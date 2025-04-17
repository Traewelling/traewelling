<?php

namespace App\Http\Controllers\Backend\Transport;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Http\Controllers\API\v1\ExperimentalController;
use App\Http\Controllers\Controller;
use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\User;
use App\Repositories\StationRepository;
use App\Services\Wikidata\WikidataImportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StationController extends Controller
{
    private DataProviderInterface $dataProvider;
    private StationRepository     $stationRepository;

    public function __construct(?StationRepository $stationRepository = null, ?User $user = null) {
        $this->dataProvider      = (new DataProviderBuilder())->build(null, $user);
        $this->stationRepository = $stationRepository ?? new StationRepository();
    }

    /**
     * Get the latest Stations the user is arrived.
     *
     * @param User $user
     * @param int  $maxCount
     *
     * @return Collection
     */
    public static function getLatestArrivals(User $user, int $maxCount = 5): Collection {
        $groupAndSelect = [
            'train_stations.id', 'train_stations.ibnr', 'train_stations.name',
            'train_stations.latitude', 'train_stations.longitude', 'train_stations.rilIdentifier',
        ];
        return DB::table('train_checkins') //TODO: return Station objects
                 ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                 ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                 ->where('train_checkins.user_id', $user->id)
                 ->groupBy($groupAndSelect)
                 ->select($groupAndSelect)
                 ->orderByDesc(DB::raw('MAX(train_checkins.arrival)'))
                 ->limit($maxCount)
                 ->get();
    }

    /**
     * @deprecated
     */
    public static function getAlternativeDestinationsForCheckin(Checkin $checkin): Collection {
        $encounteredOrigin = false;
        return $checkin->trip->stopovers
            ->filter(function(Stopover $stopover) use ($checkin, &$encounteredOrigin): bool {
                if (!$encounteredOrigin) { // this assumes stopovers being ordered correctly
                    $encounteredOrigin = $stopover->departure_planned == $checkin->departure && $stopover->is($checkin->originStopover);
                    return false;
                }
                return true;
            })
            ->map(function(Stopover $stopover) {
                return [
                    'id'              => $stopover->id,
                    'name'            => $stopover->station->name,
                    'arrival_planned' => userTime($stopover->arrival_planned ?? $stopover->departure_planned),
                ];
            });
    }

    /**
     * @param string $search
     * @param string $lang
     *
     * @return Collection
     */
    public function search(string $search, string $lang): Collection {
        if (!is_numeric($search) && strlen($search) <= 5 && ctype_upper($search)) {
            $stations = $this->getStationsByFuzzyRilIdentifier($search);
            if ($stations->isNotEmpty()) {
                return $stations;
            }
        } elseif (preg_match('/^Q\d+$/', $search)) {
            return $this->getStationsByWikidataId($search);
        }

        $stations = $this->dataProvider->getStations($search);
        if ($stations->count() < 10) {
            $remaining  = 10 - $stations->count();
            $dbStations = $this->stationRepository->getStationByName($search, 'de', true, $remaining);
            $stations   = $stations->merge($dbStations);
        }
        return $stations;
/*
        $stations = $this->stationRepository->getStationByName($search, $lang);

        if (count($stations) < 2) {
            $stations->merge($this->stationRepository->getStationByName($search, $lang, true))->unique();
        }

        $stations->map(function($station) use ($search) {
            similar_text($station->name, $search, $percent);
            $station->similarity = $percent;

            return $station;
        });

        return $stations->sortByDesc('similarity');
*/
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

    public function getStationByRilIdentifier(string $rilIdentifier): ?Station {
        $station = Station::where('rilIdentifier', $rilIdentifier)->first();
        if ($station !== null) {
            return $station;
        }
        return null;
    }
}
