<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataProviders\Hafas;
use App\Http\Controllers\TransportController as TransportBackend;
use App\Models\Station;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StationRepository
{
    public function getStationByName(string $name, string $lang, bool $invertLanguage = false): Collection {
        $provider = new TransportBackend(Auth::user());
        return $provider->getTrainStationAutocomplete($name);

        /*
         * -------------------------------------
         * This function was experimental and doesn't work as expected.
         * It have no good user experience and should be improved before reusing.
         * -------------------------------------
         */

        $invertLanguage = $invertLanguage ? '!=' : '=';
        return Station::leftJoin('station_names', 'station_names.station_id', '=', 'train_stations.id')
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
                      ->limit(20)
                      ->get();
    }
}
