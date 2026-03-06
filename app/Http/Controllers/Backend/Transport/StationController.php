<?php

namespace App\Http\Controllers\Backend\Transport;

use App\DataProviders\DataProviderBuilder;
use App\DataProviders\DataProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\Stopover;
use App\Repositories\StationRepository;
use Illuminate\Support\Collection;

class StationController extends Controller
{
    private DataProviderInterface $dataProvider;

    private StationRepository $stationRepository;

    public function __construct(?StationRepository $stationRepository = null)
    {
        $this->dataProvider = (new DataProviderBuilder())->build();
        $this->stationRepository = $stationRepository ?? new StationRepository();
    }

    public function search(string $search): Collection
    {
        if (!is_numeric($search) && strlen($search) <= 5 && ctype_upper($search)) {
            $stations = $this->stationRepository->getStationsByFuzzyRilIdentifier($search);
            if ($stations->isNotEmpty()) {
                return $stations;
            }
        }

        if (preg_match('/^Q\d+$/', $search)) {
            return $this->stationRepository->getStationsByWikidataId($search);
        }

        $stations = $this->dataProvider->getStations($search);
        if ($stations->count() < 10) {
            $remaining = 10 - $stations->count();
            $dbStations = $this->stationRepository->getStationByName($search);
            // remove duplicates
            $dbStations = $dbStations->filter(function (Station $station) use ($stations) {
                return !$stations->contains('id', $station->id);
            });
            $stations = $stations->merge($dbStations->take($remaining));
        }

        return $stations;
    }
}
