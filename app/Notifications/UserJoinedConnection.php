<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Models\Status;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserJoinedConnection extends Notification
{
    use Queueable;

    private $statusId;
    private $linename;
    private $origin;
    private $destination;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct($statusId = null, $linename = null, $origin = null, $destination = null)
    {
        $this->statusId    = $statusId;
        $this->linename    = $linename;
        $this->origin      = $origin;
        $this->destination = $destination;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed
     * @return array
     */
    public function via()
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed
     * @return array
     */
    public function toArray()
    {
        return [
            'status_id' => $this->statusId,
            'linename' => $this->linename,
            'origin' => $this->origin,
            'destination' => $this->destination
        ];
    }

    public static function detail($notification)
    {
        $data                 = $notification->data;
        $notification->detail = new \stdClass();
        try {
            $status = status::findOrFail($data['status_id']);
        } catch(ModelNotFoundException $e) {
            throw new ShouldDeleteNotificationException();
        }

        $notification->detail->status = $status;
        return $notification->detail;
    }

    public static function render($notification)
    {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException $e) {
            $notification->delete();
            return null;
        }
        $data = $notification->data;

        return view("includes.notification", [
            'color' => "neutral",
            'icon' => "fa fa-train",
            'lead' => __('notifications.userJoinedConnection.lead',
                         ['username' => $detail->status->user->username
                         ]),
            "link" => route('statuses.get', ['id' => $detail->status->id]),
            'notice' => trans_choice('notifications.userJoinedConnection.notice',
                                     preg_match('/\s/', $data['linename']), [
                                         'username' => $detail->status->user->username,
                                         'linename' => $data['linename'],
                                         'origin' => $data['origin'],
                                         'destination' => $data['destination']
                                     ]),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at != null,
            'notificationId' => $notification->id
        ])->render();
    }
}
