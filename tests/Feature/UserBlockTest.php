<?php

namespace Tests\Feature;

use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Like;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserBlockTest extends TestCase
{
    use RefreshDatabase;

    protected User  $alice;
    protected User  $bob;
    protected mixed $checkin;

    protected function setUp(): void {
        parent::setUp();

        $this->alice = $this->createGDPRAckedUser(['username' => 'alice']);
        $this->bob   = $this->createGDPRAckedUser(['username' => 'bob']);

        $this->checkin = $this->checkin('Frankfurt Hbf', Carbon::parse('-10min'), $this->alice);
    }

    private function aliceBlocksBob(): void {
        $this->actingAs($this->alice)
             ->post(route('user.block'), ['user_id' => $this->bob->id]);

        $this->assertEquals($this->bob->username, $this->alice->blockedUsers()->first()->username);
    }

    public function testStatusesAreBlocked(): void {
        $this->actingAs($this->bob)
             ->get('/status/' . $this->checkin['statusId'])
             ->assertSee($this->alice->name);

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get('/status/' . $this->checkin['statusId'])
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
        $this->checkin('Frankfurt Hbf', Carbon::parse('-10min'), $this->bob);

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
        $trainCheckin          = TrainCheckin::where('status_id', $this->checkin['statusId'])->firstOrFail();
        $trainCheckin->arrival = Carbon::parse('+10min');
        $trainCheckin->save();

        $this->actingAs($this->bob)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertSee('Frankfurt');

        $this->aliceBlocksBob();

        $this->actingAs($this->bob)
             ->get(route('statuses.active'))
             ->assertOk()
             ->assertDontSee('Frankfurt');
    }

    public function testBobsStatusIsHiddenFromAlicesActiveJourneys(): void {
        $this->checkin         = $this->checkin('Frankfurt Hbf', Carbon::parse('-10min'), $this->bob);
        $trainCheckin          = TrainCheckin::where('status_id', $this->checkin['statusId'])->firstOrFail();
        $trainCheckin->arrival = Carbon::parse('+10min');
        $trainCheckin->save();

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
        //Create like for already given checkin
        StatusBackend::createLike($this->bob, Status::find($this->checkin['statusId']));

        //Create a second checkin and like it
        $this->checkin = $this->checkin('Frankfurt Hbf', Carbon::parse('-10min'), $this->bob);
        StatusBackend::createLike($this->alice, Status::find($this->checkin['statusId']));

        $this->assertEquals(2, Like::all()->count());

        $this->aliceBlocksBob();

        $this->assertEquals(0, Like::all()->count());
    }
}
