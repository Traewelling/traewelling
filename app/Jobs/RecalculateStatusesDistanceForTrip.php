<?php

namespace App\Jobs;

use App\Exceptions\DistanceDeviationException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Checkin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateStatusesDistanceForTrip implements ShouldQueue
{
    use Queueable;

    public $queue = 'normal';

    private string $tripId;

    public function __construct(string $tripId)
    {
        $this->tripId = $tripId;
    }

    public function handle(): void
    {
        $checkinsToRecalc = Checkin::with(['status'])->where('trip_id', $this->tripId)->get();
        foreach ($checkinsToRecalc as $checkin) {
            Log::debug('Recalculating points and distance for Checkin #' . $checkin->checkin_id, [
                'Trip#' . $this->tripId,
            ]);
            try {
                DB::beginTransaction();
                TrainCheckinController::refreshDistanceAndPoints($checkin->status);
                DB::commit();
            } catch (DistanceDeviationException) {
                Log::info('Distance Deviation detected. Reverting changes.', [
                    'RecalculateStatusesDistanceForTrip', 'Trip#' . $this->tripId, 'Checkin#' . $checkin->checkin_id,
                ]);
                DB::rollBack();
            }
        }
    }
}
