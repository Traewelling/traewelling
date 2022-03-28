<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\Social\MastodonController;
use App\Http\Controllers\Backend\Social\TwitterController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\TransportController;
use App\Http\Resources\PointsCalculationResource;
use App\Http\Resources\StatusResource;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JetBrains\PhpStorm\ArrayShape;

abstract class TrainCheckinController extends Controller
{

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException|NotConnectedException
     * @throws HafasException
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
        Business         $tripType = Business::PRIVATE,
        StatusVisibility $visibility = StatusVisibility::PUBLIC,
        string           $body = null,
        Event            $event = null,
        bool             $force = false,
        bool             $postOnTwitter = false,
        bool             $postOnMastodon = false
    ): array {
        $status = StatusBackend::createStatus(
            user:       $user,
            business:   $tripType,
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

            if ($postOnTwitter && $user->socialProfile?->twitter_id !== null) {
                TwitterController::postStatus($status);
            }
            if ($postOnMastodon && $user->socialProfile?->mastodon_id !== null) {
                MastodonController::postStatus($status);
            }

            return $trainCheckinResponse;
        } catch (CheckInCollisionException|HafasException|StationNotOnTripException $exception) {
            $status?->delete();
            throw $exception;
        }
    }

    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws ModelNotFoundException
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

        $firstStop = $trip->stopoversNEW->where('train_station_id', $origin->id)
                                        ->where('departure_planned', $departure)->first();

        $lastStop = $trip->stopoversNEW->where('train_station_id', $destination->id)
                                       ->where('arrival_planned', $arrival)->first();

        if (empty($firstStop) || empty($lastStop)) {
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
        $alsoOnThisConnection = $trainCheckin->alsoOnThisConnection->reject(function($status) {
            return $status->statusInvisibleToMe;
        });

        foreach ($alsoOnThisConnection as $otherStatus) {
            if ($otherStatus?->user && $otherStatus->user->can('view', $status)) {
                $otherStatus->user->notify(new UserJoinedConnection($status));
            }
        }

        return [
            'status'               => $status,
            'points'               => $pointsResource,
            'alsoOnThisConnection' => StatusResource::collection($alsoOnThisConnection)
        ];
    }
}
