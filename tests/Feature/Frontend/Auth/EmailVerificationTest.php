<?php declare(strict_types=1);

namespace Tests\Feature\Frontend\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\FeatureTestCase;

class EmailVerificationTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testEmailVerificationNotification(): void {
        Notification::fake();

        $user     = User::factory(['email_verified_at' => null])->create();
        $response = $this->actingAs($user)
                         ->followingRedirects()
                         ->post(route('verification.resend'));
        $response->assertOk();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function testEmailVerificationJSONNotification(): void {
        Notification::fake();

        $user     = User::factory(['email_verified_at' => null])->create();
        $response = $this->actingAs($user)
                         ->followingRedirects()
                         ->post(route('verification.resend'), [], [
                             'Accept' => 'application/json',
                         ]);
        $response->assertAccepted();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function testEmailVerificationNotificationWithVerifiedEmail(): void {
        Notification::fake();

        $user     = User::factory(['email_verified_at' => today()])->create();
        $response = $this->actingAs($user)
                         ->followingRedirects()
                         ->post(route('verification.resend'));
        $response->assertOk();

        // test json response (should be 204 with already verified email)
        $response = $this->actingAs($user)
                         ->followingRedirects()
                         ->post(route('verification.resend'), [], [
                             'Accept' => 'application/json',
                         ]);
        $response->assertNoContent();

        Notification::assertNothingSent();
    }

    public function testEmailVerificationNotificationWithTooManyRequests(): void {
        Notification::fake();

        $user = User::factory(['email_verified_at' => null])->create();
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)
                             ->followingRedirects()
                             ->post(route('verification.resend'));
            $response->assertOk();
        }

        $response = $this->actingAs($user)
                         ->post(route('verification.resend'));
        $response->assertTooManyRequests();

        // but should still send the email once
        Notification::assertSentTo($user, VerifyEmail::class, 1);
    }

}
