<?php

declare(strict_types=1);

namespace App\Services\Trip;

use App\Enum\HafasTravelType;
use App\Events\StatusUpdateEvent;
use App\Exceptions\ManualTripValidationException;
use App\Exceptions\StopoverInUseException;
use App\Jobs\RefreshPolyline;
use App\Models\Checkin;
use App\Models\Operator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Services\Checkin\CheckinService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TripEditService
{
    public function __construct(private readonly CheckinService $checkinService) {}

    /**
     * @param  array{category?: string, lineName?: string, journeyNumber?: int|null, operatorUuid?: string|null}  $data
     *
     * @throws ManualTripValidationException
     */
    public function updateTrip(Trip $trip, array $data): Trip
    {
        return DB::transaction(function () use ($trip, $data) {
            $attributes = [];

            if (array_key_exists('category', $data)) {
                $attributes['category'] = HafasTravelType::from($data['category']);
            }
            if (array_key_exists('lineName', $data)) {
                // Manual trips carry the line name in both fields //TODO: cleanup columns
                $attributes['linename'] = $data['lineName'];
                $attributes['number'] = $data['lineName'];
            }
            if (array_key_exists('journeyNumber', $data)) {
                $attributes['journey_number'] = $data['journeyNumber'];
            }
            if (array_key_exists('operatorUuid', $data)) {
                $attributes['operator_id'] = $this->resolveOperator($data['operatorUuid'])?->id;
            }

            if ($attributes !== []) {
                $trip->update($attributes);
            }

            $trip->refresh();

            $this->refreshCheckins($trip);

            return $trip;
        });
    }

    /**
     * @param  array{stationUuid: string, arrivalPlanned?: string|null, departurePlanned?: string|null, arrivalReal?: string|null, departureReal?: string|null, arrivalPlatformPlanned?: string|null, departurePlatformPlanned?: string|null, arrivalPlatformReal?: string|null, departurePlatformReal?: string|null, cancelled?: bool}  $data
     *
     * @throws ManualTripValidationException
     */
    public function addStopover(Trip $trip, array $data): Stopover
    {
        return DB::transaction(function () use ($trip, $data) {
            $arrival = $this->parseDate($data['arrivalPlanned'] ?? null);
            $departure = $this->parseDate($data['departurePlanned'] ?? null);

            $stopover = Stopover::create([
                'trip_id' => $trip->trip_id,
                'train_station_id' => $this->resolveStation($data['stationUuid'])->id,
                'arrival_planned' => $arrival ?? $departure,
                'departure_planned' => $departure ?? $arrival,
                'arrival_real' => $this->parseDate($data['arrivalReal'] ?? null),
                'departure_real' => $this->parseDate($data['departureReal'] ?? null),
                'arrival_platform_planned' => $data['arrivalPlatformPlanned'] ?? null,
                'departure_platform_planned' => $data['departurePlatformPlanned'] ?? null,
                'arrival_platform_real' => $data['arrivalPlatformReal'] ?? null,
                'departure_platform_real' => $data['departurePlatformReal'] ?? null,
                'cancelled' => $data['cancelled'] ?? false,
            ]);

            $this->syncTrip($trip, resetRouting: true);

            return $stopover->fresh();
        });
    }

    /**
     * @param  array{stationUuid?: string, arrivalPlanned?: string|null, departurePlanned?: string|null, arrivalReal?: string|null, departureReal?: string|null, arrivalPlatformPlanned?: string|null, departurePlatformPlanned?: string|null, arrivalPlatformReal?: string|null, departurePlatformReal?: string|null, cancelled?: bool}  $data
     *
     * @throws ManualTripValidationException
     */
    public function updateStopover(Stopover $stopover, array $data): Stopover
    {
        return DB::transaction(function () use ($stopover, $data) {
            $trip = $stopover->trip;
            $stationChanged = false;

            $attributes = [];

            if (array_key_exists('stationUuid', $data)) {
                $station = $this->resolveStation($data['stationUuid']);
                $stationChanged = $station->id !== $stopover->train_station_id;
                $attributes['train_station_id'] = $station->id;
                if ($stationChanged) {
                    // The identifier belongs to the previous station and is meaningless now.
                    $attributes['station_identifier_id'] = null;
                }
            }

            $timeFields = [
                'arrivalPlanned' => 'arrival_planned',
                'departurePlanned' => 'departure_planned',
                'arrivalReal' => 'arrival_real',
                'departureReal' => 'departure_real',
            ];
            foreach ($timeFields as $input => $column) {
                if (array_key_exists($input, $data)) {
                    $attributes[$column] = $this->parseDate($data[$input]);
                }
            }

            $plainFields = [
                'arrivalPlatformPlanned' => 'arrival_platform_planned',
                'departurePlatformPlanned' => 'departure_platform_planned',
                'arrivalPlatformReal' => 'arrival_platform_real',
                'departurePlatformReal' => 'departure_platform_real',
                'cancelled' => 'cancelled',
            ];
            foreach ($plainFields as $input => $column) {
                if (array_key_exists($input, $data)) {
                    $attributes[$column] = $data[$input];
                }
            }

            if ($attributes !== []) {
                $stopover->update($attributes);
            }

            $stopover->refresh();
            if ($stopover->arrival_planned === null && $stopover->departure_planned === null) {
                throw new ManualTripValidationException('A stopover needs at least a planned arrival or departure.');
            }
            if ($stopover->arrival_planned === null) {
                $stopover->update(['arrival_planned' => $stopover->departure_planned]);
            }
            if ($stopover->departure_planned === null) {
                $stopover->update(['departure_planned' => $stopover->arrival_planned]);
            }

            $this->syncTrip($trip, resetRouting: $stationChanged);

            return $stopover->fresh();
        });
    }

    /**
     * @throws ManualTripValidationException
     * @throws StopoverInUseException
     */
    public function deleteStopover(Stopover $stopover): void
    {
        DB::transaction(function () use ($stopover) {
            $trip = $stopover->trip;

            $isReferencedByCheckins = Checkin::where('origin_stopover_id', $stopover->id)
                ->orWhere('destination_stopover_id', $stopover->id)
                ->exists();
            if ($isReferencedByCheckins) {
                throw new StopoverInUseException('This stopover is referenced by checkins and cannot be deleted.');
            }

            $stopover->delete();

            $this->syncTrip($trip, resetRouting: true);
        });
    }

    /**
     * Recalculate everything that is derived from the stopovers of this trip.
     *
     * @throws ManualTripValidationException
     */
    private function syncTrip(Trip $trip, bool $resetRouting): void
    {
        $stopovers = $this->orderedStopovers($trip);

        $this->validateStopovers($stopovers);
        $this->validateCheckinOrder($trip, $stopovers);

        $first = $stopovers->first();
        $last = $stopovers->last();

        $trip->update([
            'origin_id' => $first->train_station_id,
            'destination_id' => $last->train_station_id,
            'departure' => $first->departure_planned,
            'arrival' => $last->arrival_planned,
        ]);

        if ($resetRouting) {
            $this->resetRouting($trip);
        }

        $this->refreshCheckins($trip->fresh());
    }

    /**
     * @return Collection<int, Stopover>
     */
    private function orderedStopovers(Trip $trip): Collection
    {
        return Stopover::where('trip_id', $trip->trip_id)
            ->orderBy('arrival_planned')
            ->orderBy('departure_planned')
            ->get();
    }

    /**
     * @param  Collection<int, Stopover>  $stopovers
     *
     * @throws ManualTripValidationException
     */
    private function validateStopovers(Collection $stopovers): void
    {
        if ($stopovers->count() < 2) {
            throw new ManualTripValidationException('A trip needs at least two stopovers.');
        }

        $seen = [];
        $previous = null;
        foreach ($stopovers as $stopover) {
            if ($stopover->arrival_planned === null || $stopover->departure_planned === null) {
                throw new ManualTripValidationException('A stopover needs at least a planned arrival or departure.');
            }
            if ($stopover->departure_planned->isBefore($stopover->arrival_planned)) {
                throw new ManualTripValidationException('Departure must not be before arrival at the same stopover.');
            }
            if ($previous !== null && $stopover->arrival_planned->isBefore($previous->departure_planned)) {
                throw new ManualTripValidationException('Stopovers must be in chronological order.');
            }

            $key = $stopover->train_station_id
                   . '|' . $stopover->arrival_planned->toIso8601String()
                   . '|' . $stopover->departure_planned->toIso8601String();
            if (isset($seen[$key])) {
                throw new ManualTripValidationException('Duplicate stopover for station ' . $stopover->station?->name);
            }
            $seen[$key] = true;

            $previous = $stopover;
        }

        $maxHours = config('trwl.max_journey_hours');
        $durationInHours = $stopovers->first()->departure_planned->diffInHours($stopovers->last()->arrival_planned);
        if ($durationInHours > $maxHours) {
            throw new ManualTripValidationException(sprintf('Trip duration exceeds maximum allowed duration of %d hours', $maxHours));
        }
    }

    /**
     * The position of a stopover within a trip is derived from its planned times, so shifting one
     * can move it past a stopover a checkin refers to. Reject changes that would leave a checkin
     * with its destination before its origin.
     *
     * @param  Collection<int, Stopover>  $stopovers
     *
     * @throws ManualTripValidationException
     */
    private function validateCheckinOrder(Trip $trip, Collection $stopovers): void
    {
        $positions = [];
        foreach ($stopovers->values() as $index => $stopover) {
            $positions[$stopover->id] = $index;
        }

        $checkins = Checkin::where('trip_id', $trip->trip_id)
            ->get(['id', 'origin_stopover_id', 'destination_stopover_id']);

        foreach ($checkins as $checkin) {
            $origin = $positions[$checkin->origin_stopover_id] ?? null;
            $destination = $positions[$checkin->destination_stopover_id] ?? null;

            if ($origin === null || $destination === null) {
                continue;
            }
            if ($origin >= $destination) {
                throw new ManualTripValidationException('This change would reverse an existing checkin on this trip.');
            }
        }
    }

    /**
     * Drop the cached routing of this trip. Route segments are bound to a concrete pair of
     * stations, so they become invalid as soon as the set of stopovers changes.
     */
    private function resetRouting(Trip $trip): void
    {
        Stopover::where('trip_id', $trip->trip_id)->update(['route_segment_id' => null]);
        $trip->update(['polyline_id' => null]);

        RefreshPolyline::dispatch($trip);
    }

    private function refreshCheckins(Trip $trip): void
    {
        $checkins = Checkin::with(['status', 'originStopover', 'destinationStopover'])
            ->where('trip_id', $trip->trip_id)
            ->get();

        foreach ($checkins as $checkin) {
            if ($checkin->status === null || $checkin->originStopover === null || $checkin->destinationStopover === null) {
                continue;
            }

            $checkin->update([
                'departure' => $checkin->originStopover->departure_planned,
                'arrival' => $checkin->destinationStopover->arrival_planned,
            ]);

            $this->checkinService->refreshDistanceAndPoints($checkin->status);

            StatusUpdateEvent::dispatch($checkin->status->fresh());
        }
    }

    private function resolveStation(string $stationUuid): Station
    {
        return Station::where('uuid', $stationUuid)->firstOrFail();
    }

    private function resolveOperator(?string $operatorUuid): ?Operator
    {
        if ($operatorUuid === null) {
            return null;
        }

        return Operator::where('id', $operatorUuid)->firstOrFail();
    }

    private function parseDate(?string $date): ?Carbon
    {
        return $date === null ? null : Carbon::parse($date);
    }
}
