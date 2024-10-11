<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Station;
use Illuminate\Support\Collection;

class StationRepository
{
    public function getStationByName(string $name, string $lang, bool $invertLanguage = false): Collection {
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
