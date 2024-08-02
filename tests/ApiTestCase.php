<?php

namespace Tests;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;

abstract class ApiTestCase extends FeatureTestCase
{
    public $mockConsoleOutput = false;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install', ['--no-interaction' => true]);
        $this->artisan('passport:keys', ['--no-interaction' => true]);
    }

    protected function actAsApiUserWithAllScopes(User $user = null): void {
        if ($user === null) {
            $user = User::factory()->create();
        }
        Passport::actingAs($user, ['*']);
    }

    protected function assertUserResource(TestResponse $response): void {
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'displayName',
                                               'username',
                                               'profilePicture',
                                               'trainDistance',
                                               'trainDuration',
                                               'points',
                                               'mastodonUrl',
                                               'privateProfile',
                                               'preventIndex',
                                               'home',
                                               'language',
                                           ]
                                       ]);
    }

    protected function assertEventResource(TestResponse $response): void {
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'name',
                                               'slug',
                                               'hashtag',
                                               'host',
                                               'url',
                                               'begin',
                                               'end',
                                               'station' => [
                                                   'id',
                                                   'name',
                                                   'latitude',
                                                   'longitude',
                                                   'ibnr',
                                                   'rilIdentifier',
                                               ]
                                           ]
                                       ]);
    }

    protected function assertEventDetailsResource(TestResponse $response): void {
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'slug',
                                               'trainDistance',
                                               'trainDuration',
                                           ]
                                       ]);
    }
}
