<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\EmailChanged;
use App\Models\MailChange;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailChangedMail implements ShouldQueue
{
    use Queueable;

    private User $user;

    private MailChange $change;

    public function __construct(User $user, MailChange $mailChange)
    {
        $this->user = $user;
        $this->change = $mailChange;
    }

    public function handle(): void
    {
        Mail::to($this->change->old_email)->send(new EmailChanged($this->user, $this->change));
    }
}
