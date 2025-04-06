<?php

namespace App\Repositories;

use App\DataProviders\DataProviderBuilder;
use App\Exceptions\HafasException;
use App\Models\Event;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;
use JsonException;

class CheckinHydratorRepository
{
    public function findOrFailStopover(int $id): Stopover {
        return Stopover::findOrFail($id);
    }

    public function getOneStation(string $searchKey, string|int $id): ?Station {
        return Station::where($searchKey, $id)->first();
    }

    /**
     * @throws HafasException
     * @throws JsonException
     */
    public function getHafasTrip(string $tripID, string $lineName): Trip {
        // todo: create trip IDs with a prefix, to distinguish between different data providers
        $dataProvider = (new DataProviderBuilder)->build(null, Auth::user());

        if (is_numeric($tripID)) {
            $trip = Trip::where('id', $tripID)->where('linename', $lineName)->first();
        }
        $trip = $trip ?? Trip::where('trip_id', $tripID)->where('linename', $lineName)->first();
        return $trip ?? $dataProvider->fetchHafasTrip($tripID, $lineName);
    }

    public function findEvent(int $id): ?Event {
        return Event::find($id);
    }
}
