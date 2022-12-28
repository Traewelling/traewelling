<?php

namespace Tests\Feature;

use App\Models\TrainCheckin;
use App\Models\User;
use Carbon\Carbon;
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

    public function testStatusIsHiddenFromActiveJourneys(): void {
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

}
