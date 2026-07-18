<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Enum\StationIdentifierType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanUpUnusedMotisIdentifiers extends Command
{
    protected $signature = 'app:clean-db:motis-identifiers {--iterations=10 : Max delete iterations of 1000 rows each; 0 = unlimited}';

    protected $description = 'Delete motis station identifiers that are not referenced by any stopover or route segment';

    public function handle(): int
    {
        $maxIterations = (int) $this->option('iterations');
        $affectedRows = 0;
        $iterations = 0;
        $this->info('Deleting unused motis identifiers...');
        $this->output->writeln('');
        do {
            $result = DB::table('station_identifiers')
                ->where('type', StationIdentifierType::MOTIS->value)
                // never delete rows a user might be creating right now (also skips NULL created_at)
                ->where('created_at', '<', now()->subMinutes(10))
                ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                    ->from('train_stopovers')
                    ->whereColumn('train_stopovers.station_identifier_id', 'station_identifiers.id'))
                ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                    ->from('route_segments')
                    ->whereColumn('route_segments.from_identifier_id', 'station_identifiers.id'))
                ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                    ->from('route_segments')
                    ->whereColumn('route_segments.to_identifier_id', 'station_identifiers.id'))
                ->limit(1000)
                ->delete();

            if ($result > 0) {
                $affectedRows += $result;
                $this->output->write('.');
            }
            $iterations++;
        } while ($result > 0 && ($maxIterations === 0 || $iterations < $maxIterations));
        $this->output->writeln('');

        $this->info($affectedRows . ' unused motis identifiers deleted.');
        Log::debug($affectedRows . ' unused motis identifiers deleted.');

        return 0;
    }
}
