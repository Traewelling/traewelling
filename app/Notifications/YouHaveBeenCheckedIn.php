<?php

namespace App\Notifications;

use App\Models\Status;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class YouHaveBeenCheckedIn extends Notification implements BaseNotification
{
    use Queueable;

    private Status $status;
    private User   $userCheckedIn;

    public function __construct(Status $status, User $userCheckedIn) {
        $this->status        = $status;
        $this->userCheckedIn = $userCheckedIn;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'status'  => $this->status->only(['id']),
            'checkin' => [
                'line'        => $this->status->checkin->trip->linename,
                'origin'      => $this->status->checkin->originStopover->station->name,
                'destination' => $this->status->checkin->destinationStopover->station->name,
            ],
            'user'    => $this->userCheckedIn->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.youHaveBeenCheckedIn.lead', [
            'username' => $data['user']['username'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.youHaveBeenCheckedIn.notice', [
                                                                 'line'        => $data['checkin']['line'],
                                                                 'origin'      => $data['checkin']['origin'],
                                                                 'destination' => $data['checkin']['destination'],
                                                             ]
        );
    }

    public static function getLink(array $data): ?string {
        return route('status', ['id' => $data['status']['id']]);
    }
}
