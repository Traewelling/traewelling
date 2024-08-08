<?php

namespace Tests\Feature\APIv1;

use App\Enum\MapProvider;
use App\Enum\MastodonVisibility;
use App\Enum\StatusVisibility;
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
        $this->assertTrue($response->json('data.likesEnabled'));
        $this->assertTrue($response->json('data.pointsEnabled'));
    }

    public function testUpdateProfileSettings(): void {
        $user = User::factory(['username' => 'old', 'name' => 'old'])->create();
        Passport::actingAs($user, ['*']);

        $this->assertEquals('old', $user->username);
        $this->assertEquals('old', $user->name);
        $this->assertFalse($user->private_profile);
        $this->assertFalse($user->prevent_index);
        $this->assertEquals(7, $user->privacy_hide_days);
        $this->assertEquals(StatusVisibility::PUBLIC, $user->default_status_visibility);
        $this->assertEquals(MastodonVisibility::UNLISTED, $user->socialProfile->mastodon_visibility);
        $this->assertNull($user->mapprovider);
        $this->assertTrue($user->likes_enabled);
        $this->assertTrue($user->points_enabled);
        $this->assertEquals(FriendCheckinSetting::FORBIDDEN, $user->friend_checkin);

        $response = $this->putJson(
            uri:  '/api/v1/settings/profile',
            data: [
                      'username'                => 'new',
                      'displayName'             => 'new',
                      'privateProfile'          => true,
                      'preventIndex'            => true,
                      'privacyHideDays'         => 1,
                      'defaultStatusVisibility' => StatusVisibility::PRIVATE->value,
                      'mapProvider'             => MapProvider::OPEN_RAILWAY_MAP->value,
                      'mastodonVisibility'      => MastodonVisibility::PUBLIC->value,
                      'likesEnabled'            => false,
                      'pointsEnabled'           => false,
                      'friendCheckin'           => FriendCheckinSetting::FRIENDS->value,
                  ],
        );
        $response->assertOk();

        $user = $user->refresh();

        $this->assertEquals('new', $user->username);
        $this->assertEquals('new', $user->name);
        $this->assertTrue($user->private_profile);
        $this->assertTrue($user->prevent_index);
        $this->assertEquals(1, $user->privacy_hide_days);
        $this->assertEquals(StatusVisibility::PRIVATE, $user->default_status_visibility);
        $this->assertEquals(MapProvider::OPEN_RAILWAY_MAP, $user->mapprovider);
        $this->assertEquals(MastodonVisibility::PUBLIC, $user->socialProfile->mastodon_visibility);
        $this->assertFalse($user->likes_enabled);
        $this->assertFalse($user->points_enabled);
        $this->assertEquals(FriendCheckinSetting::FRIENDS, $user->friend_checkin);
    }
}
