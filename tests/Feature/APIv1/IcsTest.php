<?php

namespace Tests\Feature\APIv1;

use App\Models\IcsToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class IcsTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user, ['*']);
    }

    public function test_create_ics_token_without_name(): void
    {
        $response = $this->postJson(
            uri: '/api/v1/ics-tokens',
            data: [],
        );
        $response->assertStatus(422);
    }

    public function test_create_ics_token_with_name(): void
    {
        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $this->user->id,
            'name' => 'icsname',
        ]);

        $response = $this->postJson(
            uri: '/api/v1/ics-tokens',
            data: ['name' => 'icsname'],
        );
        $response->assertCreated();

        $this->assertDatabaseHas('ics_tokens', [
            'user_id' => $this->user->id,
            'name' => 'icsname',
        ]);
    }

    public function test_get_ics_tokens(): void
    {
        IcsToken::create([
            'user_id' => $this->user->id,
            'name' => 'icsname',
            'token' => Str::uuid()->toString(),
        ]);

        $response = $this->get(
            uri: '/api/v1/ics-tokens',
        );
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'token',
                    'name',
                    'createdAt',
                    'lastAccessed',
                ],
            ],
        ]);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_get_ics_tokens_with_no_tokens(): void
    {
        $response = $this->get(
            uri: '/api/v1/ics-tokens',
        );
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [],
        ]);
    }

    public function test__revoke_ics_token(): void
    {
        $token = IcsToken::create([
            'user_id' => $this->user->id,
            'name' => 'icsname',
            'token' => 'foobar',
        ]);

        $response = $this->deleteJson(
            uri: '/api/v1/ics-tokens/' . $token->id,
        );
        $response->assertStatus(204);

        $this->assertDatabaseMissing('ics_tokens', [
            'user_id' => $this->user->id,
            'name' => 'icsname',
        ]);

        $response = $this->deleteJson(
            uri: '/api/v1/ics-token/' . $token->id,
        );
        $response->assertStatus(404);
    }
}
