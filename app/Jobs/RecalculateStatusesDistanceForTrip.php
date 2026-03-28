<?php

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\Checkin;
use App\Services\Checkin\CheckinService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecalculateStatusesDistanceForTrip implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private string $tripId;

    public function __construct(string $tripId)
    {
        $this->tripId = $tripId;
        $this->onQueue(Queue::LOW->value);
    }

    public function uniqueId(): string
    {
        return $this->tripId;
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
                app(CheckinService::class)->refreshDistanceAndPoints($checkin->status);
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
