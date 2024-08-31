<?php

namespace App\Console\Commands\DatabaseCleaner;

use Illuminate\Console\Command;

class DatabaseCleaner extends Command
{
    protected $signature = 'app:clean-db';

    public function handle(): int {
        $this->call(FailedJobs::class);
        $this->call(Notifications::class);
        $this->call(PasswordResets::class);
        $this->call(Polylines::class);
        $this->call(PolylinesBrouter::class);
        $this->call(User::class);
        $this->call(TrustedUser::class);
        $this->call(Trips::class);
        $this->call(RefreshPrometheusCache::class);

        $this->call('queue-monitor:purge', ['--beforeDays' => 7]);
        $this->call('activitylog:clean');
        $this->call('cache:clear-database');
        return 0;
    }
}
