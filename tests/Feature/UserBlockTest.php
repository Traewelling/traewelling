<?php

namespace Tests\Feature;

use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Checkin;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class UserBlockTest extends FeatureTestCase
{
    use RefreshDatabase;

    protected User $alice;

    protected User $bob;

    protected mixed $checkin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alice = User::factory(['username' => 'alice', 'name' => 'Alice'])->create();
        $this->bob = User::factory(['username' => 'bob', 'name' => 'Bob'])->create();
        $this->checkin = Checkin::factory(['user_id' => $this->alice->id])->create();
    }

    private function aliceBlocksBob(): void
    {
        $this->actingAs($this->alice)
            ->post(route('user.block'), ['user_id' => $this->bob->id]);

        $this->assertEquals($this->bob->username, $this->alice->blockedUsers()->first()->username);
    }

    public function test_statuses_are_blocked(): void
    {
        $this->actingAs($this->bob)
            ->getJson('/api/v1/status/' . $this->checkin->status_id)
            ->assertSee($this->alice->name);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
            ->getJson('/api/v1/status/' . $this->checkin->status_id)
            ->assertForbidden();
    }

    public function test_alices_status_is_hidden_from_bobs_active_journeys(): void
    {
        $this->actingAs($this->bob)
            ->getJson('/api/v1/statuses/')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $this->checkin->status_id,
            ]);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
            ->getJson('/api/v1/statuses/')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $this->checkin->status_id,
            ]);
    }

    public function test_bobs_status_is_hidden_from_alices_active_journeys(): void
    {
        Checkin::factory(['user_id' => $this->bob->id])->create();

        $this->actingAs($this->alice)
            ->getJson('/api/v1/statuses/')
            ->assertOk()
            ->assertJsonFragment(['username' => $this->bob->username])
            ->assertJsonFragment(['username' => $this->alice->username]);

        $this->aliceBlocksBob();

        $this->actingAs($this->alice)
            ->getJson('/api/v1/statuses/')
            ->assertOk()
            ->assertJsonMissing(['username' => $this->bob->username])
            ->assertJsonFragment(['username' => $this->alice->username]);
    }

    public function test_profile_shows_limited_info(): void
    {
        $this->actingAs($this->bob)
            ->get(route('profile', ['username' => $this->alice->username]))
            ->assertDontSee(__('profile.youre-blocked-text'));

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
            ->get(route('profile', ['username' => $this->alice->username]))
            ->assertSee(__('profile.youre-blocked-text'));
    }

    public function test_likes_are_deleted(): void
    {
        // Create like for already given checkin
        StatusBackend::createLike($this->bob, Status::find($this->checkin->status_id));

        // Create a second checkin and like it
        $this->checkin = Checkin::factory(['user_id' => $this->bob->id])->create();
        StatusBackend::createLike($this->alice, Status::find($this->checkin->status_id));

        $this->assertEquals(2, Like::all()->count());

        $this->aliceBlocksBob();

        $this->assertEquals(0, Like::all()->count());
    }
}
