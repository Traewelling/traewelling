<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Helpers\Lang;
use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebhookDisabled extends Notification implements BaseNotification
{
    use Queueable;

    public function __construct(private readonly Webhook $webhook) {}

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'webhook_id' => $this->webhook->id,
            'client_name' => $this->webhook->client->name,
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.webhookDisabled.lead', [], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return Lang::trans('notifications.webhookDisabled.notice', [
            'client' => '<b>' . e($data['client_name']) . '</b>',
        ], $locale);
    }

    public static function getLink(array $data): ?string
    {
        return url('/settings/security');
    }
}
