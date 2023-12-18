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
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        HafasTrip        $hafasTrip,
        TrainStation     $origin,
        Carbon           $departure,
        TrainStation     $destination,
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
                eventId:    $event?->id
            );

            $trainCheckinResponse = self::createTrainCheckin(
                status:      $status,
                trip:        $hafasTrip,
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

            return $trainCheckinResponse;
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
    private static function createTrainCheckin(
        Status       $status,
        HafasTrip    $trip,
        TrainStation $origin,
        TrainStation $destination,
        Carbon       $departure,
        Carbon       $arrival,
        bool         $force = false,
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
                $firstStop = $firstStops->filter(function(TrainStopover $stopover) use ($departure) {
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
            $trainCheckin         = TrainCheckin::create([
                                                             'status_id'               => $status->id,
                                                             'user_id'                 => $status->user_id,
                                                             'trip_id'                 => $trip->trip_id,
                                                             'origin'                  => $firstStop->trainStation->ibnr, //@todo: deprecated - use origin_stopover_id instead
                                                             'origin_stopover_id'      => $firstStop->id,
                                                             'destination'             => $lastStop->trainStation->ibnr, //@todo: deprecated - use destination_stopover_id instead
                                                             'destination_stopover_id' => $lastStop->id,
                                                             'distance'                => $distance,
                                                             'points'                  => $pointCalculation->points,
                                                             'departure'               => $firstStop->departure_planned, //@todo: deprecated - use origin_stopover_id instead
                                                             'arrival'                 => $lastStop->arrival_planned //@todo: deprecated - use destination_stopover_id instead
                                                         ]);
            $alsoOnThisConnection = $trainCheckin->alsoOnThisConnection;

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
        TrainCheckin  $checkin,
        TrainStopover $newDestinationStopover
    ): PointReason {
        if ($newDestinationStopover->arrival_planned->isBefore($checkin->origin_stopover->arrival_planned)
            || $newDestinationStopover->is($checkin->origin_stopover)
            || !$checkin->HafasTrip->stopovers->contains('id', $newDestinationStopover->id)
        ) {
            throw new InvalidArgumentException();
        }

        $newDistance = (new LocationController($checkin->HafasTrip, $checkin->origin_stopover, $newDestinationStopover))
            ->calculateDistance();

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $newDistance,
            hafasTravelType: $checkin->HafasTrip->category,
            departure:       $checkin->origin_stopover->departure,
            arrival:         $newDestinationStopover->arrival,
            tripSource:      $checkin->HafasTrip->source
        );

        $checkin->update([
                             'arrival'                 => $newDestinationStopover->arrival_planned,
                             'destination'             => $newDestinationStopover->trainStation->ibnr,
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
     * @return HafasTrip
     * @throws HafasException
     * @throws StationNotOnTripException
     * @api v1
     */
    public static function getHafasTrip(string $tripId, string $lineName, int $startId): HafasTrip {
        $hafasTrip = HafasController::getHafasTrip($tripId, $lineName);
        $hafasTrip->loadMissing(['stopovers', 'originStation', 'destinationStation']);

        $originStopover = $hafasTrip->stopovers->filter(function(TrainStopover $stopover) use ($startId) {
            return $stopover->train_station_id === $startId || $stopover->trainStation->ibnr === $startId;
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
        $checkin = $status->trainCheckin;
        if ($resetPolyline) {
            $checkin->HafasTrip->update(['polyline_id' => null]);
        }
        $firstStop   = $checkin->origin_stopover;
        $lastStop    = $checkin->destination_stopover;
        $distance    = (new LocationController(
            hafasTrip:   $checkin->HafasTrip,
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
            hafasTravelType: $checkin->HafasTrip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival,
            tripSource:      $checkin->HafasTrip->source,
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
}
