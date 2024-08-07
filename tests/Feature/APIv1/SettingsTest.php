<?php

namespace Tests\Feature\APIv1;

use App\Enum\User\FriendCheckinSetting;
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

        $this->assertEquals(FriendCheckinSetting::FORBIDDEN->value, $response->json('data.friendCheckin'));
    }

    public function testUpdateProfileSettings(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->assertEquals(FriendCheckinSetting::FORBIDDEN, $user->friend_checkin);

        $response = $this->putJson(
            uri:  '/api/v1/settings/profile',
            data: [
                      'username'      => 'test',
                      'displayName'   => 'test',
                      'likesEnabled'  => true,
                      'pointsEnabled' => true,
                      'friendCheckin' => FriendCheckinSetting::FRIENDS->value,
                  ],
        );
        $response->assertOk();

        $user = $user->refresh();

        $this->assertEquals('test', $user->username);
        $this->assertEquals('test', $user->name);
        $this->assertTrue($user->likes_enabled);
        $this->assertTrue($user->points_enabled);
        $this->assertEquals(FriendCheckinSetting::FRIENDS, $user->friend_checkin);
    }
}
