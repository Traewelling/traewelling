<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\MailChange;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanMailChanges implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(Queue::BACKGROUND->value);
    }

    public function handle(): void
    {
        Log::info('Starting to clean up old mail changes.');
        $done = MailChange::where('created_at', '<', now()->subDays(30))->delete();
        Log::info("Finished cleaning up old mail changes. Deleted $done entries.");
    }
}
