<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\UserController;
use App\Models\Event;
use App\Models\Checkin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserBlockTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanBeBlockedOnceAndThenUnblocked(): void {
        $alice      = User::factory()->create();
        Passport::actingAs($alice, ['*']);
        $bob        = User::factory()->create();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        $response = $this->postJson(strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]));
        $response->assertCreated();

        $this->assertDatabaseHas('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Already blocked -> expect 409
        $response = $this->postJson(strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]));
        $response->assertConflict();

        //Now unblock user
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]));
        $response->assertOk();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Now unblock an already unblocked user and expect 409
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]));
        $response->assertConflict();
    }

    public function testNonExistingUserCantBeBlocked(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->postJson(strtr('/api/v1/user/:userId/block', [':userId' => 9999]));
        $response->assertNotFound();
    }

    public function testNonExistingUserCantBeUnblocked(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->deleteJson(strtr('/api/v1/user/:userId/block', [':userId' => 9999]));
        $response->assertNotFound();
    }

    public function testBlockedUserIsNotVisibleOnEventPage(): void {
        // due to issue#1755
        $alice = User::factory(['username' => 'alice'])->create();
        $bob   = User::factory(['username' => 'bob'])->create();
        $event = Event::factory()->create();

        // Alice and Bob check in to the event
        $aliceCheckin = Checkin::factory(['user_id' => $alice->id])->create();
        $aliceCheckin->status->update(['event_id' => $event->id]);
        $bobCheckin = Checkin::factory(['user_id' => $bob->id])->create();
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
