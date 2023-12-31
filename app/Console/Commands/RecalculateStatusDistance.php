<?php

namespace App\Console\Commands;

use App\Exceptions\DistanceDeviationException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Status;
use Illuminate\Console\Command;

class RecalculateStatusDistance extends Command
{
    protected $signature   = 'trwl:recalculateStatusDistance {id*}';
    protected $description = 'Recalculate distance for status id';

    public function handle(): void {
        $ids      = $this->arguments()['id'];
        $statuses = Status::whereIn('id', $ids)->get();
        $this->info(sprintf('Found %d of %d statuses', count($ids), count($statuses)));
        $this->newLine(3);
        foreach ($statuses as $status) {
            try {
                $oldDistance = $status->checkin->distance;
                TrainCheckinController::refreshDistanceAndPoints(status: $status, resetPolyline: true);
                $this->info(sprintf('#%d: %d -> %d', $status->id, $oldDistance, $status->checkin->distance));
            } catch (DistanceDeviationException) {
                $this->error(sprintf('#%d: DistanceDeviationException occurred', $status->id));
            }
            $this->newLine();
        }
    }
}
