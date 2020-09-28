<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MastodonNotSent extends Notification
{
    use Queueable;

    public $error;
    public $status;

    public function __construct($error, Status $status)
    {
        $this->error  = $error;
        $this->status = $status;
    }

    public function via()
    {
        return ['database'];
    }

    public function toArray()
    {
        return [
            'error' => $this->error,
            'status_id' => $this->status->id,
        ];
    }

    public static function detail($notification)
    {
        $data = $notification->data;

        try {
            $status = Status::findOrFail($data['status_id']);
        } catch(ModelNotFoundException $e) {
            throw new ShouldDeleteNotificationException();
        }
        $notification->detail         = new \stdClass();
        $notification->detail->status = $status;
        return $notification->type;
    }

    public static function render($notification)
    {
        try {
            $detail = Self::detail($notification);
        } catch (ShouldDeleteNotificationException $e) {
            $notification->delete();
            return null;
        }
        $data = $notification->data;


        return view("includes.notification", [
            'color' => "warning",
            'icon' => "fas fa-exclamation-triangle",
            'lead' => __('notifications.socialNotShared.lead', ['platform' => "Mastodon"]),
            "link" => route('statuses.get', ['id' => $detail->status->id]),
            'notice' => __('notifications.socialNotShared.mastodon.' . $data['error']),

            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at != null,
            'notificationId' => $notification->id
        ])->render();
    }
}
