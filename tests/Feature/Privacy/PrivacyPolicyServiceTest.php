<?php

namespace Tests\Feature\Privacy;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class PrivacyPolicyServiceTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_accept_privacy_policy(): void
    {
        $user = User::factory()->create();
        PrivacyPolicyAcceptance::where('user_id', $user->uuid)->delete();

        Passport::actingAs($user, ['*']);

        $this->putJson('/api/v1/settings/acceptPrivacy')
            ->assertNoContent();

        $this->putJson('/api/v1/settings/acceptPrivacy')
            ->assertStatus(409);
    }

    public function test_future_policy(): void
    {
        $future = now()->addDays(7)->startOfDay();
        $user = User::factory()->create();
        PrivacyPolicy::factory()->create(['valid_at' => $future->toIso8601ZuluString()]);
        Passport::actingAs($user, ['*']);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travelTo($future->clone()->subSeconds(5));
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travel(10)->seconds();

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(406);

        $this->putJson('/api/v1/settings/acceptPrivacy')
            ->assertNoContent();

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);
    }

    public function test_not_accepted_policy(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);
    }

    public function test_new_privacy_policy(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travel(2)->days();
        PrivacyPolicy::factory()->create();

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(406);
    }

    public function test_no_accepted_privacy_policy(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        PrivacyPolicyAcceptance::where('user_id', $user->uuid)->delete();

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(406);
    }

    public function test_accept_future_privacy_policy(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $validDate = now()->addDays(7)->setHour(0)->toIso8601ZuluString();

        $policy = PrivacyPolicy::factory()->create([
            'valid_at' => $validDate,
        ]);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->putJson('/api/v1/privacy-policies/' . $policy->id . '/acceptance')
            ->assertNoContent();

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travel(20)->days();
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);
    }

    public function test_dont_accept_future_privacy_policy(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $validDate = now()->addDays(7)->setHour(0)->toIso8601ZuluString();

        $policy = PrivacyPolicy::factory()->create([
            'valid_at' => $validDate,
        ]);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travel(20)->days();
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(406);
    }

    public function test_accept_conflicting_privacy_policy(): void
    {
        PrivacyPolicy::whereNotNull('id')->delete();
        $validDate = now()->subDays(7)->setHour(0);
        $futureValidDate = now()->addDays(7)->setHour(0);
        $obsolete = PrivacyPolicy::factory()->create([
            'valid_at' => $validDate->toIso8601ZuluString(),
        ]);
        $current = PrivacyPolicy::factory()->create([
            'valid_at' => now()->toIso8601ZuluString(),
        ]);
        $future = PrivacyPolicy::factory()->create([
            'valid_at' => $futureValidDate->toIso8601ZuluString(),
        ]);
        $user = User::factory()->create();
        PrivacyPolicyAcceptance::where('user_id', $user->uuid)->delete();

        Passport::actingAs($user, ['*']);
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(406);

        $this->putJson('/api/v1/privacy-policies/' . $current->id . '/acceptance');

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        // test accept obsolete policy
        $this->putJson('/api/v1/privacy-policies/' . $obsolete->id . '/acceptance')
            ->assertStatus(409)
            ->assertSee('obsolete')
            ->assertSee($validDate->toIso8601String());
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        // test accept current policy again
        $this->putJson('/api/v1/privacy-policies/' . $current->id . '/acceptance')
            ->assertStatus(409)
            ->assertSee('User already accepted privacy policy');
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        // accept future privacy policy
        $this->putJson('/api/v1/privacy-policies/' . $future->id . '/acceptance')
            ->assertNoContent();
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);

        $this->travel(20)->days();
        $this->getJson('/api/v1/dashboard')
            ->assertStatus(200);
    }
}
