<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enum\StatusVisibility;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HideStatus extends Command
{
    protected $signature = 'trwl:hideStatus';

    protected $description = 'Hide older statuses based on planned arrival time.';

    private const int CHUNK_SIZE = 500;

    public function handle(): int
    {
        $users = User::whereNotNull('privacy_hide_days')
            ->where('privacy_hide_days', '>=', 0)
            ->get(['id', 'username', 'privacy_hide_days']);

        if ($users->isEmpty()) {
            $this->info('No users with privacy_hide_days configured.');

            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            $hideDays = (int) $user->privacy_hide_days;
            $cutoff = now()->subDays($hideDays);

            $this->info(sprintf(
                'Hiding statuses for %s (cutoff: %s, days: %d)',
                $user->username,
                $cutoff->toDateTimeString(),
                $hideDays,
            ));

            $totalRows = 0;

            DB::table('statuses as s')
                ->select('s.id')
                ->join('train_checkins as tc', 'tc.status_id', '=', 's.id')
                ->join('train_stopovers as dso', 'dso.id', '=', 'tc.destination_stopover_id')
                ->where('s.user_id', $user->id)
                ->where('s.visibility', '!=', StatusVisibility::PRIVATE->value)
                ->whereNotNull('dso.arrival_planned')
                ->where('dso.arrival_planned', '<', $cutoff)
                ->orderBy('s.id')
                ->chunk(self::CHUNK_SIZE, function ($rows) use (&$totalRows): void {
                    DB::table('statuses')
                        ->whereIn('id', collect($rows)->pluck('id')->all())
                        ->update([
                            'visibility' => StatusVisibility::PRIVATE->value,
                            'updated_at' => now(),
                        ]);
                    $totalRows += count($rows);
                });

            $this->info("Hid {$totalRows} statuses");
        }

        return Command::SUCCESS;
    }
}
