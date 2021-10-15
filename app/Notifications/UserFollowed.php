<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use stdClass;

class UserFollowed extends Notification
{
    use Queueable;

    public $follow;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct(Follow $follow = null) {
        $this->follow = $follow;
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
            'follow_id' => $this->follow->id,
        ];
    }

    /**
     * Detail-Handler of notification
     *
     * @throws ShouldDeleteNotificationException
     */
    public static function detail($notification): stdClass {
        $data                 = $notification->data;
        $notification->detail = new stdClass();
        try {
            $follow = Follow::findOrFail($data['follow_id']);
            $sender = User::findOrFail($follow->user_id);
        } catch (ModelNotFoundException) {
            // The follow doesn't exist anymore or the user following you was deleted. Eitherway,
            // we can delete the notification.
            throw new ShouldDeleteNotificationException();
        }
        $notification->detail->follow = $follow;
        $notification->detail->sender = $sender;

        return $notification->detail;
    }

    public static function render($notification): ?string {
        try {
            $detail = self::detail($notification);
        } catch (ShouldDeleteNotificationException) {
            $notification->delete();
            return null;
        }

        return view("includes.notification", [
            'color'           => "neutral",
            'icon'            => "fas fa-user-friends",
            'lead'            => __('notifications.userFollowed.lead', [
                'followerUsername' => $detail->sender->username
            ]),
            "link"            => route('account.show', ['username' => $detail->sender->username]),
            'notice'          => "",
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }
}
