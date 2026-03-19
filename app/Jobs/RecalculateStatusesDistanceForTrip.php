<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Checkin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecalculateStatusesDistanceForTrip implements ShouldQueue
{
    use Queueable;

    private string $tripId;

    public function __construct(string $tripId)
    {
        $this->tripId = $tripId;
        $this->onQueue('normal');
    }

    public function handle(): void
    {
        $checkinsToRecalc = Checkin::with(['status', 'trip', 'originStopover', 'destinationStopover'])
            ->where('trip_id', $this->tripId)
            ->get();

        foreach ($checkinsToRecalc as $checkin) {
            Log::debug('Recalculating points and distance', [
                'checkin_id' => $checkin->id,
                'trip_id' => $this->tripId,
            ]);

            try {
                DB::beginTransaction();
                TrainCheckinController::refreshDistanceAndPoints($checkin->status);
                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                Log::error('RecalculateStatusesDistanceForTrip: unexpected error', [
                    'checkin_id' => $checkin->id,
                    'trip_id' => $this->tripId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
