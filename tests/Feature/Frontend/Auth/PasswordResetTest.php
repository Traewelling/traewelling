<?php

namespace Tests\Feature\Frontend\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\FeatureTestCase;

class PasswordResetTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_password_reset_after_creation(): void
    {
        $this->assertGuest();
        Notification::fake();

        $user = User::factory(['created_at' => now()])->create();
        $response = $this->followingRedirects()
            ->post(route('password.email'), [
                'email' => $user->email,
            ]);
        $response->assertOk();
        $mails = DB::table('password_resets')->where('email', $user->email)->get();
        $this->assertEmpty($mails);

        Notification::assertNothingSent();
    }

    public function test_password_reset(): void
    {
        $this->assertGuest();
        Notification::fake();
        $this->get(route('password.request'))->assertViewIs('vue.spa');

        $user = User::factory(['created_at' => now()->subDay()])->create();
        $response = $this->followingRedirects()
            ->post(route('password.email'), [
                'email' => $user->email,
            ]);
        $response->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_with_nonexistent_email_returns_consistent_message(): void
    {
        // OWASP: Avoid user enumeration - return the same message regardless of whether the account exists
        $this->assertGuest();
        Notification::fake();
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', trans('passwords.sent_if_exists'));
        Notification::assertNothingSent();
    }

    public function test_password_reset_with_correct_token(): void
    {
        $this->assertGuest();

        $user = User::factory(['created_at' => now()->subDay()])->create();
        Notification::fake();
        $response = $this->followingRedirects()
            ->post(route('password.email'), [
                'email' => $user->email,
            ]);
        $response->assertOk();
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $token]))
            ->assertOk()
            ->assertViewIs('vue.spa');

        $this->postJson(route('password.update'), [
            'token' => 'wrong token',
            'email' => $user->email,
            'password' => 'GoodPassword123!',
            'password_confirmation' => 'GoodPassword123!',
        ])->assertJsonValidationErrors(['email' => 'This password reset token is invalid.']);

        $this->postJson(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'GoodPassword123!',
            'password_confirmation' => 'GoodPassword123!',
        ])->assertOk();
        $this->assertAuthenticatedAs($user);
    }
}
