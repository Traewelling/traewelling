<?php

namespace App\Notifications;

use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserJoinedConnection extends Notification implements BaseNotification
{
    use Queueable;

    private Status $status;

    public function __construct(Status $status) {
        $this->status = $status;

        $this->origin      = $status->checkin->originStopover->station->name;
        $this->destination = $status->checkin->destinationStopover->station->name;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'status'  => $this->status->only(['id']),
            'checkin' => [
                'linename'    => $this->status->checkin->trip->linename,
                'origin'      => $this->status->checkin->originStopover->station->name,
                'destination' => $this->status->checkin->destinationStopover->station->name,
            ],
            'user'    => $this->status->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.userJoinedConnection.lead', [
            'username' => $data['user']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return trans_choice(
            'notifications.userJoinedConnection.notice',
            preg_match('/\s/', $data['checkin']['linename']), [
                'username'    => $data['user']['username'],
                'linename'    => $data['checkin']['linename'],
                'origin'      => $data['checkin']['origin'],
                'destination' => $data['checkin']['destination'],
            ]
        );
    }

    public static function getLink(array $data): ?string {
        return route('status', ['id' => $data['status']['id']]);
    }
}
