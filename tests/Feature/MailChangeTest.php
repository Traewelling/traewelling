<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Backend\SettingsController;
use App\Jobs\CleanMailChanges;
use App\Jobs\SendEmailChangedMail;
use App\Mail\EmailChanged;
use App\Models\MailChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\FeatureTestCase;

class MailChangeTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_mail_change_is_deleted_when_user_is_deleted(): void
    {
        $user = User::factory()->create();

        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $this->assertDatabaseCount('mail_changes', 1);

        $user->delete();

        $this->assertDatabaseCount('mail_changes', 0);
    }

    public function test_user_has_many_mail_changes(): void
    {
        $user = User::factory()->create();

        MailChange::create(['user_id' => $user->id, 'old_email' => 'a@example.com', 'new_email' => 'b@example.com']);
        MailChange::create(['user_id' => $user->id, 'old_email' => 'b@example.com', 'new_email' => 'c@example.com']);

        $this->assertCount(2, $user->mailChanges);
    }

    public function test_update_mail_creates_mail_change_record(): void
    {
        Queue::fake();

        $user = User::factory(['email' => 'original@example.com'])->create();

        SettingsController::updateMail('changed@example.com', $user);

        $this->assertDatabaseHas('mail_changes', [
            'user_id' => $user->id,
            'old_email' => 'original@example.com',
            'new_email' => 'changed@example.com',
        ]);
    }

    public function test_update_mail_dispatches_send_email_changed_mail_job(): void
    {
        Queue::fake();

        $user = User::factory(['email' => 'original@example.com'])->create();

        SettingsController::updateMail('changed@example.com', $user);

        Queue::assertPushed(SendEmailChangedMail::class);
    }

    public function test_update_mail_updates_user_email(): void
    {
        Queue::fake();

        $user = User::factory(['email' => 'original@example.com'])->create();

        SettingsController::updateMail('changed@example.com', $user);

        $this->assertSame('changed@example.com', $user->fresh()->email);
    }

    public function test_update_mail_resets_email_verified_at(): void
    {
        Queue::fake();

        $user = User::factory(['email' => 'original@example.com', 'email_verified_at' => now()])->create();
        $this->assertNotNull($user->email_verified_at);

        SettingsController::updateMail('changed@example.com', $user);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_send_email_changed_mail_job_sends_to_old_address(): void
    {
        Mail::fake();

        $user = User::factory(['email' => 'new@example.com'])->create();
        $change = MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        new SendEmailChangedMail($user, $change)->handle();

        Mail::assertSent(EmailChanged::class, function (EmailChanged $mail) {
            return $mail->hasTo('old@example.com');
        });
        Mail::assertSentCount(1);
    }

    public function test_email_changed_mailable_renders_without_error(): void
    {
        $user = User::factory(['language' => 'en'])->create();
        $change = MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $mailable = new EmailChanged($user, $change);
        $content = $mailable->content();
        $this->assertSame('mail.email-changed', $content->markdown);
        $mailable->assertSeeInHtml($change->new_email);
        $mailable->assertSeeInHtml($change->id);
    }

    public function test_clean_mail_changes_deletes_records_older_than_30_days(): void
    {
        $user = User::factory()->create();
        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $this->assertDatabaseCount('mail_changes', 1);

        $this->travel(31)->days();
        new CleanMailChanges()->handle();

        $this->assertDatabaseCount('mail_changes', 0);
    }

    public function test_clean_mail_changes_keeps_recent_records(): void
    {
        $user = User::factory()->create();
        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $this->assertDatabaseCount('mail_changes', 1);

        new CleanMailChanges()->handle();

        $this->assertDatabaseCount('mail_changes', 1);
    }

    public function test_clean_mail_changes_keeps_records_exactly_at_30_days(): void
    {
        $user = User::factory()->create();
        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $this->assertDatabaseCount('mail_changes', 1);

        $this->travel(30)->days();
        new CleanMailChanges()->handle();

        $this->assertDatabaseCount('mail_changes', 1);
    }

    public function test_clean_mail_changes_only_deletes_old_records(): void
    {
        $user = User::factory()->create();

        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'a@example.com',
            'new_email' => 'b@example.com',
        ]);

        $this->travel(31)->days();

        MailChange::create([
            'user_id' => $user->id,
            'old_email' => 'b@example.com',
            'new_email' => 'c@example.com',
        ]);

        $this->assertDatabaseCount('mail_changes', 2);

        new CleanMailChanges()->handle();

        $this->assertDatabaseCount('mail_changes', 1);
        $this->assertDatabaseHas('mail_changes', ['old_email' => 'b@example.com']);
        $this->assertDatabaseMissing('mail_changes', ['old_email' => 'a@example.com']);
    }
}
