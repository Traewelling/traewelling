<?php

namespace App\Jobs;

use App\Http\Controllers\ReRoutingController;
use App\Models\Trip;
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

    private Trip $trip;
    private ReRoutingController $reRoutingController;

    public function __construct(Trip $trip, ?ReRoutingController $reRoutingController = null) {
        $this->trip = $trip;
        $this->reRoutingController = $reRoutingController ?? app(ReRoutingController::class);
    }

    public function handle(): void {
        if (!$this->trip->category->onRails()) {
            Log::debug('RefreshPolyline Job skipped: Trip is not on rails', ['trip_id' => $this->trip->id, 'category' => $this->trip->category]);
            return;
        }
        Log::debug('RefreshPolyline Job started', ['trip_id' => $this->trip->id]);
        $this->reRoutingController->rerouteStops($this->trip);
    }
}
