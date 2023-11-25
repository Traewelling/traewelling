<?php

namespace Tests\Feature;

use App\Dto\Coordinate;
use App\Models\User;
use App\Objects\LineSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendAccessTest extends TestCase
{

    use RefreshDatabase;

    public function testDefaultUserCantAccessBackend(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.dashboard'))
             ->assertStatus(403);
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
             ->assertStatus(403);
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
                ->assertStatus(403);
    }

    public function testDefaultUserCantAccessEventSuggestions(): void {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->get(route('admin.events.suggestions'))
             ->assertStatus(403);
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
