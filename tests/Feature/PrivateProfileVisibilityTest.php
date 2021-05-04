<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PrivateProfileVisibilityTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $bob;
    private User $alice;
    private User $gertrud;

    /**
     * We want to test, if a private profile and/or status is visible to the user itself, a following user, a
     * not-following user and a guest. Therefore we create three users: bob (private profile), gertrud (following bob),
     * and alice (not following bob). Also Gertrud and bob will have their own seperate check-ins.
     */
    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
        $gertrudResult = json_decode($this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                                          ->json('GET', route('api.v0.getUser'))->getContent(), true);
        $this->gertrud = User::where('id', $gertrudResult['id'])->firstOrFail();
        $this->bob     = $this->createGDPRAckedUser();
        $this->alice   = $this->createGDPRAckedUser();
        UserController::createFollow($this->gertrud, $this->bob);
        $this->bob->update(['private_profile' => 'true']);
        $now = new DateTime("+2 day 12:45");
        $this->checkin("Frankfurt Hbf", $now, $this->bob);
    }

    /**
     * @test
     */
    public function view_profile_without_following() {
        $statuses = Status::where('user_id', $this->bob->id)->get();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.user', ['username' => $this->bob->username]));
        $response = json_decode($response->getContent(), true);
        $this->assertNotEquals(null, $response['statuses']);
    }
}
