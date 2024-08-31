<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Helpers\CacheKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RefreshPrometheusCache extends Command
{
    protected $signature   = 'app:clean-db:prometheus';
    protected $description = 'Delete prometheus cache to properly recalculate the metrics';

    public function handle(): int {
        $this->info('Deleting prometheus cache...');
        Cache::forget(CacheKey::STATUS_CREATED);
        Cache::forget(CacheKey::STATUS_DELETED);
        Cache::forget(CacheKey::USER_CREATED);
        Cache::forget(CacheKey::USER_DELETED);

        return 0;
    }
}
