<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Status;
use Illuminate\Console\Command;

class RecalculateStatusDistance extends Command
{
    protected $signature   = 'trwl:recalculateStatusDistance {id*} {--polyline}';
    protected $description = 'Recalculate distance for status id';

    public function handle(): void{
        $ids = $this->arguments()['id'];
        $statuses = Status::whereIn('id', $ids)->get();
        $this->info(sprintf('Found %d of %d statuses', count($ids), count($statuses)));
        $this->newLine(3);
        foreach($statuses as $status) {
            $oldDistance = $status->trainCheckin->distance;
            TrainCheckinController::refreshDistanceAndPoints(status: $status, resetPolyline: true);
            $this->info(sprintf('#%d: %d -> %d', $status->id, $oldDistance, $status->trainCheckin->distance));
            $this->newLine();
        }
    }
}
