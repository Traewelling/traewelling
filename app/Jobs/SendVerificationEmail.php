<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Traewelling\QueueMonitor\Traits\IsMonitored;

/**
 * Send the Email which verifies a user account asynchronously.
 * @see https://aregsar.com/blog/2020/how-to-queue-laravel-user-verification-email/
 */
class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    protected User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        $this->queueData([
                             "user_id"  => $this->user->id,
                             "username" => $this->user->username,
                         ]);

        // This queued job sends
        // Illuminate\Auth\Notifications\VerifyEmail verification
        // to the user by triggering the notification
        $this->user->notify(new VerifyEmail);
    }
}
