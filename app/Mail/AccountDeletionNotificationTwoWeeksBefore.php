<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class AccountDeletionNotificationTwoWeeksBefore extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: __(key: 'mail.account_deletion_notification_two_weeks_before.subject', locale: $this->user?->language),
        );
    }

    public function content(): Content {
        App::setLocale($this->user?->language);
        return new Content(
            view: 'mail.account_deletion_notification_two_weeks_before',
            with: [
                      'user' => $this->user,
                  ],
        );
    }
}
