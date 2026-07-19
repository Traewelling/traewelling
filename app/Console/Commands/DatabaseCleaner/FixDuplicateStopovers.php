<?php

declare(strict_types=1);

namespace App\Console\Commands\DatabaseCleaner;

use App\Services\Cleanup\DuplicateStopoverService;
use Illuminate\Console\Command;

class FixDuplicateStopovers extends Command
{
    protected $signature = 'trwl:fix-duplicate-stopovers
        {--fix : Apply changes. Without this flag the command only reports (dry-run).}
        {--distance=200 : Max distance in meters between the two stations to treat them as the same stop}
        {--limit= : Max number of duplicate stopovers to process}';

    protected $description = 'Remove duplicate same-stop stopovers added by real-time updates and repoint their check-ins';

    public function handle(DuplicateStopoverService $service): int
    {
        $maxMeters = (int) $this->option('distance');
        $apply = (bool) $this->option('fix');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $this->info(sprintf(
            '%s duplicate stopovers within %d m of an earlier twin...',
            $apply ? 'Fixing' : '[dry-run] Scanning for',
            $maxMeters,
        ));

        $found = 0;
        $removed = 0;
        $skipped = 0;
        $checkins = 0;
        $trips = [];

        foreach ($service->findDuplicates($maxMeters, $limit) as $pair) {
            $found++;
            $references = $service->checkinReferenceCount($pair->duplicate);
            $conflict = false;

            if ($apply) {
                $repointed = $service->fix($pair);
                if ($repointed === null) {
                    $skipped++;
                    $conflict = true;
                } else {
                    $removed++;
                    $checkins += $repointed;
                    $trips[$pair->duplicate->trip_id] = true;
                }
            } else {
                $checkins += $references;
                $trips[$pair->duplicate->trip_id] = true;
            }

            $this->line(sprintf(
                '  trip %s: %s stopover %d (%s) -> keep %d (%s)%s',
                $pair->duplicate->trip_id,
                $conflict ? 'SKIP (check-in conflict)' : 'drop',
                $pair->duplicate->id,
                $pair->duplicate->station?->name ?? '?',
                $pair->keeper->id,
                $pair->keeper->station?->name ?? '?',
                (!$conflict && $references > 0) ? " [{$references} check-in ref(s)]" : '',
            ));
        }

        if ($apply && $trips !== []) {
            $service->refreshAffectedTrips(array_keys($trips));
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d duplicate stopover(s) across %d trip(s); %d check-in reference(s) %s%s.',
            $apply ? 'Removed' : 'Would remove',
            $apply ? $removed : $found,
            count($trips),
            $checkins,
            $apply ? 'repointed' : 'would be repointed',
            $apply && $skipped > 0 ? "; {$skipped} skipped (check-in conflict)" : '',
        ));

        if (!$apply && $found > 0) {
            $this->comment('Re-run with --fix to apply.');
        }

        return self::SUCCESS;
    }
}
