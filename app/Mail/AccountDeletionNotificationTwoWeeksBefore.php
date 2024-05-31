<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionNotificationTwoWeeksBefore extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
        $this->locale(str_starts_with($user->language, 'de') ? 'de' : 'en'); //other languages currently don't have a translation here and (bug?) fall back to the default locale doesn't work?
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: __(
                         key:    'mail.account_deletion_notification_two_weeks_before.subject',
                         locale: str_starts_with($this->user->language, 'de') ? 'de' : 'en'
                     ),
        );
    }

    public function content(): Content {
        return new Content(
            view: 'mail.account_deletion_notification_two_weeks_before',
            with: [
                      'user' => $this->user,
                  ],
        );
    }
}
