<?php

namespace Tests;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;

abstract class ApiTestCase extends TestCase
{
    public $mockConsoleOutput = false;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install', ['--no-interaction' => true]);
        $this->artisan('passport:keys', ['--no-interaction' => true]);
    }

    protected function actAsApiUserWithAllScopes(): void {
        Passport::actingAs(User::factory()->create(), ['*']);
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
