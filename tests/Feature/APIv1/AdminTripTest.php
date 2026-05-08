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
}
