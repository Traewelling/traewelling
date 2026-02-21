<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserProfileTest extends ApiTestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Guest access
    // -------------------------------------------------------------------------

    public function test_public_profile_is_accessible_by_guest(): void
    {
        $user = User::factory(['private_profile' => false])->create();

        $this->getJson('/api/v1/user/' . $user->username)
            ->assertOk()
            ->assertJsonPath('data.username', $user->username)
            ->assertJsonPath('data.privateProfile', false);
    }

    public function test_private_profile_is_forbidden_for_guest(): void
    {
        $user = User::factory(['private_profile' => true])->create();

        $this->getJson('/api/v1/user/' . $user->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'PRIVATE_PROFILE');
    }

    // -------------------------------------------------------------------------
    // Authenticated access
    // -------------------------------------------------------------------------

    public function test_public_profile_is_accessible_by_authenticated_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory(['private_profile' => false])->create();

        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertOk()
            ->assertJsonPath('data.username', $bob->username)
            ->assertJsonPath('data.privateProfile', false)
            ->assertJsonPath('data.userInvisibleToMe', false)
            ->assertJsonPath('data.muted', false)
            ->assertJsonPath('data.blocked', false);
    }

    // -------------------------------------------------------------------------
    // Muted user
    // -------------------------------------------------------------------------

    public function test_viewing_a_muted_user_profile_returns_403_with_muted_reason(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        UserController::muteUser($alice, $bob);

        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'USER_MUTED')
            ->assertJsonPath('meta.user.username', $bob->username)
            ->assertJsonPath('meta.user.muted', true);
    }

    // -------------------------------------------------------------------------
    // Blocked by auth user (auth user blocked the profile)
    // -------------------------------------------------------------------------

    public function test_viewing_a_blocked_user_profile_returns_403_with_blocked_reason(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        UserController::blockUser($alice, $bob);

        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'USER_BLOCKED')
            ->assertJsonPath('meta.user.username', $bob->username)
            ->assertJsonPath('meta.user.blocked', true);
    }

    // -------------------------------------------------------------------------
    // Blocked by private profile
    // -------------------------------------------------------------------------

    public function test_private_profile_that_blocked_auth_user_returns_you_are_blocked_not_private_profile(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory(['private_profile' => true])->create();

        UserController::blockUser($bob, $alice);

        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'YOU_ARE_BLOCKED');
    }
}
