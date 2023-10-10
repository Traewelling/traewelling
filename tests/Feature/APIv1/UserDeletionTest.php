<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UserDeletionTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserAccountCanBeDeleted(): void {
        $alice      = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $this->assertDatabaseHas('users', ['id' => $alice->id]);

        $response = $this->deleteJson(
            uri:     '/api/v1/settings/account',
            data:    ['confirmation' => $alice->username],
        );
        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $alice->id]);
    }
}
