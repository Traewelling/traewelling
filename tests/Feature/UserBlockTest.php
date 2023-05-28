<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\TrainCheckin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBlockTest extends TestCase
{
    use RefreshDatabase;

    protected User  $alice;
    protected User  $bob;
    protected mixed $checkin;

    protected function setUp(): void {
        parent::setUp();
        $this->alice   = User::factory(['username' => 'alice', 'name' => 'Alice'])->create();
        $this->bob     = User::factory(['username' => 'bob', 'name' => 'Bob'])->create();
        $this->checkin = TrainCheckin::factory(['user_id' => $this->alice->id])->create();
    }

    private function aliceBlocksBob(): void {
        $this->actingAs($this->alice)
             ->post(route('user.block'), ['user_id' => $this->bob->id]);

        $this->assertEquals($this->bob->username, $this->alice->blockedUsers()->first()->username);
    }

    public function testStatusesAreBlocked(): void {
        $this->actingAs($this->bob)
             ->get('/status/' . $this->checkin->status_id)
             ->assertSee($this->alice->name);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get('/status/' . $this->checkin->status_id)
             ->assertForbidden();
    }

    public function testAlicesStatusIsHiddenFromBobsGlobalDashboard(): void {
        $this->actingAs($this->bob)
             ->get(route('globaldashboard'))
             ->assertSee($this->alice->username);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get(route('globaldashboard'))
             ->assertOk()
             ->assertDontSee($this->alice->username);
    }

    public function testBobsStatusIsHiddenFromAlicesGlobalDashboard(): void {
        TrainCheckin::factory(['user_id' => $this->bob->id])->create();

        $this->actingAs($this->alice)
             ->get(route('globaldashboard'))
             ->assertOk()
             ->assertSee(route('profile.picture', ['username' => $this->bob->username]))
             ->assertSee(route('profile.picture', ['username' => $this->alice->username]));

        $this->aliceBlocksBob();

        $this->actingAs($this->alice)
             ->get(route('globaldashboard'))
             ->assertOk()
            // Bob's name is present in the session bag due the "you successfully blocked bob' message. Instead, we
            // check that Bob's profile picture is not there, while Alice's picture is still there (from the checkin
            // in self::setUp).
             ->assertDontSee(route('profile.picture', ['username' => $this->bob->username]))
             ->assertSee(route('profile.picture', ['username' => $this->alice->username]));
    }

    public function testAlicesStatusIsHiddenFromBobsActiveJourneys(): void {
        $this->actingAs($this->bob)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertSee($this->checkin->destinationStation->name);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertDontSee($this->checkin->destinationStation->name);
    }

    public function testBobsStatusIsHiddenFromAlicesActiveJourneys(): void {
        TrainCheckin::factory(['user_id' => $this->bob->id])->create();

        $this->actingAs($this->alice)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertSee(route('profile.picture', ['username' => $this->bob->username]))
             ->assertSee(route('profile.picture', ['username' => $this->alice->username]));

        $this->aliceBlocksBob();

        $this->actingAs($this->alice)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertDontSee(route('profile.picture', ['username' => $this->bob->username]))
             ->assertSee(route('profile.picture', ['username' => $this->alice->username]));
    }

    public function testProfileShowsLimitedInfo(): void {
        $this->actingAs($this->bob)
             ->get(route('profile', ['username' => $this->alice->username]))
             ->assertSee('fa-route') // Profile statistics, i.e. showing the distance icon
             ->assertDontSee(__('profile.youre-blocked-text'));

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get(route('profile', ['username' => $this->alice->username]))
             ->assertDontSee('fa-route') // Profile statistics, i.e. showing the distance icon
             ->assertSee(__('profile.youre-blocked-text'));
    }

    public function testLikesAreDeleted(): void {
        $this->actingAs($this->bob)
             ->post(route('like.create'), ['statusId' => $this->checkin->status_id])
             ->assertStatus(201);

        $this->checkin = TrainCheckin::factory(['user_id' => $this->bob->id])->create();
        $this->actingAs($this->alice)
             ->post(route('like.create'), ['statusId' => $this->checkin->status_id])
             ->assertStatus(201);

        $this->assertEquals(2, Like::all()->count());

        $this->aliceBlocksBob();

        $this->assertEquals(0, Like::all()->count());
    }
}
