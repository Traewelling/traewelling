<?php

namespace App\Jobs;

use App\Http\Controllers\ReRoutingController;
use App\Models\Trip;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RefreshPolyline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    private const string REFRESH_POLYLINE_COOLDOWN_CACHE_KEY = 'refresh_polyline_cooldown';
    private Trip $trip;
    private ReRoutingController $reRoutingController;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(Trip $trip, ?ReRoutingController $reRoutingController = null) {
        $this->trip = $trip;
        $this->reRoutingController = $reRoutingController ?? app(ReRoutingController::class);
    }

    public function handle(): void {
        if (Cache::has(self::REFRESH_POLYLINE_COOLDOWN_CACHE_KEY)) {
            Log::debug('RefreshPolyline Job skipped: Cooldown active', ['trip_id' => $this->trip->id]);
            throw new \Exception('Pausing RefreshPolyline Job due to cooldown');
        }

        if (!$this->trip->category->onRails()) {
            Log::debug('RefreshPolyline Job skipped: Trip is not on rails', ['trip_id' => $this->trip->id, 'category' => $this->trip->category]);
            return;
        }
        Log::debug('RefreshPolyline Job started', ['trip_id' => $this->trip->id]);
        $percentage = $this->reRoutingController->rerouteStops($this->trip);

        if ($percentage > 10) {
            Cache::set(self::REFRESH_POLYLINE_COOLDOWN_CACHE_KEY, true, now()->addMinutes());
            throw new \Exception('Pausing RefreshPolyline Job due to cooldown');
        }
    }
}
