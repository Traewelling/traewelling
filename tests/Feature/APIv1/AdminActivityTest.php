<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AdminActivityTest extends ApiTestCase
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

    public function test_admin_can_list_activity(): void
    {
        activity()->causedBy($this->admin)->log('created');
        activity()->causedBy($this->admin)->log('updated');

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/activity');

        $res->assertOk();
        $res->assertJsonStructure(['data' => [
            '*' => ['id', 'causer', 'description', 'subjectType', 'subjectFullType', 'subjectId', 'changes', 'createdAt'],
        ]]);
    }

    public function test_non_admin_cannot_list_activity(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/activity')->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_activity(): void
    {
        $this->getJson('/api/v1/admin/activity')->assertUnauthorized();
    }

    public function test_admin_can_filter_activity_by_subject(): void
    {
        $target = User::factory()->create();

        activity()->causedBy($this->admin)->performedOn($target)->log('updated');
        activity()->causedBy($this->admin)->performedOn($this->user)->log('created');

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/activity?' . http_build_query([
            'subjectType' => User::class,
            'subjectId' => $target->id,
        ]));

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.subjectId', $target->id);
        $res->assertJsonPath('data.0.subjectType', 'User');
    }

    public function test_activity_response_structure_is_complete(): void
    {
        $target = User::factory()->create();
        activity()
            ->causedBy($this->admin)
            ->performedOn($target)
            ->withProperties(['attributes' => ['name' => 'New Name'], 'old' => ['name' => 'Old Name']])
            ->log('updated');

        $this->actAsApiUserWithAllScopes($this->admin);
        $res = $this->getJson('/api/v1/admin/activity');

        $res->assertOk();
        $res->assertJsonPath('data.0.causer.id', $this->admin->id);
        $res->assertJsonPath('data.0.causer.username', $this->admin->username);
        $res->assertJsonPath('data.0.description', 'updated');
        $res->assertJsonPath('data.0.subjectType', 'User');
        $res->assertJsonPath('data.0.subjectId', $target->id);
        $res->assertJsonPath('data.0.changes.old.name', 'Old Name');
        $res->assertJsonPath('data.0.changes.attributes.name', 'New Name');
    }
}
