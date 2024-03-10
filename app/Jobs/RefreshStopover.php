<?php

namespace App\Jobs;

use App\Http\Controllers\HafasController;
use App\Models\Stopover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class RefreshStopover implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected Stopover $stopover;

    public function __construct(Stopover $stopover) {
        $this->stopover = $stopover;
    }

    public function handle(): void {
        HafasController::refreshStopover($this->stopover);
    }
}
