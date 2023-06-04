<?php

namespace App\Notifications;

use App\Models\Follow;
use Illuminate\Bus\Queueable;

class UserFollowed extends BaseNotification
{
    use Queueable;

    public Follow $follow;

    public function __construct(Follow $follow = null) {
        $this->follow = $follow;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'follow'   => $this->follow->only(['id']),
            'follower' => $this->follow->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.userFollowed.lead', [
            'followerUsername' => $data['follower']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return null;
    }

    public static function getLink(array $data): ?string {
        return route('profile', [
            'username' => $data['follower']['username'],
        ]);
    }
}
