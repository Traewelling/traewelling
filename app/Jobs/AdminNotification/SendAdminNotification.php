<?php

declare(strict_types=1);

namespace App\Jobs\AdminNotification;

use App\Enum\Queue;
use App\Services\AdminNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAdminNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $type,
        private readonly string $htmlMessage,
        private readonly Model $model,
    ) {
        $this->onQueue(Queue::REALTIME->value);
    }

    public function handle(): void
    {
        $result = match ($this->type) {
            'events' => AdminNotificationService::sendEventNotification($this->htmlMessage),
            'reports' => AdminNotificationService::sendReportNotification($this->htmlMessage),
        };

        if ($result->hasAny()) {
            $this->model->update([
                'telegram_notification_id' => $result->telegramId,
                'matrix_notification_id' => $result->matrixId,
            ]);
        }
    }
}
