<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AdminTripTest extends ApiTestCase
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

    public function test_admin_can_get_trip_index(): void
    {
        Trip::factory()->count(3)->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/trips');

        $res->assertOk();
        $res->assertJsonCount(3, 'data');
        $res->assertJsonStructure(['data' => [
            '*' => ['id', 'tripId', 'origin', 'destination', 'lineName', 'checkinsCount'],
        ]]);
    }

    public function test_non_admin_cannot_get_trip_index(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/trips')->assertForbidden();
    }

    public function test_unauthenticated_cannot_get_trip_index(): void
    {
        $this->getJson('/api/v1/admin/trips')->assertUnauthorized();
    }

    public function test_admin_can_get_trip_details(): void
    {
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson("/api/v1/admin/trips/{$trip->id}");

        $res->assertOk();
        $res->assertJsonPath('data.id', $trip->id);
        $res->assertJsonStructure(['data' => [
            'id', 'tripId', 'category', 'lineName', 'stopovers', 'statuses',
        ]]);
    }

    public function test_non_admin_cannot_get_trip_details(): void
    {
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson("/api/v1/admin/trips/{$trip->id}")->assertForbidden();
    }

    public function test_unauthenticated_cannot_get_trip_details(): void
    {
        $trip = Trip::factory()->create();
        $this->getJson("/api/v1/admin/trips/{$trip->id}")->assertUnauthorized();
    }

    public function test_get_nonexistent_trip_returns_404(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson('/api/v1/admin/trips/99999999')->assertNotFound();
    }

    public function test_admin_can_dispatch_reroute_job(): void
    {
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson("/api/v1/admin/trips/{$trip->id}/reroute")->assertNoContent();
    }

    public function test_non_admin_cannot_dispatch_reroute_job(): void
    {
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson("/api/v1/admin/trips/{$trip->id}/reroute")->assertForbidden();
    }

    public function test_unauthenticated_cannot_dispatch_reroute_job(): void
    {
        $trip = Trip::factory()->create();
        $this->postJson("/api/v1/admin/trips/{$trip->id}/reroute")->assertUnauthorized();
    }

    public function test_reroute_nonexistent_trip_returns_404(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson('/api/v1/admin/trips/99999999/reroute')->assertNotFound();
    }
}
