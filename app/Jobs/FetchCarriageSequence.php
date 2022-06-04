<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\Transport\CarriageSequenceController;
use App\Models\TrainStopover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchCarriageSequence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private TrainStopover $stopover;

    public function __construct(TrainStopover $stopover) {
        $this->stopover = $stopover;
    }

    public function handle(): void {
        CarriageSequenceController::fetchSequence($this->stopover);
    }
}
