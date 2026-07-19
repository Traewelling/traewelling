<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\BackfillRouteSegments as BackfillRouteSegmentsJob;
use App\Models\Trip;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class BackfillRouteSegments extends Command
{
    protected $signature = 'trwl:backfill-route-segments
        {--from= : Only trips departing on/after this date (Y-m-d)}
        {--to= : Only trips departing on/before this date (Y-m-d)}
        {--limit= : Max number of trips to queue in this run}';

    protected $description = 'Queue force-rerouting jobs for (historical) trips so every leg gets a polyline';

    public function handle(): int
    {
        $from = $this->option('from') ? CarbonImmutable::parse($this->option('from'))->startOfDay() : null;
        $to = $this->option('to') ? CarbonImmutable::parse($this->option('to'))->endOfDay() : null;
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($from === null && $to === null && !$this->confirm('No date range given, this queues ALL trips. Continue?', false)) {
            return self::SUCCESS;
        }

        $query = Trip::query()
            ->when($from, fn ($query) => $query->where('departure', '>=', $from))
            ->when($to, fn ($query) => $query->where('departure', '<=', $to));

        $this->info(sprintf(
            'Queuing backfill jobs for trips departing %s - %s%s...',
            $from?->toDateString() ?? 'beginning',
            $to?->toDateString() ?? 'now',
            $limit !== null ? " (limit {$limit})" : ''
        ));

        $queued = 0;
        $query->orderBy('id')->chunkById(1000, function ($trips) use (&$queued, $limit): bool {
            foreach ($trips as $trip) {
                BackfillRouteSegmentsJob::dispatch($trip->id);
                $queued++;
                if ($limit !== null && $queued >= $limit) {
                    return false;
                }
            }

            return true;
        });

        $this->info("Queued {$queued} backfill jobs.");

        return self::SUCCESS;
    }
}
