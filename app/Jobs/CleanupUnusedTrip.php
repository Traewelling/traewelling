<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupUnusedTrip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        private readonly string $tripId,
        private readonly ?int $deletedCheckinId = null,
    ) {
        $this->onQueue(Queue::BACKGROUND->value);
    }

    public function handle(): void
    {
        // the deleted checkin is excluded explicitly: it is cascade-deleted with its status on
        // database level, but on connections without this FK it may still be readable here
        $checkinsExist = Checkin::where('trip_id', $this->tripId)
            ->when($this->deletedCheckinId !== null, fn ($query) => $query->whereKeyNot($this->deletedCheckinId))
            ->exists();
        if ($checkinsExist) {
            return;
        }

        $trip = Trip::where('trip_id', $this->tripId)->first();
        if ($trip === null) {
            return;
        }

        $polylineId = $trip->polyline_id;

        try {
            // stopovers and carriage sequences are deleted via cascade,
            // continuation_trip_id references are set to null via FK
            $trip->delete();
        } catch (QueryException) {
            // a new checkin referenced the trip in the meantime, the restrict FK protects it
            Log::debug('CleanupUnusedTrip: trip is in use again, skipping', ['trip_id' => $this->tripId]);

            return;
        }

        Log::debug('CleanupUnusedTrip: deleted unused trip', ['trip_id' => $this->tripId]);

        $this->deleteUnusedPolylines($polylineId);
    }

    /**
     * Deletes the polyline of the deleted trip if it is not referenced anymore and walks up the
     * parent chain, since a parent polyline may have become unused as well.
     */
    private function deleteUnusedPolylines(?int $polylineId): void
    {
        while ($polylineId !== null) {
            $polyline = PolyLine::find($polylineId);
            if (
                $polyline === null
                || Trip::where('polyline_id', $polyline->id)->exists()
                || PolyLine::where('parent_id', $polyline->id)->exists()
            ) {
                return;
            }

            $parentId = $polyline->parent_id;

            try {
                $polyline->delete();
            } catch (QueryException) {
                // referenced again in the meantime, the restrict FKs protect it
                return;
            }

            Log::debug('CleanupUnusedTrip: deleted unused polyline', ['polyline_id' => $polyline->id]);
            $polylineId = $parentId;
        }
    }
}
