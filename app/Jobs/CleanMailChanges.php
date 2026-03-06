<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MailChange;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanMailChanges implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Log::info('Starting to clean up old mail changes.');
        $done = MailChange::whereCreatedAt('<', now()->subDays(30))->delete();
        Log::info("Finished cleaning up old mail changes. Deleted $done entries.");
    }
}
