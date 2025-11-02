<?php

namespace App\Http\Controllers\Backend\Transport;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Http\Controllers\Backend\Transport\dtos\StationDto;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\User;
use App\Repositories\StationRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StationController extends Controller
{
    private DataProviderInterface $dataProvider;
    private StationRepository     $stationRepository;

    public function __construct(?StationRepository $stationRepository = null) {
        $this->dataProvider      = (new DataProviderBuilder())->build();
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

        return DB::table('train_checkins')
                 ->join('train_stopovers', 'train_checkins.destination_stopover_id', '=', 'train_stopovers.id')
                 ->join('train_stations', 'train_stopovers.train_station_id', '=', 'train_stations.id')
                 ->where('train_checkins.user_id', $user->id)
                 ->groupBy($groupAndSelect)
                 ->select($groupAndSelect)
                 ->orderByDesc(DB::raw('MAX(train_checkins.arrival)'))
                 ->limit($maxCount)
                 ->get()
                 ->map(function(object $station) {
                     $areas = Area::query()
                                  ->join('areas_stations_maps', 'areas_stations_maps.area_id', '=', 'areas.id')
                                  ->where('areas_stations_maps.station_id', $station->id)
                                  ->get(['areas.id', 'areas.name', 'areas.adminLevel', 'areas_stations_maps.default']);

                     return new StationDto(
                         (int) $station->id,
                         $station->ibnr,
                         $station->name,
                         (float) $station->latitude,
                         (float) $station->longitude,
                         $station->rilIdentifier ?? null,
                         $areas
                     );
                 });
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

    public function search(string $search): Collection {
        if (!is_numeric($search) && strlen($search) <= 5 && ctype_upper($search)) {
            $stations = $this->stationRepository->getStationsByFuzzyRilIdentifier($search);
            if ($stations->isNotEmpty()) {
                return $stations;
            }
            $stations = $this->stationRepository->getStationsByFuzzyRilIdentifierDeprecated($search);
            if ($stations->isNotEmpty()) {
                return $stations;
            }
        } elseif (preg_match('/^Q\d+$/', $search)) {
            return $this->stationRepository->getStationsByWikidataId($search);
        }

        $stations = $this->dataProvider->getStations($search);
        if ($stations->count() < 10) {
            $remaining  = 10 - $stations->count();
            $dbStations = $this->stationRepository->getStationByName($search, 'de', true);
            // remove duplicates
            $dbStations = $dbStations->filter(function(Station $station) use ($stations) {
                return !$stations->contains('id', $station->id);
            });
            $stations   = $stations->merge($dbStations->take($remaining));
        }
        return $stations;
    }
}
