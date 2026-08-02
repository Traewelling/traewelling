<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    private function url(Stopover $stopover): string
    {
        return "/api/v1/trips/{$stopover->trip->uuid}/stopovers/{$stopover->uuid}";
    }

    public function test_admin_can_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson($this->url($stopover))->assertNoContent();

        $this->assertDatabaseMissing('train_stopovers', ['id' => $stopover->id]);
    }

    public function test_non_admin_cannot_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson($this->url($stopover))->assertForbidden();

        $this->assertDatabaseHas('train_stopovers', ['id' => $stopover->id]);
    }

    public function test_unauthenticated_cannot_delete_stopover(): void
    {
        $stopover = $this->createStopover();

        $this->deleteJson($this->url($stopover))->assertUnauthorized();
    }

    public function test_delete_returns_not_found_for_unknown_stopover(): void
    {
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/" . Str::uuid())->assertNotFound();
    }

    public function test_stopover_referenced_by_checkin_cannot_be_deleted(): void
    {
        $checkin = Checkin::factory()->create();
        $stopover = $checkin->originStopover;

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson($this->url($stopover))->assertConflict();

        $this->assertDatabaseHas('train_stopovers', ['id' => $stopover->id]);
    }
}
