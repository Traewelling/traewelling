<?php

namespace App\Console\Commands;

use App\Models\TrainCheckin;
use Illuminate\Console\Command;

/**
 * @deprecated Just created and marked as deprecated, because it is only needed for migrating old checkins.
 *             Can be deleted after migration.
 */
class CalculateMissingDuration extends Command
{

    protected $signature   = 'trwl:calculate-missing-duration';
    protected $description = 'Calculate missing duration for train checkins. Currently only needed for migrating old checkins.';

    public function handle(): int {
        TrainCheckin::with(['HafasTrip.stopovers', 'originStation', 'destinationStation'])
                    ->whereNull('duration')
                    ->chunk(100, function($checkins) {
                        foreach ($checkins as $checkin) {
                            $duration = $checkin->duration;
                            $this->info("Duration for checkin {$checkin->id} is {$duration}");
                        }
                    });
        return Command::SUCCESS;
    }
}
