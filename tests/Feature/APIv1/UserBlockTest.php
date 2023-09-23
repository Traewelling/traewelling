<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\UserController;
use App\Models\Event;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserBlockTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanBeBlockedOnceAndThenUnblocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $bob        = User::factory()->create();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Already blocked -> expect 409
        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();

        //Now unblock user
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Now unblock an already unblocked user and expect 409
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();
    }

    public function testNonExistingUserCantBeBlocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }

    public function testNonExistingUserCantBeUnblocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }

    public function testBlockedUserIsNotVisibleOnEventPage(): void {
        // due to issue#1755
        $alice = User::factory(['username' => 'alice'])->create();
        $bob   = User::factory(['username' => 'bob'])->create();
        $event = Event::factory()->create();

        // Alice and Bob check in to the event
        $aliceCheckin = TrainCheckin::factory(['user_id' => $alice->id])->create();
        $aliceCheckin->status->update(['event_id' => $event->id]);
        $bobCheckin = TrainCheckin::factory(['user_id' => $bob->id])->create();
        $bobCheckin->status->update(['event_id' => $event->id]);

        // alice should see both checkins
        $response = $this->actingAs($alice)
                         ->get(route('event', $event->slug));
        $response->assertOk();
        $response->assertSee($alice->username);
        $response->assertSee($bob->username);

        // Alice blocks Bob
        UserController::blockUser($alice, $bob);

        // alice should NOT see both checkins
        $response = $this->actingAs($alice)
                         ->get(route('event', $event->slug));
        $response->assertOk();
        $response->assertSee($alice->username);
        $response->assertDontSee($bob->username);
    }
}
