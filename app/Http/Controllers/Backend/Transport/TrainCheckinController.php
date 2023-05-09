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
use App\Http\Controllers\Backend\GeoController;
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
        $trip->load('stopoversNEW');

        //Note: Compare with ->format because of timezone differences!
        $firstStop = $trip->stopoversNEW->where('train_station_id', $origin->id)
                                        ->where('departure_planned', $departure->format('Y-m-d H:i:s'))
                                        ->first();
        $lastStop  = $trip->stopoversNEW->where('train_station_id', $destination->id)
                                        ->where('arrival_planned', $arrival->format('Y-m-d H:i:s'))
                                        ->first();

        if (empty($firstStop) || empty($lastStop)) {
            Log::debug('TrainCheckin: No stop found for origin or destination (HafasTrip ' . $trip->trip_id . ')');
            Log::debug('TrainCheckin: Origin-ID: ' . $origin->id . ', Departure: ' . $departure->toIso8601String());
            Log::debug('TrainCheckin: Destination-ID: ' . $destination->id . ', Arrival: ' . $arrival->toIso8601String());
            throw new StationNotOnTripException();
        }

        $overlapping = TransportController::getOverlappingCheckIns(
            user:  $status->user,
            start: $firstStop->departure,
            end:   $lastStop->arrival
        );
        if (!$force && $overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $distance = GeoController::calculateDistance(hafasTrip: $trip, origin: $firstStop, destination: $lastStop);

        $pointCalculation = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $trip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival,
            forceCheckin:    $force
        );
        try {
            $trainCheckin         = TrainCheckin::create([
                                                             'status_id'   => $status->id,
                                                             'user_id'     => $status->user_id,
                                                             'trip_id'     => $trip->trip_id,
                                                             'origin'      => $firstStop->trainStation->ibnr,
                                                             'destination' => $lastStop->trainStation->ibnr,
                                                             'distance'    => $distance,
                                                             'points'      => $pointCalculation->points,
                                                             'departure'   => $firstStop->departure_planned,
                                                             'arrival'     => $lastStop->arrival_planned
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

    public static function changeDestination(TrainCheckin $checkin, TrainStopover $newDestinationStopover): PointReason {
        if ($newDestinationStopover->arrival_planned->isBefore($checkin->origin_stopover->arrival_planned)
            || $newDestinationStopover->is($checkin->origin_stopover)
            || !$checkin->HafasTrip->stopoversNEW->contains('id', $newDestinationStopover->id)
        ) {
            throw new InvalidArgumentException();
        }

        $newDistance = GeoController::calculateDistance(
            hafasTrip:   $checkin->HafasTrip,
            origin:      $checkin->origin_stopover,
            destination: $newDestinationStopover,
        );

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $newDistance,
            hafasTravelType: $checkin->HafasTrip->category,
            departure:       $checkin->origin_stopover->departure,
            arrival:         $newDestinationStopover->arrival,
        );

        $checkin->update([
                             'arrival'     => $newDestinationStopover->arrival_planned->toIso8601String(),
                             'destination' => $newDestinationStopover->trainStation->ibnr,
                             'distance'    => $newDistance,
                             'points'      => $pointsResource->points,
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
        $hafasTrip->loadMissing(['stopoversNEW', 'originStation', 'destinationStation']);

        $originStopover = $hafasTrip->stopoversNEW->filter(function(TrainStopover $stopover) use ($startId) {
            return $stopover->train_station_id === $startId || $stopover->trainStation->ibnr === $startId;
        })->first();

        if ($originStopover === null) {
            throw new StationNotOnTripException();
        }

        //try to refresh the departure time of the origin station
        RefreshStopover::dispatchIf($originStopover && !str_starts_with($tripId, 'manual-'), $originStopover);

        return $hafasTrip;
    }

    /**
     * @throws DistanceDeviationException
     */
    public static function refreshDistanceAndPoints(Status $status, bool $resetPolyline = false): void {
        $trainCheckin = $status->trainCheckin;
        if ($resetPolyline) {
            $trainCheckin->HafasTrip->update(['polyline_id' => null]);
        }
        $firstStop   = $trainCheckin->origin_stopover;
        $lastStop    = $trainCheckin->destination_stopover;
        $distance    = GeoController::calculateDistance(
            hafasTrip:   $trainCheckin->HafasTrip,
            origin:      $firstStop,
            destination: $lastStop
        );
        $oldPoints   = $trainCheckin->points;
        $oldDistance = $trainCheckin->distance;

        if ($trainCheckin->distance === 0 || $distance / $trainCheckin->distance >= 1.15) {
            Log::error(sprintf('Distance deviation for status #%d is greater than 15 percent. Original: %d, new: %d',
                               $status->id,
                               $oldDistance,
                               $distance));
            throw new DistanceDeviationException();
        }

        $pointsResource = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            hafasTravelType: $trainCheckin->HafasTrip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival
        );
        $payload        = [
            'distance' => $distance,
            'points'   => $pointsResource->points,
        ];
        $trainCheckin->update($payload);
        Log::debug(sprintf('Updated distance and points of status #%d: Old: %dm %dp New: %dm %dp',
                           $status->id,
                           $oldDistance,
                           $oldPoints,
                           $distance,
                           $pointsResource->points,
                   ));
    }
}
