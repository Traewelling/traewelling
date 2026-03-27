<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\PointReason;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Status;
use App\Models\Stopover;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * @deprecated: Functionality should be moved to the CheckinService
 */
class TrainCheckinController extends Controller
{
    public static function changeDestination(
        Checkin $checkin,
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

    public static function refreshDistanceAndPoints(Status $status, bool $resetPolyline = false): void
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
        $payload = [
            'distance' => $distance,
            'points' => $pointsResource->points,
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

    public static function calculateCheckinDuration(Checkin $checkin, bool $update = true): int
    {
        $departure = $checkin->manual_departure ?? $checkin->originStopover->departure ?? $checkin->departure;
        $arrival = $checkin->manual_arrival ?? $checkin->destinationStopover->arrival ?? $checkin->arrival;
        $duration = $departure->diffInMinutes($arrival);

        if ($duration < 0) {
            // diffInMinutes() returns negative minutes, if the arrival is before the departure.
            $duration = 0;
        }

        // don't use eloquent here, because it would trigger the observer (and this function) again
        if ($update) {
            DB::table('train_checkins')->where('id', $checkin->id)->update(['duration' => $duration]);
        }

        return $duration;
    }
}
