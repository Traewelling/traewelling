<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\UserCheckedIn;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController;
use App\Http\Resources\PointsCalculationResource;
use App\Http\Resources\StatusResource;
use App\Jobs\FetchCarriageSequence;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
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
     * @throws NotConnectedException
     * @throws AlreadyCheckedInException
     */
    #[ArrayShape([
        'status'               => Status::class,
        'points'               => PointsCalculationResource::class,
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
        bool             $postOnTwitter = false,
        bool             $postOnMastodon = false
    ): array {
        if ($departure->isAfter($arrival)) {
            throw new InvalidArgumentException('Departure time must be before arrival time');
        }

        $status = StatusBackend::createStatus(
            user:       $user,
            business:   $travelReason,
            visibility: $visibility,
            body:       $body,
            eventId:    $event?->id
        );

        try {
            $trainCheckinResponse = self::createTrainCheckin(
                status:      $status,
                trip:        $hafasTrip,
                origin:      $origin,
                destination: $destination,
                departure:   $departure,
                arrival:     $arrival,
                force:       $force,
            );

            UserCheckedIn::dispatch($status,
                                    $postOnTwitter && $user?->socialProfile?->twitter_id !== null && config('trwl.post_social') === true,
                                    $postOnMastodon && $user?->socialProfile?->mastodon_id !== null && config('trwl.post_social') === true);

            return $trainCheckinResponse;
        } catch (Exception $exception) {
            // Delete status if it was created and rethrow exception, so it can be handled by the caller
            $status->delete();
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
        'points'               => PointsCalculationResource::class,
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

        $pointsResource = PointsCalculationController::calculatePoints(
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
                                                             'points'      => $pointsResource['points'],
                                                             'departure'   => $firstStop->departure_planned,
                                                             'arrival'     => $lastStop->arrival_planned
                                                         ]);
            $alsoOnThisConnection = $trainCheckin->alsoOnThisConnection;

            foreach ($alsoOnThisConnection as $otherStatus) {
                if ($otherStatus?->user && $otherStatus->user->can('view', $status)) {
                    $otherStatus->user->notify(new UserJoinedConnection($status));
                }
            }

            FetchCarriageSequence::dispatch($firstStop);

            return [
                'status'               => $status,
                'points'               => $pointsResource,
                'alsoOnThisConnection' => StatusResource::collection($alsoOnThisConnection)
            ];
        } catch (PDOException $exception) {
            if ($exception->getCode() === 23000) { // Integrity constraint violation: Duplicate entry
                throw new AlreadyCheckedInException();
            }
            throw $exception; // Other scenarios are not handled, so rethrow the exception
        }
    }
}
