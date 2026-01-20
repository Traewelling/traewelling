<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserMuteTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_can_be_muted_once_and_then_unmuted(): void
    {
        $alice = User::factory()->create();
        Passport::actingAs($alice, ['*']);
        $bob = User::factory()->create();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id' => $alice->id,
            'muted_id' => $bob->id,
        ]);

        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertCreated();

        $this->assertDatabaseHas('user_mutes', [
            'user_id' => $alice->id,
            'muted_id' => $bob->id,
        ]);

        // Already muted -> expect 409
        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertConflict();

        // Now unmute user
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertOk();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id' => $alice->id,
            'muted_id' => $bob->id,
        ]);

        // Now unmute an already unmuted user and expect 409
        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]));
        $response->assertConflict();
    }

    public function test_non_existing_user_cant_be_muted(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->postJson(strtr('/api/v1/user/:userId/mute', [':userId' => 9999]));
        $response->assertNotFound();
    }

    public function test_non_existing_user_cant_be_unmuted(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->deleteJson(strtr('/api/v1/user/:userId/mute', [':userId' => 9999]));
        $response->assertNotFound();
    }
}
