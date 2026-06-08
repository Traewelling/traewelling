<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\MailChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AdminUserTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_users(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/users');

        $res->assertOk();
        $res->assertJsonStructure(['data' => [
            '*' => ['id', 'username', 'displayName', 'email', 'emailVerifiedAt', 'mastodonUrl', 'lastLogin', 'createdAt'],
        ]]);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_users(): void
    {
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
    }

    public function test_admin_can_search_users(): void
    {
        $target = User::factory()->create(['username' => 'findme_user', 'name' => 'Find Me']);

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/users?query=findme');

        $res->assertOk();
        $found = collect($res->json('data'))->pluck('id')->contains($target->id);
        $this->assertTrue($found, 'Search result should contain the matching user');
    }

    public function test_non_admin_cannot_search_users(): void
    {
        $target = User::factory()->create(['username' => 'findme_user', 'name' => 'Find Me']);

        $this->actAsApiUserWithAllScopes($this->user);
        $res = $this->getJson('/api/v1/admin/users?query=findme');

        $res->assertForbidden();
    }

    public function test_unauthenticated_cannot_searc_users(): void
    {
        $this->getJson('/api/v1/admin/users?query=findme')->assertUnauthorized();
    }

    public function test_admin_can_view_user(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/users/' . $this->user->id);

        $res->assertOk();
        $res->assertJsonStructure(['data' => [
            'id', 'username', 'displayName', 'email', 'emailVerifiedAt', 'hasPassword',
            'mastodonUrl', 'lastLogin', 'createdAt', 'trainDistance', 'trainDuration', 'points',
            'roles', 'allRoles', 'mailChanges',
            'privacyPolicyCurrent', 'privacyPolicyFuture', 'privacyPolicyFutureExists',
            'recentStatuses',
        ]]);
        $res->assertJsonPath('data.id', $this->user->id);
    }

    public function test_non_admin_cannot_view_user(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/users/' . $this->admin->id)->assertForbidden();
    }

    public function test_unauthenticated_cannot_view_user(): void
    {
        $this->getJson('/api/v1/admin/users/' . $this->user->id)->assertUnauthorized();
    }

    public function test_view_nonexistent_user_returns_404(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson('/api/v1/admin/users/99999999')->assertNotFound();
    }

    public function test_admin_can_view_user_mail_changes(): void
    {
        MailChange::create([
            'user_id' => $this->user->id,
            'old_email' => 'old@example.com',
            'new_email' => 'new@example.com',
        ]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/users/' . $this->user->id);

        $res->assertOk();
        $res->assertJsonCount(1, 'data.mailChanges');
        $res->assertJsonPath('data.mailChanges.0.oldEmail', 'old@example.com');
        $res->assertJsonPath('data.mailChanges.0.newEmail', 'new@example.com');
    }

    public function test_admin_can_update_user_email(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/email', [
            'email' => 'newemail@example.com',
        ]);

        $res->assertNoContent();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_admin_cannot_update_email_to_existing_email(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/email', [
            'email' => 'taken@example.com',
        ]);

        $res->assertUnprocessable();
    }

    public function test_non_admin_cannot_update_user_email(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson('/api/v1/admin/users/' . $this->admin->id . '/email', [
            'email' => 'hacked@example.com',
        ])->assertForbidden();
    }

    public function test_unauthenticated_cannot_update_user_email(): void
    {
        $this->putJson('/api/v1/admin/users/' . $this->user->id . '/email', [
            'email' => 'anon@example.com',
        ])->assertUnauthorized();
    }

    public function test_admin_can_update_user_roles(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/roles', [
            'roles' => ['open-beta'],
        ]);

        $res->assertNoContent();
        $this->assertTrue($this->user->fresh()->hasRole('open-beta'));
    }

    public function test_admin_cannot_remove_admin_role_via_roles_endpoint(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->admin->id . '/roles', [
            'roles' => [],
        ]);

        $res->assertNoContent();
        $this->assertTrue($this->admin->fresh()->hasRole('admin'), 'Admin role should be preserved');
    }

    public function test_non_admin_cannot_update_user_roles(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson('/api/v1/admin/users/' . $this->admin->id . '/roles', [
            'roles' => [],
        ])->assertForbidden();
    }

    public function test_unauthenticated_cannot_update_user_roles(): void
    {
        $this->putJson('/api/v1/admin/users/' . $this->user->id . '/roles', [
            'roles' => [],
        ])->assertUnauthorized();
    }
}
