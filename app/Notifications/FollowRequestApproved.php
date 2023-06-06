<?php

namespace App\Notifications;

use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowRequestApproved extends Notification implements BaseNotification
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
            'follow' => $this->follow->only(['id']),
            'user'   => $this->follow->following->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.userApprovedFollow.lead', [
            'followerRequestUsername' => $data['user']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return null;
    }

    public static function getLink(array $data): ?string {
        return route('profile', [
            'username' => $data['user']['username'],
        ]);
    }
}
