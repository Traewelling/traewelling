<?php

namespace Tests;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends TestCase
{
    public $mockConsoleOutput = false;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
    }

    protected function getTokenForTestUser(): string {
        return User::factory()->create()->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
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
                                               'trainSpeed',
                                               'points',
                                               'mastodonUrl',
                                               'privateProfile',
                                               'preventIndex',
                                               'role',
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
