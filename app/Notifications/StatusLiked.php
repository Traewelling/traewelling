<?php

namespace App\Notifications;

use App\Status;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StatusLiked extends Notification {
    use Queueable;

    public $sender;
    public $status;

    /**
     * Hide certain fields from appearing in JSON
     */
    protected $hidden = [
        "locale",
        "connection",
        "queue",
        "chainConnection",
        "chainQueue",
        "delay",
        "chained"
    ];

    /**
     * Create a new notification instance
     *
     * @param User Who liked the status?
     * @param Status Which status was liked?
     * @return void
     */
    public function __construct(User $sender = null, Status $status = null) {
        $this->sender = $sender;
        $this->status = $status;
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
            'sender_id' => $this->sender->id,
            'status_id' => $this->status->id
        ];
    }

    public static function render($notification) {
        $data = $notification->data;
        $sender = User::findOrFail($data['sender_id']);
        $status = Status::findOrFail($data['status_id']);
        $hafas = $status->trainCheckin->hafasTrip;
        
        return view("includes.notification", [
            'color' => "neutral",
            'icon' => "fas fa-heart",
            'lead' => __('notifications.statusLiked.lead', ['likerUsername' => $sender->username]),
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