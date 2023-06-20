<?php

namespace App\Notifications;

use App\Models\FollowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowRequestIssued extends Notification implements BaseNotification
{
    use Queueable;

    public FollowRequest $followRequest;

    public function __construct(FollowRequest $followRequest = null) {
        $this->followRequest = $followRequest;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'followRequest' => $this->followRequest->only(['id']),
            'user'          => $this->followRequest->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.userRequestedFollow.lead', [
            'followerRequestUsername' => $data['user']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.userRequestedFollow.notice');
    }

    public static function getLink(array $data): ?string {
        return route('settings.follower');
    }
}
