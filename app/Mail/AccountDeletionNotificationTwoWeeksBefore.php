<?php

namespace App\Mail;

use App\Helpers\Lang;
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

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->locale($user->language);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: Lang::trans(
                key: 'mail.account_deletion_notification_two_weeks_before.subject',
                locale: $this->user->language,
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.account_deletion_notification_two_weeks_before',
            with: [
                'user' => $this->user,
            ],
        );
    }
}
