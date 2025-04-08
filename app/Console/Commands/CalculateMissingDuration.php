<?php

namespace App\Console\Commands;

use App\Models\Checkin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

/**
 * @deprecated Just created and marked as deprecated, because it is only needed for migrating old checkins.
 *             Can be deleted after migration.
 */
class CalculateMissingDuration extends Command
{
    protected $signature   = 'trwl:calculate-missing-duration';
    protected $description = 'Calculate missing duration for train checkins. Currently only needed for migrating old checkins.';

    public function handle(): int {
        while (true) {
            Checkin::with(['HafasTrip.stopovers', 'originStation', 'destinationStation'])
                   ->whereNull('duration')
                   ->limit(250)
                   ->each(function($checkin) {
                       // foreach ($checkins as $checkin) {
                       $duration = $checkin->duration;
                       $this->info("Duration for checkin {$checkin->id} is {$duration}");
                       //}
                   });
        }
        return CommandAlias::SUCCESS;
    }
}
