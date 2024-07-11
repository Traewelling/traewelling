<?php

namespace App\Repositories;

use App\Exceptions\HafasException;
use App\Http\Controllers\HafasController;
use App\Models\Event;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
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
        if (is_numeric($tripID)) {
            $trip = Trip::where('id', $tripID)->where('linename', $lineName)->first();
        }
        $trip = $trip ?? Trip::where('trip_id', $tripID)->where('linename', $lineName)->first();
        return $trip ?? HafasController::fetchHafasTrip($tripID, $lineName);
    }

    public function findEvent(int $id): ?Event {
        return Event::find($id);
    }
}
