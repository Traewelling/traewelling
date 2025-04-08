<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserSearchTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserSearch(): void {
        $alice   = User::factory(['name' => 'Alice', 'username' => 'alice'])->create();
        $bob     = User::factory(['name' => 'Bob', 'username' => 'bob'])->create();
        $charlie = User::factory(['name' => 'Charlie', 'username' => 'charlie'])->create();

        Passport::actingAs($alice, ['*']);

        // 1. Test Search in Path (username AND displayname) - legacy
        $response = $this->getJson('/api/v1/user/search/charlie');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $charlie->id]);

        // 2. Test Search for username in query
        $response = $this->getJson('/api/v1/user/search?username=Charlie');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $charlie->id]);

        // 3. Test Search for displayname in query
        $response = $this->getJson('/api/v1/user/search?name=charlie');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $charlie->id]);

        // 4. Test without any parameters (should fail)
        $response = $this->getJson('/api/v1/user/search');
        $response->assertBadRequest();
    }
}
