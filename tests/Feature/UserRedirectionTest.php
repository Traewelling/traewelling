<?php

namespace Tests\Feature;

use App\Models\PrivacyAgreement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRedirectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * If not logged in, redirect please.
     * @test
     */
    public function should_redirect_dashboard_to_login_if_not_logged_in() {
        // Given: A new visitor, no user account
        // ---

        // When: Tries to visit a protected page
        $response = $this->get('/dashboard');

        // Then: Redirect to login.
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Check if users can delete their fresh account.
     * @test
     */
    public function user_can_delete_account() {
        // Given: A new user
        $user = User::factory()->create();

        // When: They delete their account
        $response = $this->actingAs($user)
                         ->get(route('account.destroy'));
        $response->assertStatus(302);
        $response->assertRedirect('/');

        // Then: It isn't there anymore.
        $this->expectException(ModelNotFoundException::class);
        User::firstOrFail($user->id);
    }

    /**
     * Test the GDPR interceptor.
     * First, new users should always redirect to the interceptor.
     * Then,
     * @test
     */
    public function gdpr_interception() {
        // Creates user
        $user = User::factory()->create();

        // Has not yet signed -> Redirection.
        $response = $this->actingAs($user)
                         ->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/gdpr-intercept');
        $this->followRedirects($response)
             ->assertSee(__('privacy.not-signed-yet'), false);

        // Signs the terms and sleep a little while so `$gdpr->valid_at` is
        // real-greater than `$user->privacy_ack_at`.
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');
        sleep(1);

        // Now the träwelling team puts up a new terms iteration:
        $gdpr             = new PrivacyAgreement();
        $gdpr->body_md_de = "Not empty";
        $gdpr->body_md_en = "Not empty";
        $gdpr->valid_at   = Carbon::now();
        $gdpr->save();

        // If the user opens the app again, they get intercepted again.
        $response = $this->actingAs($user)
                         ->get('/dashboard/global');
        $response->assertStatus(302);
        $response->assertRedirect('/gdpr-intercept');
        $this->followRedirects($response)
             ->assertSee(__('privacy.we-changed'), false);

        // At this point, we can sign the new agreement and get redirected again:
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
