<?php

namespace App\Notifications;

use App\Helpers\Lang;
use App\Models\FollowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowRequestIssued extends Notification implements BaseNotification
{
    use Queueable;

    public ?FollowRequest $followRequest;

    public function __construct(?FollowRequest $followRequest = null)
    {
        $this->followRequest = $followRequest;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'followRequest' => $this->followRequest->only(['id']),
            'user' => $this->followRequest->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.userRequestedFollow.lead', [
            'followerRequestUsername' => $data['user']['username'],
        ],

            $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return Lang::trans('notifications.userRequestedFollow.notice', [], $locale);
    }

    public static function getLink(array $data): ?string
    {
        return url('/settings/followers');
    }
}
