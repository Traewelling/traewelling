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

    public function testPasswordReset(): void {
        $this->assertGuest();
        Notification::fake();

        $user     = User::factory()->create();
        $response = $this->followingRedirects()
                         ->post(route('password.email'), [
                             'email' => $user->email,
                         ]);
        $response->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function testPasswordResetWithWrongEmail(): void {
        $this->assertGuest();
        Notification::fake();
        $response = $this->post(route('password.email'), [
            'email' => 'wrong@email.de'
        ]);
        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function testPasswordResetWithCorrectToken(): void {
        $this->assertGuest();

        $user = User::factory()->create();
        Notification::fake();
        $response = $this->followingRedirects()
                         ->post(route('password.email'), [
                             'email' => $user->email,
                         ]);
        $response->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);

        $newToken = 'some random token, because we cannot access the real token';
        $token    = hash('sha256', $newToken);
        DB::table('password_resets')->where('email', $user->email)->update(['token' => $token]);

        $response = $this->get(route('password.reset', ['token' => $token]));
        $response->assertOk();

        $response = $this->followingRedirects()
                         ->post(route('password.update'), [
                             'token'                 => 'wrong token',
                             'email'                 => $user->email,
                             'password'              => 'GoodPassword123!',
                             'password_confirmation' => 'GoodPassword123!',
                         ]);
        $response->assertSeeText('This password reset token is invalid.');

        $response = $this->followingRedirects()
                         ->post(route('password.update'), [
                             'token'                 => $newToken,
                             'email'                 => $user->email,
                             'password'              => 'GoodPassword123!',
                             'password_confirmation' => 'GoodPassword123!',
                         ]);
        $response->assertOk();
    }
}
