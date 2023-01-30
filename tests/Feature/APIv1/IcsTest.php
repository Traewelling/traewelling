<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class IcsTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateGetAndRevokeIcsToken(): void {
        $user1      = User::factory()->create();
        $user1token = $user1->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $user1->id,
            'name'    => 'icsname',
        ]);

        $response = $this->postJson(
            uri:     '/api/v1/settings/ics-token',
            data:    ['name' => 'icsname',],
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('ics_tokens', [
            'user_id' => $user1->id,
            'name'    => 'icsname',
        ]);

        $response = $this->get(
            uri:     '/api/v1/settings/ics-tokens',
            headers: ['Authorization' => 'Bearer ' . $user1token]
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
                                               ]
                                           ]
                                       ]);
        $this->assertCount(1, $response->json('data'));
        $tokenId = $response->json('data')[0]['id'];

        $response = $this->deleteJson(
            uri:     '/api/v1/settings/ics-token',
            data:    ['tokenId' => $tokenId],
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertStatus(204);

        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $user1->id,
            'name'    => 'icsname',
        ]);

        $response = $this->deleteJson(
            uri:     '/api/v1/settings/ics-token',
            data:    ['tokenId' => $tokenId],
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertStatus(404);
    }
}
