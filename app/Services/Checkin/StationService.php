<?php

declare(strict_types=1);

namespace App\Services\Checkin;

use App\DataProviders\DataProviderInterface;
use App\Models\Station;
use App\Models\User;
use App\Repositories\StationRepository;
use Illuminate\Support\Collection;

class StationService
{
    public function __construct(
        private StationRepository $stationRepository,
        private DataProviderInterface $dataProvider
    ) {}

    public function getLatestArrivalsForUser(User $user, int $limit): Collection
    {
        return $this->stationRepository->getLatestArrivalsForUser(user: $user, maxCount: $limit);
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
