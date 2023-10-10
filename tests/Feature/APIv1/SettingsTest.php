<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Laravel\Passport\Passport;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SettingsTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testGetProfileSettings(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->get(
            uri:     '/api/v1/settings/profile',
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
