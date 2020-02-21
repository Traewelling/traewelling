<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Like;
use App\Status;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StatusLiked extends Notification {
    use Queueable;

    public $like;

    /**
     * Create a new notification instance
     *
     * @return void
     */
    public function __construct(Like $like = null) {
        $this->like = $like;
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
            'like_id' => $this->like->id,
        ];
    }

    public static function render($notification) {
        $data = $notification->data;
        
        try {
            $like = Like::findOrFail($data['like_id']);
            $sender = User::findOrFail($like->user_id);
            $status = Status::findOrFail($like->status_id);
        } catch(ModelNotFoundException $e) {
            // Either the status was unliked, or the sender has deleted its account,
            // or the status was deleted. Eitherway, we don't need the notification anymore.
            throw new ShouldDeleteNotificationException();
        }

        $hafas = $status->trainCheckin->hafasTrip;
        
        return view("includes.notification", [
            'color' => "neutral",
            'icon' => "fas fa-heart",
            'lead' => __('notifications.statusLiked.lead', ['likerUsername' => $sender->username]),
            "link" => route('statuses.get', ['id' => $status->id]),
            'notice' => trans_choice(
                'notifications.statusLiked.notice',
                preg_match('/\s/', $hafas->linename),
                [
                    'line' => $hafas->linename,
                    'createdDate' => date("Y-m-d", strtotime($hafas->departure))
                ]
                ),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at != null

        ])->render();
    }
}