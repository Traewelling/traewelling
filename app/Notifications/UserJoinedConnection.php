<?php

namespace App\Notifications;

use App\Helpers\Lang;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserJoinedConnection extends Notification implements BaseNotification
{
    use Queueable;

    private Status $status;

    private string $origin;

    private string $destination;

    public function __construct(Status $status, ?string $locale = null)
    {
        $this->status = $status;

        $this->origin = $status->checkin->originStopover->station->name;
        $this->destination = $status->checkin->destinationStopover->station->name;
        $this->locale = $locale;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->only(['id']),
            'checkin' => [
                'linename' => $this->status->checkin->trip->linename,
                'origin' => $this->status->checkin->originStopover->station->name,
                'destination' => $this->status->checkin->destinationStopover->station->name,
            ],
            'user' => $this->status->user->only(['id', 'username', 'name']),
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.userJoinedConnection.lead', [
            'username' => '<b>' . e($data['user']['username']) . '</b>',
        ], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        return Lang::trans_choice(
            'notifications.userJoinedConnection.notice',
            preg_match('/\s/', $data['checkin']['linename']), [
                'username' => $data['user']['username'],
                'linename' => '<b>' . e($data['checkin']['linename']) . '</b>',
                'origin' => '<b>' . e($data['checkin']['origin']) . '</b>',
                'destination' => '<b>' . e($data['checkin']['destination']) . '</b>',
            ],
            $locale
        );
    }

    public static function getLink(array $data): ?string
    {
        return route('status', ['id' => $data['status']['id']]);
    }
}
