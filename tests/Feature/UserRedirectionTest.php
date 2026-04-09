<?php

namespace Tests\Feature;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\FeatureTestCase;

class UserRedirectionTest extends FeatureTestCase
{
    use RefreshDatabase;

    /**
     * If not logged in, redirect please.
     */
    #[Test]
    public function should_redirect_dashboard_to_login_if_not_logged_in()
    {
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
     */
    #[Test]
    public function user_can_delete_account(): void
    {
        // Given: A new user
        $user = User::factory()->create();

        // When: They delete their account via API
        $response = $this->actingAs($user, 'api')
            ->deleteJson('/api/v1/settings/account', [
                'confirmation' => $user->username,
            ]);
        $response->assertOk();

        // Then: It isn't there anymore.
        $this->expectException(ModelNotFoundException::class);
        User::firstOrFail($user->id);
    }

    /**
     * Test the GDPR interceptor.
     * First, new users should always redirect to the interceptor.
     * Then,
     */
    #[Test]
    public function gdpr_interception()
    {
        // Creates user
        $user = User::factory()->create();
        PrivacyPolicyAcceptance::where('user_id', $user->uuid)->delete();

        // Has not yet signed -> Redirection.
        $response = $this->actingAs($user)
            ->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/gdpr-intercept');
        $this->followRedirects($response)
            ->assertSee(__('privacy.not-signed-yet'), false);

        // Now the träwelling team puts up a new terms iteration:
        $policy1 = PrivacyPolicy::factory()->create(['valid_at' => Carbon::yesterday()->toIso8601String()]);

        new PrivacyPolicyRepository()->acceptPrivacyPolicy($user, $policy1);

        $policy2 = PrivacyPolicy::factory()->create(['valid_at' => Carbon::today()->toIso8601String()]);

        // If the user opens the app again, they get intercepted again.
        $response = $this->actingAs($user)
            ->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/gdpr-intercept');
        $this->followRedirects($response)
            ->assertSee(__('privacy.we-changed'), false)
            ->assertSee('<input type="hidden" name="id" value="' . $policy2->id . '"/>', false)
            ->assertSee(__('privacy.sign.more'), true);

        // At this point, we can sign the new agreement and get redirected again:
        $response = $this->actingAs($user)
            ->post('/gdpr-ack', ['id' => $policy2->id]);
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
    }
}
