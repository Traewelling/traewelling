<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserNotificationMessageResource;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use stdClass;

class UserJoinedConnection extends Notification
{
    use Queueable;

    private ?int $statusId;
    private ?string $linename;
    private ?string $origin;
    private ?string $destination;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct(
        int    $statusId = null,
        string $linename = null,
        string $origin = null,
        string $destination = null
    ) {
        $this->statusId    = $statusId;
        $this->linename    = $linename;
        $this->origin      = $origin;
        $this->destination = $destination;
    }

    /** @deprecated will be handled in frontend */
    public static function render(DatabaseNotification $notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }
        $data = $notification->data;

        return view("includes.notification", [
            'color'           => "neutral",
            'icon'            => "fa fa-train",
            'lead'            => __('notifications.userJoinedConnection.lead',
                                    ['username' => $detail->status->user->username
                                    ]),
            "link"            => route('statuses.get', ['id' => $detail->status->id]),
            'notice'          => trans_choice('notifications.userJoinedConnection.notice',
                                              preg_match('/\s/', $data['linename']), [
                                                  'username'    => $detail->status->user->username,
                                                  'linename'    => $data['linename'],
                                                  'origin'      => $data['origin'],
                                                  'destination' => $data['destination']
                                              ]),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }

    /**
     * @param DatabaseNotification $notification
     *
     * @return stdClass
     * @throws ShouldDeleteNotificationException
     */
    public static function detail(DatabaseNotification $notification): stdClass {
        $data                 = $notification->data;
        $notification->detail = new stdClass();
        try {
            $status = Status::findOrFail($data['status_id']);
        } catch (ModelNotFoundException) {
            throw new ShouldDeleteNotificationException();
        }

        $notification->detail->status  = new StatusResource($status);
        $notification->detail->message = new UserNotificationMessageResource
        ([
             'icon'   => 'fa fa-train',
             'lead'   => [
                 'key'    => 'notifications.userJoinedConnection.lead',
                 'values' => [
                     'username' => $status->user->username
                 ]
             ],
             'notice' => [
                 'key'    => 'notifications.userJoinedConnection.notice',
                 'values' => [
                     'username'    => $status->user->username,
                     'linename'    => $data['linename'],
                     'origin'      => $data['origin'],
                     'destination' => $data['destination']
                 ]
             ]
         ]);
        return $notification->detail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'status_id'   => $this->statusId,
            'linename'    => $this->linename,
            'origin'      => $this->origin,
            'destination' => $this->destination
        ];
    }
}
