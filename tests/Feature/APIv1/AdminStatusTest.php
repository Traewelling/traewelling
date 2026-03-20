<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\StatusVisibility;
use App\Models\Checkin;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AdminStatusTest extends ApiTestCase
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

    public function test_admin_can_list_statuses(): void
    {
        Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/statuses');

        $res->assertOk();
        $res->assertJsonStructure(['data' => [['id', 'user', 'checkin', 'visibility', 'business']]]);
    }

    public function test_non_admin_cannot_list_statuses(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/statuses')->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_statuses(): void
    {
        $this->getJson('/api/v1/admin/statuses')->assertUnauthorized();
    }

    public function test_index_filter_by_user_query(): void
    {
        $targetUser = User::factory()->create(['name' => 'Findable User', 'username' => 'findable']);
        $status = Status::factory(['user_id' => $targetUser->id])
            ->has(Checkin::factory(['user_id' => $targetUser->id]))
            ->create();
        Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/statuses?userQuery=Findable');

        $res->assertOk();
        $ids = collect($res->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($status->id));
        $this->assertCount(1, $ids);
    }

    public function test_admin_can_get_single_status(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson("/api/v1/admin/statuses/{$status->id}");

        $res->assertOk();
        $res->assertJsonPath('data.id', $status->id);
        $res->assertJsonStructure(['data' => [
            'id', 'body', 'visibility', 'business',
            'moderation_notes', 'lock_visibility', 'hide_body',
            'user', 'checkin', 'stopovers',
        ]]);
    }

    public function test_non_admin_cannot_get_single_status(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson("/api/v1/admin/statuses/{$status->id}")->assertForbidden();
    }

    public function test_unauthenticated_cannot_get_single_status(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();
        $this->getJson("/api/v1/admin/statuses/{$status->id}")->assertUnauthorized();
    }

    public function test_get_single_status_returns_404_for_unknown_id(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson('/api/v1/admin/statuses/99999999')->assertNotFound();
    }

    public function test_show_includes_stopovers(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson("/api/v1/admin/statuses/{$status->id}");

        $res->assertOk();
        $this->assertNotNull($res->json('data.stopovers'));
        $this->assertNotEmpty($res->json('data.stopovers'));
    }

    public function test_admin_can_update_status(): void
    {
        $status = Status::factory(['visibility' => StatusVisibility::PUBLIC])
            ->has(Checkin::factory())
            ->create();

        $checkin = $status->checkin;
        $originStationId = $checkin->originStopover->train_station_id;
        $destinationStationId = $checkin->destinationStopover->train_station_id;

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->putJson("/api/v1/admin/statuses/{$status->id}", [
            'origin' => $originStationId,
            'destination' => $destinationStationId,
            'body' => 'Updated body',
            'visibility' => StatusVisibility::PRIVATE->value,
            'business' => 0,
            'moderation_notes' => 'Test moderation note',
            'lock_visibility' => true,
            'hide_body' => false,
        ]);

        $res->assertOk();
        $res->assertJsonPath('data.visibility', StatusVisibility::PRIVATE->value);
        $this->assertDatabaseHas('statuses', [
            'id' => $status->id,
            'moderation_notes' => 'Test moderation note',
            'lock_visibility' => true,
        ]);
    }

    public function test_non_admin_cannot_update_status(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();
        $checkin = $status->checkin;

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/admin/statuses/{$status->id}", [
            'origin' => $checkin->originStopover->train_station_id,
            'destination' => $checkin->destinationStopover->train_station_id,
            'visibility' => StatusVisibility::PUBLIC->value,
        ])->assertForbidden();
    }

    public function test_unauthenticated_cannot_update_status(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();

        $this->putJson("/api/v1/admin/statuses/{$status->id}", [
            'origin' => 1,
            'destination' => 2,
            'visibility' => 0,
        ])->assertUnauthorized();
    }

    public function test_update_returns_404_for_unknown_id(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->putJson('/api/v1/admin/statuses/99999999', [
            'origin' => 1,
            'destination' => 2,
            'visibility' => 0,
        ])->assertNotFound();
    }

    public function test_update_validates_required_fields(): void
    {
        $status = Status::factory()->has(Checkin::factory())->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->putJson("/api/v1/admin/statuses/{$status->id}", [])->assertUnprocessable();
    }
}
