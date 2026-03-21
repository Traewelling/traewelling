<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class BackendAccessTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_default_user_cant_access_backend(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_default_user_cant_access_activity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('admin.activity'))
            ->assertForbidden();
    }

    public function test_admin_can_access_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_event_moderator_can_access_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_default_user_cant_access_user_detail_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('admin.users.show', ['id' => $user->id]))
            ->assertForbidden();
    }

    public function test_admin_can_access_user_detail_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
            ->get(route('admin.users.show', ['id' => $user->id]))
            ->assertStatus(200);
    }

    public function test_event_moderator_cant_access_user_detail_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
            ->get(route('admin.users.show', ['id' => $user->id]))
            ->assertForbidden();
    }

    public function test_default_user_cant_access_event_suggestions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/admin/event-suggestions')
            ->assertForbidden();
    }

    public function test_admin_can_access_event_suggestions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
            ->get('/admin/event-suggestions')
            ->assertStatus(200);
    }

    public function test_event_moderator_can_access_event_suggestions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
            ->get('/admin/event-suggestions')
            ->assertStatus(200);
    }
}
