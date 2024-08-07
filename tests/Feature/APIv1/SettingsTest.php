<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class SettingsTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testGetProfileSettings(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        $response = $this->get(
            uri: '/api/v1/settings/profile',
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

    public function testUpdateProfileSettings(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->putJson(
            uri:  '/api/v1/settings/profile',
            data: [
                      'username'      => 'test',
                      'displayName'   => 'test',
                      'likesEnabled'  => true,
                      'pointsEnabled' => true,
                  ],
        );
        $response->assertOk();

        $user = $user->refresh();

        self::assertEquals('test', $user->username);
        self::assertEquals('test', $user->name);
        self::assertTrue($user->likes_enabled);
        self::assertTrue($user->points_enabled);
    }
}
