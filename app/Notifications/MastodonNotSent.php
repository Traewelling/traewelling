<?php

namespace App\Notifications;

use App\Helpers\Lang;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MastodonNotSent extends Notification implements BaseNotification
{
    use Queueable;

    public ?int $httpResponseCode;

    public Status $status;

    public function __construct(?int $httpResponseCode, Status $status)
    {
        $this->httpResponseCode = $httpResponseCode;
        $this->status = $status;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'httpResponseCode' => $this->httpResponseCode,
            'status' => $this->status->only(['id', 'uuid']),
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.socialNotShared.lead', [
            'platform' => 'Mastodon',
        ], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return Lang::trans('notifications.socialNotShared.mastodon.' . $data['httpResponseCode'], [], $locale);
    }

    public static function getLink(array $data): ?string
    {
        return isset($data['status']['uuid'])
            ? route('status', ['uuid' => $data['status']['uuid']])
            : url('/status/' . $data['status']['id']);
    }
}
