<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Tests\ApiTestCase;

class PrivateProfileVisibilityTest extends ApiTestCase
{
    use RefreshDatabase;

    private stdClass $users;

    /**
     * We want to test, if a private profile and/or status is visible to the user itself, a following user, a
     * not-following user and a guest. Therefore we create three users: bob (private profile), gertrud (following bob),
     * and alice (not following bob). Also Gertrud and bob will have their own seperate check-ins.
     */
    public function setUp(): void {
        parent::setUp();
        $this->users = $this->createAliceBobAndGertrud();
    }

    /**
     * Watching a private profile is characterized by being able to see ones statuses on a profile page.
     * If the statuses are returned as null, you're not allowed to see the statuses.
     *
     * @test
     */
    public function view_profile_of_private_user() {
        // Can a guest see the profile of bob? => no
        Auth::logout();
        $guest = $this->get(route('account.show', ['username' => $this->users->bob->user->username]));
        $guest->assertSuccessful();
        $this->assertGuest();
        $guest->assertSee(__('profile.private-profile-text'));

        // Can Bob see the profile of bob? => yes
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET', route('api.v0.user', ['username' => $this->users->bob->user->username]));
        $bob->assertSuccessful();
        $bob = json_decode($bob->getContent(), true);
        $this->assertNotEquals(null, $bob['statuses'], 'Bob cannot see his own statuses!');

        // Can Alice see the profile of Bob? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET', route('api.v0.user', ['username' => $this->users->bob->user->username]));
        $alice->assertSuccessful();
        $alice = json_decode($alice->getContent(), true);
        $this->assertEquals(null, $alice['statuses'], 'Alice can see the statuses of bob!');

        // Can Gertrud see the profile of bob? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.user', ['username' => $this->users->bob->user->username]));
        $gertrud->assertSuccessful();
        $gertrud = json_decode($gertrud->getContent(), true);
        $this->assertNotEquals(null, $gertrud['statuses'], 'Gertrud cannot see the statuses bob!');
    }

    /**
     * @test
     */
    public function view_status_of_private_user() {
        // Can a guest see the status of bob? => no
        Auth::logout();
        $guest = $this->get(route('statuses.get', ['id' => $this->users->bob->checkin['statusId']]));
        $this->assertGuest();
        $guest->assertStatus(403);

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
        $gertrud->assertSuccessful();
        $bob->assertJson(['id' => $this->users->bob->checkin['statusId']]);
    }

    /**
     * If a user is private, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the dashboard
     * @test
     */
    public function view_status_of_private_user_on_global_dashboard() {
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

        // Can Gertrud see the statuses of bob on the dashboard? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.index'));
        $gertrud->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $gertrud->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $gertrud->assertSuccessful();
    }

    /**
     * If a user is private, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the private dashboard
     * @test
     */
    public function view_status_of_private_user_on_private_dashboard() {
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

        // Can Gertrud see the statuses of bob on the dashboard? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.index') . '?view=personal');
        $gertrud->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $gertrud->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $gertrud->assertSuccessful();
    }

    /**
     * If a user is private, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on the en route page
     * @test
     */
    public function view_status_of_private_user_on_en_route() {
        // Can a guest see the statuses of bob on the dashboard? => no
        Auth::logout();
        $guest = $this->get(route('statuses.active'));
        $this->assertGuest();
        $guest->assertDontSee($this->users->bob->user->username);

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

        // Can Gertrud see the statuses of bob on the dashboard? => yes
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET', route('api.v0.statuses.enroute'));
        $gertrud->assertJsonFragment(["username" => $this->users->bob->user->username]);
        $gertrud->assertJsonFragment(["id" => $this->users->bob->checkin['statusId']]);
        $gertrud->assertSuccessful();
    }


    /**
     * If a user is private, only authorized (not explicitly authenticated) users should be able to see their statuses
     * on event pages
     * @test
     */
    public function view_status_of_private_user_on_event_pages() {
        // Can a guest see the statuses of bob on the dashboard? => no
        Auth::logout();
        $guest = $this->get(route('statuses.byEvent', ['eventSlug' => $this->users->bob->checkin['event']['slug']]));
        $this->assertGuest();
        $guest->assertDontSee($this->users->bob->user->username);

        // Can Bob see the statuses of bob on the event page? => no, because we were lazy
        $bob = $this->actingAs($this->users->bob->user, 'api')
                    ->json('GET',
                           route('api.v0.statuses.event', ['statusId' => $this->users->bob->checkin['event']['id']])
                    );
        $bob->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $bob->assertSuccessful();

        // Can Alice see the statuses of bob on the dashboard? => no
        $alice = $this->actingAs($this->users->alice->user, 'api')
                      ->json('GET',
                             route('api.v0.statuses.event', ['statusId' => $this->users->bob->checkin['event']['id']])
                      );
        $alice->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $alice->assertSuccessful();

        // Can Gertrud see the statuses of bob on the dashboard? => no, because we were lazy
        $gertrud = $this->actingAs($this->users->gertrud->user, 'api')
                        ->json('GET',
                               route('api.v0.statuses.event', ['statusId' => $this->users->bob->checkin['event']['id']])
                        );
        $gertrud->assertJsonMissing(["username" => $this->users->bob->user->username]);
        $gertrud->assertSuccessful();
    }

    /**
     * This method creates thee users: Gertrud, Alice and Bob.
     * Bob is a private profile, followed by Gertrud. Alice is a seperate user, following nobody.
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
        $data->bob->user                     = $this->createGDPRAckedUser();
        $data->bob->user->privacy_ack_at     = now();
        $data->gertrud->user                 = $this->createGDPRAckedUser();
        $data->gertrud->user->privacy_ack_at = now();
        $data->alice->user                   = $this->createGDPRAckedUser();
        $data->alice->user->privacy_ack_at   = now();
        $data->bob->user->save();
        $data->gertrud->user->save();
        $data->alice->user->save();

        // Create new CheckIn for Bob
        $now                = new DateTime("-30min");
        $data->bob->checkin = $this->checkin("Frankfurt Hbf", $now, $data->bob->user, 1);

        // Make Gertrud follow bob and make bob's profile private
        UserController::destroyFollow($data->alice->user, $data->bob->user);
        UserController::createFollow($data->gertrud->user, $data->bob->user);
        $data->bob->user->update(['private_profile' => 'true']);

        $this->assertTrue($data->bob->user->private_profile);

        return $data;

    }
}
