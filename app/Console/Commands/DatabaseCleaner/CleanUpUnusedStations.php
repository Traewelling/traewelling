<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanUpUnusedStations extends Command
{
    protected $signature = 'app:clean-db:stations {--iterations=10 : Max delete iterations of 1000 rows each; 0 = unlimited}';

    protected $description = 'Delete stations that are not referenced by any stopover, trip, event, event suggestion, identifier, route segment or user home station';

    public function handle(): int
    {
        $maxIterations = (int) $this->option('iterations');
        $affectedRows = 0;
        $iterations = 0;
        $lastId = null;
        $this->info('Deleting unused stations...');
        $this->output->writeln('');
        do {
            $query = DB::table('train_stations');
            if ($lastId !== null) {
                $query->where('id', '<', $lastId);
            }
            $ids = $this->unusedStations($query)
                ->orderByDesc('id')
                ->limit(1000)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $lastId = $ids->last();
            $result = $this->unusedStations(DB::table('train_stations')->whereIn('id', $ids))->delete();

            if ($result > 0) {
                $affectedRows += $result;
                $this->output->write('.');
            }
            $iterations++;
        } while ($maxIterations === 0 || $iterations < $maxIterations);
        $this->output->writeln('');

        $this->info($affectedRows . ' unused stations deleted.');
        Log::debug($affectedRows . ' unused stations deleted.');

        return 0;
    }

    private function unusedStations(Builder $query): Builder
    {
        return $query
            // never delete rows a user might be creating right now (also skips NULL created_at)
            ->where('created_at', '<', now()->subMinutes(10))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('train_stopovers')
                ->whereColumn('train_stopovers.train_station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('hafas_trips')
                ->whereColumn('hafas_trips.origin_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('hafas_trips')
                ->whereColumn('hafas_trips.destination_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('events')
                ->whereColumn('events.station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('event_suggestions')
                ->whereColumn('event_suggestions.station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('station_identifiers')
                ->whereColumn('station_identifiers.station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('route_segments')
                ->whereColumn('route_segments.from_station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('route_segments')
                ->whereColumn('route_segments.to_station_id', 'train_stations.id'))
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('users')
                ->whereColumn('users.home_id', 'train_stations.id'));
    }
}
