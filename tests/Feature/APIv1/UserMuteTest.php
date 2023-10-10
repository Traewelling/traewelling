<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserMuteTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanBeMutedOnceAndThenUnmuted(): void {
        $alice = User::factory()->create();
        Passport::actingAs($alice, ['*']);
        $bob = User::factory()->create();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertCreated();

        $this->assertDatabaseHas('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        //Already muted -> expect 409
        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertConflict();

        //Now unmute user
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertOk();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        //Now unmute an already unmuted user and expect 409
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertConflict();
    }

    public function testNonExistingUserCantBeMuted(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => 9999]));
        $response->assertNotFound();
    }

    public function testNonExistingUserCantBeUnmuted(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => 9999]));
        $response->assertNotFound();
    }
}
