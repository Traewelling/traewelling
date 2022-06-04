<?php

namespace App\Console\Commands;

use App\Models\ApiLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanUpDatabase extends Command
{
    protected $signature   = 'trwl:cleanUpDatabase';
    protected $description = 'Delete unused and old database data';

    public function handle(): int {
        $affectedRows = ApiLog::where('created_at', '<', Carbon::now()->subQuarter()->toIso8601String())->delete();
        Log::debug('[DatabaseCleanup] ' . $affectedRows . ' rows deleted.');
        return 0;
    }
}
