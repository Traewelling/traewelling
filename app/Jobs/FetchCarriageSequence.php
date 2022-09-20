<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\Transport\CarriageSequenceController;
use App\Models\TrainStopover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Traewelling\QueueMonitor\Traits\IsMonitored;

class FetchCarriageSequence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    private TrainStopover $stopover;

    public function __construct(TrainStopover $stopover) {
        $this->stopover = $stopover;
    }

    public function handle(): void {
        $this->queueData([
                             "stopover" => $this->stopover->id,
                             "for_trip" => $this->stopover->trip_id
                         ]);
        CarriageSequenceController::fetchSequence($this->stopover);
    }
}
