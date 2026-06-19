<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SettingPermissionsTest extends ApiTestCase
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

    public function test_cannot_remove_admin_role(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->assertTrue($this->admin->hasRole('admin'));
        $res = $this->putJson('/api/v1/admin/users/' . $this->admin->id . '/roles', [
            'roles' => [],
        ]);

        $this->admin->refresh();

        $this->assertTrue($this->admin->hasRole('admin'));
    }

    public function test_cannot_set_admin_role(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/roles', [
            'roles' => ['admin'],
        ]);

        $this->assertFalse($this->user->hasRole('admin'));
        $this->user->refresh();

        $this->assertFalse($this->user->hasRole('admin'));
    }

    public function test_set_role(): void
    {
        $this->assertFalse($this->user->hasRole('open-beta'));

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/roles', [
            'roles' => ['open-beta'],
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->hasRole('open-beta'));
    }

    public function test_change_role(): void
    {
        $this->user->assignRole('closed-beta');
        $this->user->assignRole('open-beta');

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson('/api/v1/admin/users/' . $this->user->id . '/roles', [
            'roles' => ['open-beta'],
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->hasRole('open-beta'));
        $this->assertFalse($this->user->hasRole('closed-beta'));
    }
}
