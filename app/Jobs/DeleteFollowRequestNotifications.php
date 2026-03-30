<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Notifications\FollowRequestIssued;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteFollowRequestNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $followRequestId,
        private readonly int $notifiableId,
    ) {
        $this->onQueue(Queue::LOW->value);
    }

    public function handle(): void
    {
        DatabaseNotification::where('type', FollowRequestIssued::class)
            ->where('notifiable_id', $this->notifiableId)
            ->where('data->followRequest->id', $this->followRequestId)
            ->delete();
    }
}
