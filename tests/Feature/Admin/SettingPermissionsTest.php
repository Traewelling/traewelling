<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingPermissionsTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testCannotRemoveAdminRole(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));

        $this->actingAs($user)
             ->post(route('admin.users.update-roles'), ['id' => $user->id, 'roles' => []]);
        $user->refresh();

        $this->assertTrue($user->hasRole('admin'));
    }

    public function testCannotSetAdminRole(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $bob = User::factory()->create();
        $this->assertFalse($bob->hasRole('admin'));

        $this->actingAs($user)
             ->post(route('admin.users.update-roles'), ['id' => $bob->id, 'roles' => ['admin' => 1]]);
        $bob->refresh();

        $this->assertFalse($bob->hasRole('admin'));
    }

    public function testSetRole(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $bob = User::factory()->create();
        $this->assertFalse($bob->hasRole('open-beta'));

        $this->actingAs($user)
             ->post(route('admin.users.update-roles'), ['id' => $bob->id, 'roles' => ['open-beta' => 1]]);
        $bob->refresh();

        $this->assertTrue($bob->hasRole('open-beta'));
    }



    public function testChangeRole(): void {
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
