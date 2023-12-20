<?php

namespace App\Console\Commands;

use App\Models\TrainCheckin;
use Illuminate\Console\Command;

/**
 * @deprecated Just created and marked as deprecated, because it is only needed for migrating old checkins.
 *             Can be deleted after migration.
 */
class MigrateStopovers extends Command
{

    protected $signature   = 'app:migrate-stopovers';
    protected $description = 'Calculate missing stopover relations for train checkins. Currently only needed for migrating old checkins.';

    public function handle(): int {
        while (TrainCheckin::whereNull('origin_stopover_id')->orWhereNull('destination_stopover_id')->count() > 0) {
            TrainCheckin::with(['HafasTrip.stopovers', 'originStation', 'destinationStation'])
                        ->whereNull('origin_stopover_id')
                        ->orWhereNull('destination_stopover_id')
                        ->limit(100)
                        ->each(function(TrainCheckin $checkin) {
                            $originStopover = $checkin->HafasTrip->stopovers->where('train_station_id', $checkin->originStation->id)
                                                                            ->where('departure_planned', $checkin->departure)
                                                                            ->first();

                            $destinationStopover = $checkin->HafasTrip->stopovers->where('train_station_id', $checkin->destinationStation->id)
                                                                                 ->where('arrival_planned', $checkin->arrival)
                                                                                 ->first();

                            if ($originStopover === null) {
                                $this->error("Could not find origin stopover for checkin {$checkin->id}");
                                return;
                            }
                            if ($destinationStopover === null) {
                                $this->error("Could not find destination stopover for checkin {$checkin->id}");
                                return;
                            }

                            $checkin->update([
                                                 'origin_stopover_id'      => $originStopover->id,
                                                 'destination_stopover_id' => $destinationStopover->id,
                                             ]);

                            $this->info("Migrated stopover ids for checkin {$checkin->id}");
                        });
        }
        return Command::SUCCESS;
    }
}
