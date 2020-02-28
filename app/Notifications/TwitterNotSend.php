<?php

namespace App\Notifications;

use App\Exceptions\ShouldDeleteNotificationException;
use App\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TwitterNotSend extends Notification {
    use Queueable;

    public $error;
    public $status;

    public function __construct($error, Status $status) {
        $this->error = $error;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'error' => $this->error,
            'status_id' => $this->status->id,
        ];
    }

    public static function render($notification) {
        $data = $notification->data;
        
        try {
            $status = Status::findOrFail($data['status_id']);
        } catch(ModelNotFoundException $e) {
            throw new ShouldDeleteNotificationException();
        }

        $hafas = $status->trainCheckin->hafasTrip;
        
        return view("includes.notification", [
            'color' => "warning",
            'icon' => "fas fa-exclamation-triangle",
            'lead' => __('notifications.socialNotShared.lead', ['platform' => "Twitter"]),
            "link" => route('statuses.get', ['id' => $status->id]),
            'notice' => __('notifications.socialNotShared.twitter.' . $data['error']),

            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at != null,
            'notificationId' => $notification->id
        ])->render();
    }
}