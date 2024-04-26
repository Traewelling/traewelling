<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Dto\PointCalculation;
use App\Enum\Business;
use App\Enum\PointReason;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Events\UserCheckedIn;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\DistanceDeviationException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController;
use App\Http\Resources\StatusResource;
use App\Jobs\RefreshStopover;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\Station;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use PDOException;

abstract class TrainCheckinController extends Controller
{

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws AlreadyCheckedInException
     */
    #[ArrayShape([
        'status'               => Status::class,
        'points'               => PointCalculation::class,
        'alsoOnThisConnection' => AnonymousResourceCollection::class
    ])]
    public static function checkin(
        User             $user,
        Trip             $trip,
        Station          $origin,
        Carbon           $departure,
        Station          $destination,
        Carbon           $arrival,
        Business         $travelReason = Business::PRIVATE,
        StatusVisibility $visibility = StatusVisibility::PUBLIC,
        ?string          $body = null,
        ?Event           $event = null,
        bool             $force = false,
        bool             $postOnMastodon = false,
        bool             $shouldChain = false
    ): array {
        if ($departure->isAfter($arrival)) {
            throw new InvalidArgumentException('Departure time must be before arrival time');
        }

        try {
            $status = StatusBackend::createStatus(
                user:       $user,
                business:   $travelReason,
                visibility: $visibility,
                body:       $body,
                event:      $event
            );

            $checkinResponse = self::createCheckin(
                status:      $status,
                trip:        $trip,
                origin:      $origin,
                destination: $destination,
                departure:   $departure,
                arrival:     $arrival,
                force:       $force,
            );

            UserCheckedIn::dispatch(
                $status,
                $postOnMastodon && $user->socialProfile?->mastodon_id !== null,
                $shouldChain
            );

            return $checkinResponse;
        } catch (PDOException $exception) {
            if (isset($status)) {
                $status->delete();
            }
            if ((int) $exception->getCode() === 23000) { // Integrity constraint violation: Duplicate entry
                throw new AlreadyCheckedInException();
            }
            throw $exception; // Other scenarios are not handled
        } catch (Exception $exception) {
            // Delete status if it was created and rethrow exception, so it can be handled by the caller
            if (isset($status)) {
                $status->delete();
            }
            throw $exception;
        }
    }

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws ModelNotFoundException
     * @throws AlreadyCheckedInException
     */
    #[ArrayShape([
        'status'               => Status::class,
        'points'               => PointCalculation::class,
        'alsoOnThisConnection' => AnonymousResourceCollection::class
    ])]
    private static function createCheckin(
        Status  $status,
        Trip    $trip,
        Station $origin,
        Station $destination,
        Carbon  $departure,
        Carbon  $arrival,
        bool    $force = false,
    ): array {
        $trip->load('stopovers');

        //Note: Compare with ->format because of timezone differences!
        $firstStop = $trip->stopovers->where('train_station_id', $origin->id)
                                     ->where('departure_planned', $departure)
                                     ->first();
        $lastStop  = $trip->stopovers->where('train_station_id', $destination->id)
                                     ->where('arrival_planned', $arrival)
                                     ->first();

        // In some rare occasions, the departure time of the origin station has a different timezone
        // than the first stopover. In this case, we need to find it by comparing the departure time
        // in a localtime string format.
        if (empty($firstStop)) {
            $firstStops = $trip->stopovers->where('train_station_id', $origin->id);

            if ($firstStops->count() > 1) {
                $firstStop = $firstStops->filter(function(Stopover $stopover) use ($departure) {
                    return $stopover->departure_planned->format('H:i') === $departure->format('H:i');
                })->first();
            } else {
                $firstStop = $firstStops->first();
            }
        }


        if (empty($firstStop) || empty($lastStop)) {
            throw new StationNotOnTripException(
                origin:      $origin,
                destination: $destination,
                departure:   $departure,
                arrival:     $arrival,
                trip:        $trip
            );
        }

        $overlapping = TransportController::getOverlappingCheckIns(
            user:  $status->user,
            start: $firstStop->departure,
            end:   $lastStop->arrival
        );
        if (!$force && $overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $distance = (new LocationController($trip, $firstStop, $lastStop))->calculateDistance();

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $trip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival,
            tripSource:      $trip->source,
            forceCheckin:    $force,
        );
        try {
            $checkin              = Checkin::create([
                                                        'status_id'               => $status->id,
                                                        'user_id'                 => $status->user_id,
                                                        'trip_id'                 => $trip->trip_id,
                                                        'origin'                  => $firstStop->station->ibnr, //@todo: deprecated - use origin_stopover_id instead
                                                        'origin_stopover_id'      => $firstStop->id,
                                                        'destination'             => $lastStop->station->ibnr, //@todo: deprecated - use destination_stopover_id instead
                                                        'destination_stopover_id' => $lastStop->id,
                                                        'distance'                => $distance,
                                                        'points'                  => $pointCalculation->points,
                                                        'departure'               => $firstStop->departure_planned, //@todo: deprecated - use origin_stopover_id instead
                                                        'arrival'                 => $lastStop->arrival_planned //@todo: deprecated - use destination_stopover_id instead
                                                    ]);
            $alsoOnThisConnection = $checkin->alsoOnThisConnection;

            foreach ($alsoOnThisConnection as $otherStatus) {
                if ($otherStatus?->user && $otherStatus->user->can('view', $status)) {
                    $otherStatus->user->notify(new UserJoinedConnection($status));
                }
            }

            return [
                'status'               => $status,
                'points'               => $pointCalculation,
                'alsoOnThisConnection' => StatusResource::collection($alsoOnThisConnection)
            ];
        } catch (PDOException $exception) {
            if ($exception->getCode() === 23000) { // Integrity constraint violation: Duplicate entry
                throw new AlreadyCheckedInException();
            }
            throw $exception; // Other scenarios are not handled, so rethrow the exception
        }
    }

    public static function changeDestination(
        Checkin  $checkin,
        Stopover $newDestinationStopover
    ): PointReason {
        if ($newDestinationStopover->arrival_planned->isBefore($checkin->originStopover->arrival_planned)
            || $newDestinationStopover->is($checkin->originStopover)
            || !$checkin->trip->stopovers->contains('id', $newDestinationStopover->id)
        ) {
            throw new InvalidArgumentException();
        }

        $newDistance = (new LocationController($checkin->trip, $checkin->originStopover, $newDestinationStopover))
            ->calculateDistance();

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $newDistance,
            hafasTravelType: $checkin->trip->category,
            departure:       $checkin->originStopover->departure,
            arrival:         $newDestinationStopover->arrival,
            tripSource:      $checkin->trip->source
        );

        $checkin->update([
                             'arrival'                 => $newDestinationStopover->arrival_planned,
                             'destination'             => $newDestinationStopover->station->ibnr,
                             'destination_stopover_id' => $newDestinationStopover->id,
                             'distance'                => $newDistance,
                             'points'                  => $pointsResource->points,
                         ]);
        $checkin->refresh();

        StatusUpdateEvent::dispatch($checkin->status);

        return $pointsResource->reason;
    }

    /**
     * @param string $tripId
     * @param string $lineName
     * @param int    $startId
     *
     * @return Trip
     * @throws HafasException
     * @throws StationNotOnTripException
     * @api v1
     */
    public static function getHafasTrip(string $tripId, string $lineName, int $startId): Trip {
        $hafasTrip = HafasController::getHafasTrip($tripId, $lineName);
        $hafasTrip->loadMissing(['stopovers', 'originStation', 'destinationStation']);

        $originStopover = $hafasTrip->stopovers->filter(function(Stopover $stopover) use ($startId) {
            return $stopover->train_station_id === $startId || $stopover->station->ibnr === $startId;
        })->first();

        if ($originStopover === null) {
            throw new StationNotOnTripException();
        }

        //try to refresh the departure time of the origin station
        RefreshStopover::dispatchIf(
            $originStopover && !str_starts_with($hafasTrip->trip_id, 'manual-'),
            $originStopover
        );

        return $hafasTrip;
    }

    /**
     * @throws DistanceDeviationException
     */
    public static function refreshDistanceAndPoints(Status $status, bool $resetPolyline = false): void {
        $checkin = $status->checkin;
        if ($resetPolyline) {
            $checkin->trip->update(['polyline_id' => null]);
        }
        $firstStop   = $checkin->originStopover;
        $lastStop    = $checkin->destinationStopover;
        $distance    = (new LocationController(
            trip:        $checkin->trip,
            origin:      $firstStop,
            destination: $lastStop
        ))->calculateDistance();
        $oldPoints   = $checkin->points;
        $oldDistance = $checkin->distance;

        if ($distance === 0 || ($oldDistance !== 0 && $distance / $oldDistance >= 1.15)) {
            Log::warning(sprintf(
                             'Distance deviation for status #%d is greater than 15 percent. Original: %d, new: %d',
                             $status->id,
                             $oldDistance,
                             $distance
                         ));
            throw new DistanceDeviationException();
        }

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $checkin->trip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival,
            tripSource:      $checkin->trip->source,
            timestampOfView: $status->created_at
        );
        $payload        = [
            'distance' => $distance,
            'points'   => $pointsResource->points,
        ];
        $checkin->update($payload);
        Log::debug(sprintf('Updated distance and points of status #%d: Old: %dm %dp New: %dm %dp',
                           $status->id,
                           $oldDistance,
                           $oldPoints,
                           $distance,
                           $pointsResource->points,
                   ));
    }

    public static function calculateCheckinDuration(Checkin $checkin, bool $update=true): int {
        $departure = $checkin->manual_departure ?? $checkin->originStopover->departure ?? $checkin->departure;
        $arrival   = $checkin->manual_arrival ?? $checkin->destinationStopover->arrival ?? $checkin->arrival;
        $duration  = $arrival->diffInMinutes($departure);
        //don't use eloquent here, because it would trigger the observer (and this function) again
        if ($update) {
            DB::table('train_checkins')->where('id', $checkin->id)->update(['duration' => $duration]);
        }
        return $duration;
    }
}
