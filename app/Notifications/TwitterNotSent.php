<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;

class TwitterNotSent extends Notification
{
    use Queueable;

    public string $error;
    public Status $status;

    public function __construct(string $error, Status $status) {
        $this->error  = $error;
        $this->status = $status;
    }

    public function via(): array {
        return ['database'];
    }

    #[ArrayShape(['error' => "string", 'status_id' => "int"])]
    public function toArray(): array {
        return [
            'error'     => $this->error,
            'status_id' => $this->status->id,
        ];
    }

    /**
     * @param DatabaseNotification $notification
     *
     * @return string
     * @throws ShouldDeleteNotificationException
     */
    public static function detail(DatabaseNotification $notification): string {
        $data                 = $notification->data;
        $notification->detail = new stdClass();

        try {
            $status = Status::findOrFail($data['status_id']);
        } catch (ModelNotFoundException) {
            throw new ShouldDeleteNotificationException();
        }

        $notification->detail->status = $status;
        return $notification->type;
    }

    public static function render(DatabaseNotification $notification): ?string {
        try {
            self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }

        $data = $notification->data;
        return view("includes.notification", [
            'color'  => "warning",
            'icon'   => "fas fa-exclamation-triangle",
            'lead'   => __('notifications.socialNotShared.lead', ['platform' => "Twitter"]),
            "link"   => route('statuses.get', ['id' => $data['status_id']]),
            'notice' => __('notifications.socialNotShared.twitter.' . $data['error']),

            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }
}
