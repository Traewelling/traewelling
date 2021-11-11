<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Exceptions\CheckInCollisionException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransportController;
use App\Http\Resources\StatusResource;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JetBrains\PhpStorm\ArrayShape;

abstract class TrainCheckinController extends Controller
{
    /**
     * @throws StationNotOnTripException
     * @throws CheckInCollisionException
     * @throws ModelNotFoundException
     */
    #[ArrayShape([
        'status'               => StatusResource::class,
        'alsoOnThisConnection' => AnonymousResourceCollection::class
    ])]
    public static function createTrainCheckin(
        Status    $status,
        HafasTrip $trip,
        int       $entryStop,
        int       $exitStop,
        Carbon    $departure = null,
        Carbon    $arrival = null,
        bool      $ibnr = false
    ): array {
        $trip->load('stopoversNEW');

        if (!$ibnr) {
            $firstStop = $trip->stopoversNEW->where('train_station_id', $entryStop)
                                            ->where('departure_planned', $departure)->first();

            $lastStop = $trip->stopoversNEW->where('train_station_id', $exitStop)
                                           ->where('arrival_planned', $arrival)->first();
        } else {
            $firstStop = $trip->stopoversNEW->where('trainStation.ibnr', $entryStop)
                                            ->where('departure_planned', $departure)->first();

            $lastStop = $trip->stopoversNEW->where('trainStation.ibnr', $exitStop)
                                           ->where('arrival_planned', $arrival)->first();
        }

        if (empty($firstStop) || empty($lastStop)) {
            throw new StationNotOnTripException();
        }

        $overlapping = TransportController::getOverlappingCheckIns(
            user:  auth()->user(),
            start: $firstStop->departure,
            end:   $lastStop->arrival
        );
        if ($overlapping->count() > 0) {
            throw new CheckInCollisionException($overlapping->first());
        }

        $distance = GeoController::calculateDistance(hafasTrip: $trip, origin: $firstStop, destination: $lastStop);

        $points = PointsCalculationController::calculatePoints(
            distanceInMeter: $distance,
            category:        $trip->category,
            departure:       $firstStop->departure,
            arrival:         $lastStop->arrival
        );

        $trainCheckin = TrainCheckin::create([
                                                 'status_id'   => $status->id,
                                                 'user_id'     => auth()->user()->id,
                                                 'trip_id'     => $trip->trip_id,
                                                 'origin'      => $firstStop->trainStation->ibnr,
                                                 'destination' => $lastStop->trainStation->ibnr,
                                                 'distance'    => $distance,
                                                 'points'      => $points,
                                                 'departure'   => $firstStop->departure_planned,
                                                 'arrival'     => $lastStop->arrival_planned
                                             ]);
        foreach ($trainCheckin->alsoOnThisConnection as $otherStatus) {
            if ($otherStatus?->user && $otherStatus->user->can('view', $status)) {
                $otherStatus->user->notify(new UserJoinedConnection(
                                               statusId:    $status->id,
                                               linename:    $trip->linename,
                                               origin:      $firstStop->name,
                                               destination: $lastStop->name
                                           ));
            }
        }

        return [
            'status'               => new StatusResource($status),
            'alsoOnThisConnection' => StatusResource::collection($trainCheckin->alsoOnThisConnection)
        ];
    }
}
