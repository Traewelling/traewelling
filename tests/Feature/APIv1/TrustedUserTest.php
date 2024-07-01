<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class TrustedUserTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testStoreAndDeleteTrustedUserForYourself(): void {
        $user        = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$user->id}/trusted", ['user_id' => $trustedUser->id]);
        $response->assertCreated();
        $this->assertDatabaseHas('trusted_users', ['user_id' => $user->id, 'trusted_id' => $trustedUser->id]);

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$user->id}/trusted");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $trustedUser->id]);

        // untrust user
        $response = $this->deleteJson("/api/v1/user/{$user->id}/trusted/{$trustedUser->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('trusted_users', ['user_id' => $user->id, 'trusted_id' => $trustedUser->id]);
    }

    public function testStoreAndDeleteTrustedUserForOtherUsersAsNonAdmin(): void {
        $user        = User::factory()->create();
        $truster     = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$truster->id}/trusted", ['user_id' => $trustedUser->id]);
        $response->assertForbidden();

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$truster->id}/trusted");
        $response->assertForbidden();

        // untrust user
        $response = $this->deleteJson("/api/v1/user/{$truster->id}/trusted/{$trustedUser->id}");
        $response->assertForbidden();
    }

    public function testStoreAndDeleteTrustedUserForOtherUsersAsAdmin(): void {
        $user        = User::factory()->create()->assignRole('admin');
        $truster     = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$truster->id}/trusted", ['user_id' => $trustedUser->id]);
        $response->assertCreated();
        $this->assertDatabaseHas('trusted_users', ['user_id' => $truster->id, 'trusted_id' => $trustedUser->id]);

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$truster->id}/trusted");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $trustedUser->id]);

        // untrust user
        $response = $this->deleteJson("/api/v1/user/{$truster->id}/trusted/{$trustedUser->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('trusted_users', ['user_id' => $truster->id, 'trusted_id' => $trustedUser->id]);
    }
}

