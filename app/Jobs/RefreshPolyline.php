<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\BrouterController;
use App\Models\HafasTrip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RefreshPolyline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected HafasTrip $hafasTrip;

    public function __construct(HafasTrip $hafasTrip) {
        $this->hafasTrip = $hafasTrip;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void {
        BrouterController::reroutePolyline($this->hafasTrip);
    }
}
