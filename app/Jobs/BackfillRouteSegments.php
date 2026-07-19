<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\Trip;
use App\Services\ReRoutingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

/**
 * Backfills route segments for a single (historical) trip.
 *
 * Unlike RefreshPolyline this deliberately runs without the protective guards (no cooldown, no
 * on-rails restriction) and reroutes in force mode: whatever BRouter returns is accepted and a
 * great-circle arc is stored when it returns nothing, so every leg ends up with a segment.
 * Imperfect results can be cleaned up manually afterwards.
 */
class BackfillRouteSegments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 600;

    public function __construct(private readonly int $tripId)
    {
        $this->onQueue(Queue::BACKGROUND->value);
    }

    public function handle(ReRoutingService $reRoutingService): void
    {
        $trip = Trip::find($this->tripId);
        if ($trip === null) {
            Log::info('BackfillRouteSegments: Trip no longer exists, skipping', ['trip_id' => $this->tripId]);

            return;
        }

        $reRoutingService->rerouteStops($trip, force: true);
    }
}
