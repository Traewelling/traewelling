<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\TrainCheckin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Tests\ApiTestCase;

class MutedProfileVisibilityTest extends ApiTestCase
{
    use RefreshDatabase;

    private stdClass $users;

    /**
     * We want to test, if a muted profile and/or status is visible to the user itself, a following user, a
     * not-following user and a guest. Therefore, we create three users: bob (muted profile), gertrud (following bob),
     * and alice (not following bob). Also, Gertrud and bob will have their own seperate check-ins.
     */
    public function setUp(): void {
        parent::setUp();
        $this->users = $this->createAliceBobAndGertrud();
    }

    /**
     * Watching a muted profile is characterized by being able to see ones statuses on a profile page.
     * If the statuses are returned as null, you're not allowed to see the statuses.
     *
     * @test
     */
    public function view_profile_of_muted_user() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the profile of bob? => yes
        Auth::logout();
        $guest = $this->get(route('profile', ['username' => $this->users->bob->user->username]));
        $guest->assertSuccessful();
        $this->assertGuest();
        $guest->assertDontSee(__('user.muted.heading'));

        // Can alice see the profile of bob? => no
        $alice = $this->actingAs($this->users->alice->user, 'web')
                      ->get(route('profile', ['username' => $this->users->bob->user->username]));
        $alice->assertSuccessful();
        $alice->assertSee(__('user.muted.heading'));
    }

    /**
     * @test
     */
    public function view_status_of_muted_user() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the status of bob? => yes
        Auth::logout();
        $guest = $this->get(route('statuses.get', ['id' => $this->users->bob->checkin['statusId']]));
        $this->assertGuest();
        $guest->assertSuccessful();

        // Can Bob see the status of bob? => yes
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET', route('api.v0.statuses.show', ['status' => $this->users->bob->checkin['statusId']]));
        $bob->assertSuccessful();
        $bob->assertJson(['id' => $this->users->bob->checkin['statusId']]);


        // Can Alice see the status of bob? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET',
                             route('api.v0.statuses.show', ['status' => $this->users->bob->checkin['statusId']])
                      );
        $alice->assertStatus(403);

        // Can Gertrud see the status of bob? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET',
                               route('api.v0.statuses.show', ['status' => $this->users->bob->checkin['statusId']])
                        );
        $alice->assertStatus(403);
        $bob->assertJson(['id' => $this->users->bob->checkin['statusId']]);
    }

    /**
     * If a user is muted, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the dashboard
     * @test
     */
    public function view_status_of_muted_user_on_global_dashboard() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the statuses of bob on the dashboard? => no, because they can't access the dashboard
        // Can Bob see the statuses of bob on the dashboard? => yes
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET', route('api.v0.statuses.index'));
        $bob->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $bob->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $bob->assertSuccessful();

        // Can Alice see the statuses of bob on the dashboard? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET', route('api.v0.statuses.index'));
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $alice->assertSuccessful();

        // Can Gertrud see the statuses of bob on the dashboard? => no
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.index'));
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $gertrud->assertSuccessful();
    }

    /**
     * If a user is muted, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the muted dashboard
     * @test
     */
    public function view_status_of_muted_user_on_dashboard() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the statuses of bob on the dashboard? => no, because they can't access the dashboard
        // Can Bob see the statuses of bob on the dashboard? => yes
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET', route('api.v0.statuses.index') . '?view=personal');
        $bob->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $bob->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $bob->assertSuccessful();

        // Can Alice see the statuses of bob on the dashboard? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET', route('api.v0.statuses.index') . '?view=personal');
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $alice->assertJsonMissing(["id" => $this->users->bob->checkin['statusId']]);
        $alice->assertSuccessful();

        // Can Gertrud see the statuses of bob on the dashboard? => no
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.index') . '?view=personal');
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $alice->assertJsonMissing(["id" => $this->users->bob->checkin['statusId']]);
        $gertrud->assertSuccessful();
    }

    /**
     * If a user is muted, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the en route page
     * @test
     */
    public function view_status_of_muted_user_on_en_route() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the statuses of bob on the dashboard? => yes
        Auth::logout();
        $guest = $this->get(route('statuses.active'));
        $this->assertGuest();
        $guest->assertSee($this->users->bob->user->username);

        // Can Bob see the statuses of bob on the dashboard? => yes
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET', route('api.v0.statuses.enroute'));
        $bob->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $bob->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $bob->assertSuccessful();

        // Can Alice see the statuses of bob on the dashboard? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET', route('api.v0.statuses.enroute'));
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $alice->assertSuccessful();

        // Can Gertrud see the statuses of bob on the dashboard? => no
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.enroute'));
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $gertrud->assertSuccessful();
    }


    /**
     * If a user is muted, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on event pages
     * @test
     */
    public function view_status_of_muted_user_on_event_pages() {
        $this->markTestSkipped('Test does not work properly and therefore was not executed. It must be rewritten.');

        // Can a guest see the statuses of bob on the dashboard? => yes
        Auth::logout();
        $guest = $this->get(route('statuses.byEvent', ['eventSlug' => $this->users->bob->checkin['event']['slug']]));
        $this->assertGuest();
        $guest->assertSee($this->users->bob->user->username);

        // Can Bob see the statuses of bob on the event page? => yes
        $bob = $this->actingAs($this->users->bob->user, 'web')
                    ->get(route('statuses.byEvent', ['eventSlug' => $this->users->bob->checkin['event']['slug']]));
        $bob->assertSee(["username" => $this->users->bob->user->username]);
        $bob->assertSuccessful();

        // Can Alice see the statuses of bob on the dashboard? => no
        $alice = $this->actingAs($this->users->alice->user, 'web')
                      ->get(route('statuses.byEvent', ['eventSlug' => $this->users->bob->checkin['event']['slug']]));
        $alice->assertDontSee(["username" => $this->users->bob->user->username]);
        $alice->assertSuccessful();

        // Can Gertrud see the statuses of bob on the dashboard? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'web')
                        ->get(route('statuses.byEvent', ['eventSlug' => $this->users->bob->checkin['event']['slug']]));
        $gertrud->assertDontSee(["username" => $this->users->bob->user->username]);
        $gertrud->assertSuccessful();
    }

    /**
     * This method creates thee users: Gertrud, Alice and Bob.
     * Bob is a muted profile, followed by Gertrud. Alice is a seperate user, following nobody.
     * Bob has one check in.
     *
     * @return stdClass
     * @throws \App\Exceptions\AlreadyFollowingException
     */
    public function createAliceBobAndGertrud(): stdClass {
        $data          = new stdClass();
        $data->bob     = new stdClass();
        $data->gertrud = new stdClass();
        $data->alice   = new stdClass();
        // Create Gertrud, Alice and Bob
        $data->bob->user     = User::factory(['name' => 'bob'])->create();
        $data->gertrud->user = User::factory(['name' => 'gertrud'])->create();
        $data->alice->user   = User::factory(['name' => 'alice'])->create();

        // Create new CheckIn for Bob
        $data->bob->checkin = TrainCheckin::factory(['user_id' => $data->bob->user->id])->create();

        // Make Gertrud follow bob and make bob's profile muted
        UserController::destroyFollow($data->alice->user, $data->bob->user);
        UserController::createFollow($data->gertrud->user, $data->bob->user);
        \App\Http\Controllers\Backend\UserController::muteUser($data->alice->user, $data->bob->user);
        \App\Http\Controllers\Backend\UserController::muteUser($data->gertrud->user, $data->bob->user);

        return $data;
    }
}
