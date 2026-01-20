<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class IcsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_create_get_and_revoke_ics_token(): void
    {
        $user1 = User::factory()->create();
        Passport::actingAs($user1, ['*']);

        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $user1->id,
            'name' => 'icsname',
        ]);

        $response = $this->postJson(
            uri: '/api/v1/settings/ics-token',
            data: ['name' => 'icsname'],
        );
        $response->assertCreated();

        $this->assertDatabaseHas('ics_tokens', [
            'user_id' => $user1->id,
            'name' => 'icsname',
        ]);

        $response = $this->get(
            uri: '/api/v1/settings/ics-tokens',
        );
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'token',
                    'name',
                    'created',
                    'lastAccessed',
                ],
            ],
        ]);
        $this->assertCount(1, $response->json('data'));
        $tokenId = $response->json('data')[0]['id'];

        $response = $this->deleteJson(
            uri: '/api/v1/settings/ics-token',
            data: ['tokenId' => $tokenId],
        );
        $response->assertStatus(204);

        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $user1->id,
            'name' => 'icsname',
        ]);

        $response = $this->deleteJson(
            uri: '/api/v1/settings/ics-token',
            data: ['tokenId' => $tokenId],
        );
        $response->assertStatus(404);
    }
}
