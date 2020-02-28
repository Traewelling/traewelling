<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Follow;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserFollowed extends Notification {
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'follow_id' => $this->follow->id,
        ];
    }

    public static function render($notification) {
        $data = $notification->data;
        
        try {
            $follow = Follow::findOrFail($data['follow_id']);
            $sender = User::findOrFail($follow->user_id);
        } catch(ModelNotFoundException $e) {
            // The follow doesn't exist anymore or the user following you was deleted. Eitherway,
            // we can delete the notification.
            throw new ShouldDeleteNotificationException();
        }
        
        return view("includes.notification", [
            'color' => "neutral",
            'icon' => "fas fa-user-friends",
            'lead' => __('notifications.userFollowed.lead', ['followerUsername' => $sender->username]),
            "link" => route('account.show', ['username' => $sender->username]),
            'notice' => "",
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at != null,
            'notificationId' => $notification->id
        ])->render();
    }
}