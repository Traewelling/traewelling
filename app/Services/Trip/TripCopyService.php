<?php

declare(strict_types=1);

namespace App\Services\Trip;

use App\Enum\TripSource;
use App\Events\StatusUpdateEvent;
use App\Models\Checkin;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use App\Services\Checkin\CheckinService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Copies a trip into a new manual trip owned by a user, so they can correct data a provider got
 * wrong. Only the copying user's own checkins move over, everyone else stays on the original.
 */
class TripCopyService
{
    public function __construct(private readonly CheckinService $checkinService) {}

    public function copy(Trip $trip, User $user): Trip
    {
        return DB::transaction(function () use ($trip, $user) {
            $copy = Trip::create([
                'trip_id' => $this->generateUniqueTripId(),
                'category' => $trip->category,
                'mode' => $trip->mode,
                'number' => $trip->number,
                'linename' => $trip->linename,
                'route_color' => $trip->route_color,
                'route_text_color' => $trip->route_text_color,
                'journey_number' => $trip->journey_number,
                'operator_id' => $trip->operator_id,
                'origin_id' => $trip->origin_id,
                'destination_id' => $trip->destination_id,
                'departure' => $trip->departure,
                'arrival' => $trip->arrival,
                'polyline_id' => $trip->polyline_id,
                'motis_source_license_id' => $trip->motis_source_license_id,
                'source' => TripSource::USER,
                'user_id' => $user->id,
            ]);

            $stopoverMap = $this->copyStopovers($trip, $copy);
            $this->moveOwnCheckins($trip, $copy, $user, $stopoverMap);

            return $copy;
        });
    }

    /**
     * @return array<int, int> ID of the original stopover mapped to the ID of its copy
     */
    private function copyStopovers(Trip $trip, Trip $copy): array
    {
        $map = [];

        foreach ($trip->stopovers as $stopover) {
            $newStopover = Stopover::create([
                'trip_id' => $copy->trip_id,
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
                'route_segment_id' => $stopover->route_segment_id,
            ]);

            $map[$stopover->id] = $newStopover->id;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $stopoverMap
     */
    private function moveOwnCheckins(Trip $trip, Trip $copy, User $user, array $stopoverMap): void
    {
        $checkins = Checkin::with('status')
            ->where('trip_id', $trip->trip_id)
            ->where('user_id', $user->id)
            ->get();

        foreach ($checkins as $checkin) {
            $origin = $stopoverMap[$checkin->origin_stopover_id] ?? null;
            $destination = $stopoverMap[$checkin->destination_stopover_id] ?? null;

            if ($origin === null || $destination === null) {
                continue;
            }

            $checkin->update([
                'trip_id' => $copy->trip_id,
                'origin_stopover_id' => $origin,
                'destination_stopover_id' => $destination,
            ]);

            if ($checkin->status !== null) {
                $this->checkinService->refreshDistanceAndPoints($checkin->status);

                StatusUpdateEvent::dispatch($checkin->status);
            }
        }
    }

    private function generateUniqueTripId(): string
    {
        $tripId = Str::uuid()->toString();
        while (Trip::where('trip_id', $tripId)->exists()) {
            $tripId = Str::uuid()->toString();
        }

        return $tripId;
    }
}
