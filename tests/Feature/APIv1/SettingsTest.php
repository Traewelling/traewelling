<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SettingsTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testGetProfileSettings(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->get(
            uri:     '/api/v1/settings/profile',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'username',
                                               'displayName',
                                               'profilePicture',
                                               //...
                                           ]
                                       ]);
    }
}
