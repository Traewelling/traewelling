<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class StopoverTest extends ApiTestCase
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

    private function createStopover(): Stopover
    {
        return Trip::factory()->create()->stopovers->firstOrFail();
    }

    public function test_admin_can_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson("/api/v1/stopovers/{$stopover->id}")->assertNoContent();

        $this->assertDatabaseMissing('train_stopovers', ['id' => $stopover->id]);
    }

    public function test_non_admin_cannot_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/stopovers/{$stopover->id}")->assertForbidden();

        $this->assertDatabaseHas('train_stopovers', ['id' => $stopover->id]);
    }

    public function test_unauthenticated_cannot_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->deleteJson("/api/v1/stopovers/{$stopover->id}")->assertUnauthorized();
    }

    public function test_delete_returns_not_found_for_unknown_stopover(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson('/api/v1/stopovers/999999')->assertNotFound();
    }

    public function test_stopover_referenced_by_checkin_cannot_be_deleted(): void
    {
        $checkin = Checkin::factory()->create();
        $stopover = $checkin->originStopover;

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson("/api/v1/stopovers/{$stopover->id}")->assertConflict();

        $this->assertDatabaseHas('train_stopovers', ['id' => $stopover->id]);
    }
}
