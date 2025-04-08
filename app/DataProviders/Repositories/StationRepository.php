<?php

namespace App\DataProviders\Repositories;

use App\Models\Station;
use Illuminate\Support\Collection;
use PDOException;
use stdClass;

class StationRepository
{

    /**
     * @param stdClass $hafasStop
     *
     * @return Station
     * @throws PDOException
     */
    public static function parseHafasStopObject(stdClass $hafasStop): Station {

        $data = [
            'name'      => $hafasStop->name,
            'latitude'  => $hafasStop->location?->latitude,
            'longitude' => $hafasStop->location?->longitude,
        ];

        if (isset($hafasStop->ril100)) {
            $data['rilIdentifier'] = $hafasStop->ril100;
        }

        return Station::updateOrCreate(
            ['ibnr' => $hafasStop->id],
            $data
        );
    }

    public static function parseHafasStops(array $hafasResponse): Collection {
        $payload = [];
        foreach ($hafasResponse as $hafasStation) {
            $payload[] = [
                'ibnr'      => $hafasStation->id,
                'name'      => $hafasStation->name,
                'latitude'  => $hafasStation?->location?->latitude,
                'longitude' => $hafasStation?->location?->longitude,
            ];
        }
        return self::upsertStations($payload);
    }

    public static function upsertStations(array $payload) {
        $ibnrs = array_column($payload, 'ibnr');
        if (empty($ibnrs)) {
            return new Collection();
        }
        Station::upsert($payload, ['ibnr'], ['name', 'latitude', 'longitude']);
        return Station::whereIn('ibnr', $ibnrs)->get()
                      ->sortBy(function(Station $station) use ($ibnrs) {
                          return array_search($station->ibnr, $ibnrs);
                      })
                      ->values();
    }
}
