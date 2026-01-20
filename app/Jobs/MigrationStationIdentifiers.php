<?php

namespace App\Jobs;

use App\DataProviders\Repositories\StationRepository;
use App\Models\Station;
use App\StationIdentifierType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MigrationStationIdentifiers implements ShouldQueue
{
    use Queueable;

    private StationRepository $stationRepository;

    public function __construct(?StationRepository $stationRepository = null)
    {
        $this->stationRepository = $stationRepository ?? new StationRepository();
    }

    public function handle(): void
    {
        $stations = Station::where('identifiers_migrated', false)->limit(1000)->get();
        foreach ($stations as $station) {
            if ($station->ibnr !== null) {
                $this->stationRepository->updateStationIdentifier(
                    station: $station,
                    identifier: $station->ibnr,
                    type: StationIdentifierType::DE_DB_IBNR,
                );
            }

            if ($station->rilIdentifier !== null) {
                $this->stationRepository->updateStationIdentifier(
                    station: $station,
                    identifier: $station->rilIdentifier,
                    type: StationIdentifierType::DE_DB_RIL100
                );
            }

            if ($station->wikidata_id !== null) {
                $this->stationRepository->updateStationIdentifier(
                    station: $station,
                    identifier: $station->wikidata_id,
                    type: StationIdentifierType::WIKIDATA_ID
                );
            }

            $station->identifiers_migrated = true;
            $station->save();
        }
    }
}
