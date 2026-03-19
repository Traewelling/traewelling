<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Notifications\StatusLiked;
use App\Notifications\UserJoinedConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteStatusNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $statusId)
    {
        $this->onQueue(Queue::LOW->value);
    }

    public function handle(): void
    {
        DatabaseNotification::where('type', UserJoinedConnection::class)
            ->where('data->status->id', $this->statusId)
            ->delete();

        DatabaseNotification::where('type', StatusLiked::class)
            ->where('data->status->id', $this->statusId)
            ->delete();
    }
}
