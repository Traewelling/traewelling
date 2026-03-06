<?php

namespace App\Mail;

use App\Helpers\Lang;
use App\Models\MailChange;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChanged extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    private MailChange $mailChange;

    public function __construct(User $user, MailChange $mailChange)
    {
        $this->user = $user;
        $this->mailChange = $mailChange;
        $this->locale($this->user->language);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: Lang::trans(
                key: 'mail.email_changed.subject',
                locale: $this->user->language,
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.email-changed',
            with: [
                'user' => $this->user,
                'mailChange' => $this->mailChange,
                'locale' => $this->user->language,
            ],
        );
    }
}
