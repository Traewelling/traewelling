<?php

declare(strict_types=1);

namespace App\Jobs\AdminNotification;

use App\Enum\Queue;
use App\Services\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteAdminNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $type,
        private readonly ?int $telegramId,
        private readonly ?string $matrixEventId,
    ) {
        $this->onQueue(Queue::REALTIME->value);
    }

    public function handle(): void
    {
        match ($this->type) {
            'events' => AdminNotificationService::deleteEventNotification($this->telegramId, $this->matrixEventId),
            'reports' => AdminNotificationService::deleteReportNotification($this->telegramId, $this->matrixEventId),
        };
    }
}
