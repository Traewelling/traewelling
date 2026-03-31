<?php

namespace App\Notifications;

use App\Helpers\Lang;
use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserFollowed extends Notification implements BaseNotification
{
    use Queueable;

    public ?Follow $follow;

    public function __construct(?Follow $follow = null)
    {
        $this->follow = $follow;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'follow' => $this->follow->only(['id']),
            'follower' => $this->follow->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.userFollowed.lead', [
            'followerUsername' => $data['follower']['username'],
        ], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return null;
    }

    public static function getLink(array $data): ?string
    {
        return route('profile', [
            'username' => $data['follower']['username'],
        ]);
    }
}
