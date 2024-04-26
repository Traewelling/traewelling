<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvalidMastodonServer extends Notification implements BaseNotification
{
    use Queueable;

    private string $domain;

    public function __construct(string $domain) {
        $this->domain = $domain;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return ['domain' => $this->domain,];
    }

    public static function getLead(array $data): string {
        return __('notifications.mastodon-server.lead');
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.mastodon-server.exception', ['domain' => $data['domain']]);
    }

    public static function getLink(array $data): ?string {
        return route('settings.login-providers');
    }
}
