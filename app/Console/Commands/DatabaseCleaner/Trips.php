<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Trips extends Command
{
    protected $signature   = 'app:clean-db:trips';
    protected $description = 'Find and delete unused and old Trips from database';

    public function handle(): int {
        $affectedRows = 0;
        $this->info('Deleting unused Trips...');
        $this->output->writeln('');
        do {
            $result = DB::table('hafas_trips')
                        ->leftJoin('train_checkins', 'hafas_trips.trip_id', '=', 'train_checkins.trip_id')
                        ->whereNull('train_checkins.trip_id')
                        ->limit(1000)
                        ->delete();

            if ($result > 0) {
                $affectedRows += $result;
                $this->output->write('.');
            }
        } while ($result > 0);
        $this->output->writeln('');

        $this->info($affectedRows . ' unused Trips deleted.');
        Log::debug($affectedRows . ' unused Trips deleted.');
        return 0;
    }
}
