<?php

namespace App\Console\Commands;

use App\Enum\CacheKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SchedulerCanary extends Command
{
    protected $signature = 'app:scheduler-canary';

    protected $description = 'Set a cache item to the current time stamp. Referred to in Health Controller';

    public function handle()
    {
        Cache::put(CacheKey::SchedulerCanary, time());
    }
}
