<?php

namespace App\Notifications;

use App\Helpers\Lang;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvalidMastodonServer extends Notification implements BaseNotification
{
    use Queueable;

    private string $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return ['domain' => $this->domain];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.mastodon-server.lead', [], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return Lang::trans('notifications.mastodon-server.exception', ['domain' => $data['domain']], $locale);
    }

    public static function getLink(array $data): ?string
    {
        return url('/settings/security');
    }
}
