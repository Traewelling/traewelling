<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationMessageResource;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use stdClass;

class UserJoinedConnection extends BaseNotification
{
    use Queueable;

    private Status $status;
    private int    $status_id;
    private string $linename;
    private string $origin;
    private string $destination;

    public function __construct(Status $status) {
        $this->status_id   = $status->id;
        $this->linename    = $status->trainCheckin->HafasTrip->linename;
        $this->origin      = $status->trainCheckin->Origin->name;
        $this->destination = $status->trainCheckin->Destination->name;
    }



    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'status_id'   => $this->status_id,
            'linename'    => $this->linename,
            'origin'      => $this->origin,
            'destination' => $this->destination,
        ];
    }

    public static function getIcon(): string {
        return 'fa fa-train';
    }

    public static function getLead(array $data): string {
        return __('notifications.userJoinedConnection.lead', [
            'username' => $data['status_id'], //TODO: Username
        ]);
    }

    public static function getNotice(array $data): ?string {
        return trans_choice(
            'notifications.userJoinedConnection.notice',
            preg_match('/\s/', $data['linename']), [
                'username'    => $data['status_id'], //TODO: Username
                'linename'    => $data['linename'],
                'origin'      => $data['origin'],
                'destination' => $data['destination'],
            ]
        );
    }

    public static function getLink(array $data): ?string {
        return route('statuses.get', ['id' => $data['status_id']]);
    }
}
