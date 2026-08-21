<?php

namespace App\DataProviders\Repositories;

use App\Enum\TripSource;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class TripRepository
{
    /**
     * @param  Collection<Stopover>  $stopovers  unsaved stopovers as parsed from the data provider
     */
    public function tryToSaveStopovers(Trip $trip, Collection $stopovers): void
    {
        $this->updateOrCreateStopovers($trip, $stopovers->map(fn (Stopover $stopover) => [
            'train_station_id' => $stopover->train_station_id,
            'arrival_planned' => $stopover->arrival_planned,
            'arrival_real' => $stopover->arrival_real,
            'arrival_platform_planned' => $stopover->arrival_platform_planned,
            'arrival_platform_real' => $stopover->arrival_platform_real,
            'departure_planned' => $stopover->departure_planned,
            'departure_real' => $stopover->departure_real,
            'departure_platform_planned' => $stopover->departure_platform_planned,
            'departure_platform_real' => $stopover->departure_platform_real,
            'cancelled' => $stopover->cancelled,
            'station_identifier_id' => $stopover->station_identifier_id,
        ])->all());
    }

    public function updateOrCreateStopovers(Trip $trip, array $stopoverData): Collection
    {
        $stopovers = collect();
        $claimedIds = [];
        $unmatched = [];

        foreach ($stopoverData as $index => $data) {
            try {
                $existing = $this->queryStopoversByPlannedTimes($trip, $data, $claimedIds)
                    ->where('train_station_id', $data['train_station_id'])
                    ->first();

                if ($existing === null) {
                    $unmatched[$index] = $data;

                    continue;
                }

                $claimedIds[] = $existing->id;
                $stopovers[$index] = $this->applyStopoverData($existing, $data);
            } catch (Throwable $exception) {
                $this->logStopoverFailure($trip, $data, $exception);
            }
        }

        foreach ($unmatched as $index => $data) {
            try {
                $existing = $this->queryStopoversByPlannedTimes($trip, $data, $claimedIds)->first();

                if ($existing === null) {
                    $created = Stopover::create($data + ['trip_id' => $trip->trip_id]);
                    $claimedIds[] = $created->id;
                    $stopovers[$index] = $created;

                    continue;
                }

                $claimedIds[] = $existing->id;
                $stopovers[$index] = $this->applyStopoverData($existing, $data);
            } catch (Throwable $exception) {
                $this->logStopoverFailure($trip, $data, $exception);
            }
        }

        return $stopovers->sortKeys()->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function logStopoverFailure(Trip $trip, array $data, Throwable $exception): void
    {
        Log::critical('Failed creating Stopover: ' . $exception->getMessage(), [
            'trip' => $trip->trip_id,
            'station' => $data['train_station_id'],
            'departure' => $data['departure_planned'],
            'arrival' => $data['arrival_planned'],
        ]);
        report($exception);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $claimedIds  stopovers already assigned to another stop of this trip
     */
    private function queryStopoversByPlannedTimes(Trip $trip, array $data, array $claimedIds): Builder
    {
        return Stopover::where('trip_id', $trip->trip_id)
            ->where('arrival_planned', $data['arrival_planned'])
            ->where('departure_planned', $data['departure_planned'])
            ->whereNotIn('id', $claimedIds)
            ->orderBy('id');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyStopoverData(Stopover $stopover, array $data): Stopover
    {
        $stopover->update([
            'train_station_id' => $data['train_station_id'],
            'station_identifier_id' => $data['station_identifier_id'],
            'arrival_real' => $data['arrival_real'],
            'departure_real' => $data['departure_real'],
            'arrival_platform_planned' => $data['arrival_platform_planned'],
            'departure_platform_planned' => $data['departure_platform_planned'],
            'arrival_platform_real' => $data['arrival_platform_real'],
            'departure_platform_real' => $data['departure_platform_real'],
            'cancelled' => $data['cancelled'],
        ]);

        return $stopover;
    }

    public function getCurrentActiveTrips(TripSource $source): Collection
    {
        // To only refresh checked in trips join train_checkins:
        return Trip::join('train_checkins', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->join('train_stopovers as origin_stopover', 'origin_stopover.id', '=', 'train_checkins.origin_stopover_id')
            ->join('train_stopovers as destionation_stopover', 'destionation_stopover.id', '=', 'train_checkins.destination_stopover_id')
            ->where(function ($query) {
                $query->where('destionation_stopover.arrival_planned', '>=', now()->subMinutes(20))
                    ->orWhere('destionation_stopover.arrival_real', '>=', now()->subMinutes(20));
            })
            ->where(function ($query) {
                $query->where('origin_stopover.departure_planned', '<=', now()->addMinutes(20))
                    ->orWhere('origin_stopover.departure_real', '<=', now()->addMinutes(20));
            })
            ->where(function ($query) {
                $query->where('hafas_trips.last_refreshed', '<', now()->subMinutes(config('trwl.refresh.min_trip_interval_minutes', 2)))
                    ->orWhereNull('hafas_trips.last_refreshed');
            })
            ->where('hafas_trips.source', $source->value)
            ->select('hafas_trips.*')
            ->distinct()
            ->orderBy('hafas_trips.last_refreshed')
            ->get();
    }
}
