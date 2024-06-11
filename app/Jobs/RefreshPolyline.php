<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\BrouterController;
use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RefreshPolyline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    private Trip $trip;

    public function __construct(Trip $trip) {
        $this->trip = $trip;
    }

    public function handle(): void {
        BrouterController::reroutePolyline($this->trip);
    }
}
