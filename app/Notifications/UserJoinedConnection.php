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

    private Status $status;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct(Status $status) {
        $this->status = $status;
    }

    /** @deprecated will be handled in frontend */
    public static function render(DatabaseNotification $notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }

        return view("includes.notification", [
            'color'           => "neutral",
            'icon'            => "fa fa-train",
            'lead'            => __('notifications.userJoinedConnection.lead', [
                'username' => $detail->status->user->username
            ]),
            "link"            => route('statuses.get', ['id' => $detail->status->id]),
            'notice'          => trans_choice(
                'notifications.userJoinedConnection.notice',
                preg_match('/\s/', $detail->status->trainCheckin->HafasTrip->linename), [
                    'username'    => $detail->status->user->username,
                    'linename'    => $detail->status->trainCheckin->HafasTrip->linename,
                    'origin'      => $detail->status->trainCheckin->Origin->name,
                    'destination' => $detail->status->trainCheckin->Destination->name,
                ]
            ),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at !== null,
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
        $notification->detail = new stdClass();
        try {
            $status = Status::findOrFail($notification->data['status_id'] ?? null);
        } catch (ModelNotFoundException) {
            throw new ShouldDeleteNotificationException();
        }

        $notification->detail->status  = new StatusResource($status);
        $notification->detail->message = new UserNotificationMessageResource(
            [
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
                        'linename'    => $status->trainCheckin->HafasTrip->linename,
                        'origin'      => $status->trainCheckin->Origin->name,
                        'destination' => $status->trainCheckin->Destination->name,
                    ]
                ]
            ]
        );
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
     * This array is saved as `data` in the database
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'status_id'   => $this->status->id,
            'linename'    => $this->status->trainCheckin->HafasTrip->linename,
            'origin'      => $this->status->trainCheckin->Origin->name,
            'destination' => $this->status->trainCheckin->Destination->name,
        ];
    }
}
