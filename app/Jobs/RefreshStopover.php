<?php

namespace App\Jobs;

use App\Http\Controllers\HafasController;
use App\Models\TrainStopover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RefreshStopover implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected TrainStopover $stopover;

    public function __construct(TrainStopover $stopover) {
        $this->stopover = $stopover;
    }

    public function handle(): void {
        HafasController::refreshStopover($this->stopover);
    }
}
