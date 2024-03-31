<?php

namespace App\Notifications;

use App\Models\Mention;
use App\Models\User;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification implements BaseNotification
{
    use Queueable;

    private Status  $status;
    private Mention $mention;
    private User    $creator;

    public function __construct(Mention $mention) {
        $this->mention = $mention;
        $this->creator = $mention->status->user;
        $this->status  = $mention->status;
    }


    public function via(object $notifiable): array {
        return ['database'];
    }


    public function toArray(): array {
        return [
            'status'  => $this->status->only(['id']),
            'creator' => $this->creator->only(['id', 'username', 'name']),
            'mention' => $this->mention->only(['id', 'position', 'length']),
        ];
    }

    public static function getLead(array $data): string {
        return "Du wurdest erwähnt!";
    }

    public static function getNotice(array $data): ?string {
        return $data['creator']['username'] . " hat dich in einem Beitrag erwähnt!";
    }

    public static function getLink(array $data): ?string {
        return route('status', ['id' => $data['status']['id']]);
    }
}
