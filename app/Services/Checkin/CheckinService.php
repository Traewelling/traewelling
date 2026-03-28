<?php

namespace App\Services\Checkin;

use App\Dto\Internal\CheckInRequestDto;
use App\Dto\Internal\CheckinSuccessDto;
use App\Enum\PointReason;
use App\Events\StatusUpdateEvent;
use App\Events\UserCheckedIn;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\CheckinException;
use App\Exceptions\NotAllowedToCheckinOtherUserException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\PointsCalculationController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use App\Notifications\YouHaveBeenCheckedIn;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use PDOException;
use Throwable;

class CheckinService
{
    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws AlreadyCheckedInException
     * @throws CheckinException
     * @throws NotAllowedToCheckinOtherUserException
     */
    public function checkin(CheckInRequestDto $dto): CheckinSuccessDto
    {
        $users = $this->precheckUserCheckin($dto);
        $checkinResponse = $this->checkinFlow($dto);

        return $this->checkinOtherUsers($users, $dto, $checkinResponse);
    }

    private function checkinFlow(CheckInRequestDto $dto, ?User $checkedInBy = null): CheckinSuccessDto
    {
        if ($dto->departure->isAfter($dto->arrival)) {
            throw new CheckinException('Departure time must be before arrival time');
        }

        try {
            DB::beginTransaction();
            $status = StatusBackend::createStatus(
                user: $dto->user,
                business: $dto->travelReason,
                visibility: $dto->statusVisibility,
                body: $dto->body,
                event: $dto->event,
                createdByUser: $checkedInBy
            );

            $checkinResponse = $this->createCheckin(
                status: $status,
                trip: $dto->trip,
                origin: $dto->origin,
                destination: $dto->destination,
                departure: $dto->departure,
                arrival: $dto->arrival,
                force: $dto->forceFlag,
                checkedInBy: $checkedInBy
            );

            UserCheckedIn::dispatch(
                $status,
                $dto->postOnMastodonFlag && $dto->user->socialProfile?->mastodon_id !== null,
                $dto->chainFlag
            );

            DB::commit();

            return $checkinResponse;
        } catch (PDOException $exception) {
            DB::rollBack();
            if ((int) $exception->getCode() === 23000) { // Integrity constraint violation: Duplicate entry
                throw new AlreadyCheckedInException();
            }
            throw $exception; // Other scenarios are not handled
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @throws NotAllowedToCheckinOtherUserException
     *
     * @returns Collection|User[]
     */
    private function precheckUserCheckin(CheckInRequestDto $dto): Collection
    {
        $withUsers = [];
        if (isset($dto->userIds)) {
            $withUsers = User::whereIn('id', $dto->userIds)->get();
            $forbiddenUsers = collect();
            foreach ($withUsers as $user) {
                if (!Auth::user()?->can('checkin', $user)) {
                    $forbiddenUsers->push($user);
                }
            }
            if ($forbiddenUsers->isNotEmpty()) {
                $forbiddenUserIds = $forbiddenUsers->pluck('id')->toArray();
                throw new NotAllowedToCheckinOtherUserException($forbiddenUserIds);
            }
        }

        return $withUsers;
    }

    private function checkinOtherUsers(?Collection $withUsers, CheckInRequestDto $dto, CheckinSuccessDto $checkinResponse): CheckinSuccessDto
    {
        $byUser = $dto->user;
        foreach ($withUsers ?? [] as $user) {
            $dto->setUser($user);
            $dto->setBody(null);
            $dto->setStatusVisibility($user->default_status_visibility);
            $dto->setPostOnMastodonFlag(false);
            try {
                $checkin = $this->checkinFlow($dto, $byUser);
            } catch (Throwable) {
                continue;
            }
            $user->notify(new YouHaveBeenCheckedIn($checkin->status, auth()->user()));
            $checkinResponse->alsoOnThisConnection->push($checkin->status);
        }

        return $checkinResponse;
    }

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws ModelNotFoundException
     * @throws AlreadyCheckedInException
     */
    private function createCheckin(
        Status $status,
        Trip $trip,
        Station $origin,
        Station $destination,
        Carbon $departure,
        Carbon $arrival,
        bool $force = false,
        ?User $checkedInBy = null
    ): CheckinSuccessDto {
        $trip->load('stopovers');

        // Note: Compare with ->format because of timezone differences!
        $firstStop = $trip->stopovers->where('train_station_id', $origin->id)
            ->where('departure_planned', $departure)
            ->first();
        $lastStop = $trip->stopovers->where('train_station_id', $destination->id)
            ->where('arrival_planned', $arrival)
            ->first();

        // In some rare occasions, the departure time of the origin station has a different timezone
        // than the first stopover. In this case, we need to find it by comparing the departure time
        // in a localtime string format.
        if (empty($firstStop)) {
            $firstStops = $trip->stopovers->where('train_station_id', $origin->id);

            if ($firstStops->count() > 1) {
                $firstStop = $firstStops->filter(function (Stopover $stopover) use ($departure) {
                    return $stopover->departure_planned->format('H:i') === $departure->format('H:i');
                })->first();
            } else {
                $firstStop = $firstStops->first();
            }
        }

        if (empty($firstStop) || empty($lastStop)) {
            throw new StationNotOnTripException(
                origin: $origin,
                destination: $destination,
                departure: $departure,
                arrival: $arrival,
                trip: $trip
            );
        }

        $overlapping = TransportController::getOverlappingCheckIns(
            user: $status->user,
            start: $firstStop->departure,
            end: $lastStop->arrival
        );
        if (!$force && $overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $distance = (new LocationController($trip, $firstStop, $lastStop))->calculateDistance();

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $trip->category,
            departure: $firstStop->departure,
            arrival: $lastStop->arrival,
            tripSource: $trip->source,
            forceCheckin: $force,
        );
        try {
            /** @var Checkin $checkin */
            $checkin = Checkin::create([
                'status_id' => $status->id,
                'user_id' => $status->user_id,
                'trip_id' => $trip->trip_id,
                'origin_stopover_id' => $firstStop->id,
                'destination_stopover_id' => $lastStop->id,
                'distance' => $distance,
                'points' => $pointCalculation->points,
                'departure' => $firstStop->departure_planned, // @todo: deprecated - use origin_stopover_id instead
                'arrival' => $lastStop->arrival_planned, // @todo: deprecated - use destination_stopover_id instead
            ]);
            $alsoOnThisConnection = $checkin->alsoOnThisConnection;

            foreach ($alsoOnThisConnection as $otherStatus) {
                if ($otherStatus?->user && $otherStatus->user->can('view', $status)) {
                    if ($checkedInBy?->id === $otherStatus->user->id || $otherStatus->user->id === $status->user_id) {
                        // don't notify the user about their own checkin
                        continue;
                    }

                    $otherStatus->user->notify(new UserJoinedConnection($status));
                }
            }

            return new CheckinSuccessDto($status, $pointCalculation, $alsoOnThisConnection);
        } catch (PDOException $exception) {
            if ($exception->getCode() === 23000) { // Integrity constraint violation: Duplicate entry
                throw new AlreadyCheckedInException();
            }
            throw $exception; // Other scenarios are not handled, so rethrow the exception
        }
    }

    public function changeDestination(
        Checkin $checkin,
        Stopover $newDestinationStopover
    ): PointReason {
        if ($newDestinationStopover->arrival_planned->isBefore($checkin->originStopover->arrival_planned)
            || $newDestinationStopover->is($checkin->originStopover)
            || !$checkin->trip->stopovers->contains('id', $newDestinationStopover->id)
        ) {
            throw new InvalidArgumentException();
        }

        $newDistance = new LocationController($checkin->trip, $checkin->originStopover, $newDestinationStopover)
            ->calculateDistance();

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $newDistance,
            hafasTravelType: $checkin->trip->category,
            departure: $checkin->originStopover->departure,
            arrival: $newDestinationStopover->arrival,
            tripSource: $checkin->trip->source
        );

        $checkin->update([
            'arrival' => $newDestinationStopover->arrival_planned,
            'destination_stopover_id' => $newDestinationStopover->id,
            'distance' => $newDistance,
            'points' => $pointsResource->points,
        ]);
        $checkin->refresh();

        StatusUpdateEvent::dispatch($checkin->status);

        return $pointsResource->reason;
    }

    public function refreshDistanceAndPoints(Status $status, bool $resetPolyline = false): void
    {
        $checkin = $status->checkin;
        if ($checkin === null || $checkin->trip === null
            || $checkin->originStopover === null || $checkin->destinationStopover === null) {
            Log::warning('refreshDistanceAndPoints: skipping status with missing relations', ['status_id' => $status->id]);

            return;
        }

        if ($resetPolyline) {
            $checkin->trip->update(['polyline_id' => null]);
        }
        $firstStop = $checkin->originStopover;
        $lastStop = $checkin->destinationStopover;
        $distance = new LocationController(
            trip: $checkin->trip,
            origin: $firstStop,
            destination: $lastStop
        )->calculateDistance();
        $oldPoints = $checkin->points;
        $oldDistance = $checkin->distance ?? 0;

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $checkin->trip->category,
            departure: $firstStop->departure,
            arrival: $lastStop->arrival,
            tripSource: $checkin->trip->source,
            timestampOfView: $status->created_at
        );
        $checkin->update([
            'distance' => $distance,
            'points' => $pointsResource->points,
        ]);
        Log::debug(sprintf('Updated distance and points of status #%d: Old: %dm %dp New: %dm %dp',
            $status->id,
            $oldDistance,
            $oldPoints,
            $distance,
            $pointsResource->points,
        ));
    }

    public function calculateCheckinDuration(Checkin $checkin, bool $update = true): int
    {
        $departure = $checkin->manual_departure ?? $checkin->originStopover->departure ?? $checkin->departure;
        $arrival = $checkin->manual_arrival ?? $checkin->destinationStopover->arrival ?? $checkin->arrival;
        $duration = $departure->diffInMinutes($arrival);

        if ($duration < 0) {
            // diffInMinutes() returns negative minutes if arrival is before departure
            $duration = 0;
        }

        // Bypass Eloquent to avoid triggering the observer (and this method) again
        if ($update) {
            DB::table('train_checkins')->where('id', $checkin->id)->update(['duration' => $duration]);
        }

        return $duration;
    }
}
