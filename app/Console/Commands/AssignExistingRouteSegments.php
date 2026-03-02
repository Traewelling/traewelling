<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enum\HafasTravelType;
use App\Models\RouteSegment;
use App\Models\Stopover;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Command\Command as CommandAlias;

/**
 * @deprecated One-time migration command to backfill route_segment_id on old stopovers.
 *             Can be deleted after migration is complete.
 */
class AssignExistingRouteSegments extends Command
{
    protected $signature = 'trwl:assign-existing-route-segments
                            {--dry-run : Show what would be assigned without saving}
                            {--chunk=100 : Number of trips to process at once}
                            {--limit= : Stop after processing this many trips (useful for testing)}
                            {--from= : Only process trips created on or after this date (YYYY-MM-DD)}
                            {--until= : Only process trips created on or before this date (YYYY-MM-DD)}';

    protected $description = 'Assign existing RouteSegments to old stopovers that are missing one, without fetching new data.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $from = $this->option('from') ? Carbon::parse($this->option('from'))->startOfDay() : null;
        $until = $this->option('until') ? Carbon::parse($this->option('until'))->endOfDay() : null;

        if ($dryRun) {
            $this->warn('Dry-run mode: no changes will be saved.');
        }
        if ($from) {
            $this->line("Filtering: created_at >= {$from->toDateString()}");
        }
        if ($until) {
            $this->line("Filtering: created_at <= {$until->toDateString()}");
        }

        // Only process trips whose category has a supported ORR profile
        $supportedCategories = $this->getSupportedCategories();
        if (empty($supportedCategories)) {
            $this->error('No supported trip categories found.');

            return CommandAlias::FAILURE;
        }

        // Filter only by category as a whereHas EXISTS subquery against 100M+ stopovers kills the
        // DB connection. Since ~92% of stopovers have no segment, it would filter almost nothing
        // anyway. Trips that have all segments assigned are skipped cheaply in the inner loop.
        $query = Trip::whereIn('category', $supportedCategories)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($until, fn ($q) => $q->where('created_at', '<=', $until));

        if ($limit !== null) {
            $this->warn("Processing limited to {$limit} trips (skipping full count).");
            $total = $limit;
        } else {
            $total = $query->count();
            $this->info("Found {$total} qualifying trips.");
        }

        $assigned = 0;
        $skipped = 0;
        $processed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($chunkSize, function ($trips) use ($dryRun, $limit, &$assigned, &$skipped, &$processed, $bar): bool {
            foreach ($trips as $trip) {
                if ($limit !== null && $processed >= $limit) {
                    return false; // stop chunking
                }
                $pathType = $trip->category->getORRProfile();
                if ($pathType === null) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                /** @var Collection<int, Stopover> $stops */
                $stops = $trip->stopovers()->get();

                foreach ($stops as $key => $stop) {
                    $previousStop = $stops[$key - 1] ?? null;
                    if ($previousStop === null) {
                        continue; // first stop, no leg before it
                    }

                    // The segment for leg A→B is stored on the START stopover (A)
                    if ($previousStop->route_segment_id !== null) {
                        continue; // this leg already has a segment
                    }

                    $startTime = $previousStop->departure ?? $previousStop->arrival;
                    $endTime = $stop->arrival ?? $stop->departure;

                    if ($startTime === null || $endTime === null || !$startTime->isValid() || !$endTime->isValid()) {
                        continue; // cannot determine duration without valid timestamps
                    }

                    $duration = (int) round($startTime->diffInSeconds($endTime));
                    if ($duration <= 0) {
                        continue;
                    }

                    $segment = RouteSegment::where('from_station_id', $previousStop->train_station_id)
                        ->where('to_station_id', $stop->train_station_id)
                        ->where('path_type', $pathType)
                        ->whereBetween('duration', [(int) ($duration * 0.7), (int) ($duration * 1.3)])
                        ->first();

                    if ($segment === null) {
                        continue; // ¯\_(´•︵•)_/¯`
                    }

                    if (!$dryRun) {
                        $previousStop->update(['route_segment_id' => $segment->id]);
                    }

                    $assigned++;

                    $this->line(
                        sprintf(
                            "\n  Trip %s: assigned segment %s to stopover %d (%d→%d, %ds)",
                            $trip->trip_id,
                            $segment->id,
                            $previousStop->id,
                            $previousStop->train_station_id,
                            $stop->train_station_id,
                            $duration,
                        ),
                        verbosity: 'vv',
                    );
                }

                $bar->advance();
                $processed++;
            }

            return true;
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Assigned: {$assigned} route segments.");
        if ($skipped > 0) {
            $this->line("Skipped:  {$skipped} trips (no ORR profile for category).");
        }

        return CommandAlias::SUCCESS;
    }

    /**
     * Returns the string values of all HafasTravelType cases that have a non-null ORR profile.
     *
     * @return string[]
     */
    private function getSupportedCategories(): array
    {
        return collect(HafasTravelType::cases())
            ->filter(fn (HafasTravelType $case) => $case->getORRProfile() !== null)
            ->map(fn (HafasTravelType $case) => $case->value)
            ->values()
            ->all();
    }
}
