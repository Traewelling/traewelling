<?php

namespace App\Jobs;

use App\Enum\Queue;
use App\Helpers\CacheKey;
use App\Http\Controllers\ReRoutingController;
use App\Models\Trip;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
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

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(Trip $trip, ?ReRoutingController $reRoutingController = null)
    {
        $this->trip = $trip;
        $this->reRoutingController = $reRoutingController ?? app(ReRoutingController::class);
        $this->onQueue(Queue::NORMAL);
    }

    public static function dispatch(Trip $trip, ?ReRoutingController $reRoutingController = null): PendingDispatch
    {
        Cache::set(CacheKey::getReroutePolylineJobKey($trip->id), true, now()->addMinutes(5));

        return static::newPendingDispatch(new static($trip, $reRoutingController));
    }

    public function handle(): void
    {
        if (app()->environment('testing')) {
            Log::info('RefreshPolyline Job skipped: Testing environment', ['trip_id' => $this->trip->id]);

            return;
        }
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

        if ($percentage > config('trwl.distance_deviation.cooldown_error_percent')) {
            Log::warning('Too many route segments failed to refresh, activating cooldown', [
                'trip_id' => $this->trip->id,
                'failed_percentage' => $percentage,
                'cooldown_error_percent' => config('trwl.distance_deviation.cooldown_error_percent'),
            ]);
            Cache::set(self::REFRESH_POLYLINE_COOLDOWN_CACHE_KEY, true, now()->addSeconds(config('trwl.distance_deviation.cooldown_seconds')));
            throw new \Exception('Pausing RefreshPolyline Job due to cooldown');
        }

        Cache::forget(CacheKey::getReroutePolylineJobKey($this->trip->id));
    }
}
