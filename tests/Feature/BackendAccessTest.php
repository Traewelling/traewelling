<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendAccessTest extends TestCase
{

    use RefreshDatabase;

    public function testDefaultUserCantAccessBackend(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.dashboard'))
             ->assertForbidden();
    }

    public function testDefaultUserCantAccessActivity(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.activity'))
             ->assertForbidden();
    }

    public function testAdminCanAccessBackend(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
             ->get(route('admin.dashboard'))
             ->assertStatus(200);
    }

    public function testEventModeratorCanAccessBackend(): void {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
             ->get(route('admin.dashboard'))
             ->assertStatus(200);
    }

    public function testDefaultUserCantAccessUserDetailPage(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.users.user', ['id' => $user->id]))
             ->assertForbidden();
    }

    public function testAdminCanAccessUserDetailPage(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
             ->get(route('admin.users.user', ['id' => $user->id]))
             ->assertStatus(200);
    }

    public function testEventModeratorCantAccessUserDetailPage(): void {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
             ->get(route('admin.users.user', ['id' => $user->id]))
             ->assertForbidden();
    }

    public function testDefaultUserCantAccessEventSuggestions(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.events.suggestions'))
             ->assertForbidden();
    }

    public function testAdminCanAccessEventSuggestions(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user)
             ->get(route('admin.events.suggestions'))
             ->assertStatus(200);
    }

    public function testEventModeratorCanAccessEventSuggestions(): void {
        $user = User::factory()->create();
        $user->assignRole('event-moderator');
        $this->actingAs($user)
             ->get(route('admin.events.suggestions'))
             ->assertStatus(200);
    }
}
