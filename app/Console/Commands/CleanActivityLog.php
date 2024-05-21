<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CleanActivityLog extends Command
{
    protected $signature   = 'app:clean-activity-log';
    protected $description = 'Remove all activity log entries for users older than 14 days - except admin activities!';

    public function handle(): void {
        $userIdsFromAdmins = Role::findByName('admin')->users()->pluck('id')->toArray();
        $this->info('Deleting activity log entries for users older than 14 days - except admin (ids ' . implode(', ', $userIdsFromAdmins) . ') activities...');

        $rows = DB::table('activity_log')
                  ->where('causer_type', User::class)
                  ->whereNotIn('causer_id', $userIdsFromAdmins)
                  ->where('created_at', '<', now()->subDays(14))
                  ->delete();

        $this->info('Deleted ' . $rows . ' activity log entries');
    }
}
