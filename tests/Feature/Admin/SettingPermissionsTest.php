<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class SettingPermissionsTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_cannot_remove_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));

        $this->actingAs($user)
            ->post(route('admin.users.update-roles'), ['id' => $user->id, 'roles' => []]);
        $user->refresh();

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_cannot_set_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $bob = User::factory()->create();
        $this->assertFalse($bob->hasRole('admin'));

        $this->actingAs($user)
            ->post(route('admin.users.update-roles'), ['id' => $bob->id, 'roles' => ['admin' => 1]]);
        $bob->refresh();

        $this->assertFalse($bob->hasRole('admin'));
    }

    public function test_set_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $bob = User::factory()->create();
        $this->assertFalse($bob->hasRole('open-beta'));

        $this->actingAs($user)
            ->post(route('admin.users.update-roles'), ['id' => $bob->id, 'roles' => ['open-beta' => 1]]);
        $bob->refresh();

        $this->assertTrue($bob->hasRole('open-beta'));
    }

    public function test_change_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $bob = User::factory()->create();
        $bob->assignRole('closed-beta');
        $bob->assignRole('open-beta');

        $this->actingAs($user)
            ->post(route('admin.users.update-roles'), ['id' => $bob->id, 'roles' => ['open-beta' => 1]]);
        $bob->refresh();

        $this->assertTrue($bob->hasRole('open-beta'));
        $this->assertFalse($bob->hasRole('closed-beta'));
    }
}
