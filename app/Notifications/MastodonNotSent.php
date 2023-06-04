<?php

namespace App\Notifications;

use App\Models\Status;
use Illuminate\Bus\Queueable;

class MastodonNotSent extends BaseNotification
{
    use Queueable;

    public ?int   $httpResponseCode;
    public Status $status;

    public function __construct(?int $httpResponseCode, Status $status) {
        $this->httpResponseCode = $httpResponseCode;
        $this->status           = $status;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'httpResponseCode' => $this->httpResponseCode,
            'status'           => $this->status->only(['id']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.socialNotShared.lead', [
            'platform' => 'Mastodon',
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.socialNotShared.mastodon.' . $data['httpResponseCode']);
    }

    public static function getLink(array $data): ?string {
        return route('statuses.get', [
            'id' => $data['status']['id'],
        ]);
    }
}
