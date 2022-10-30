<?php

namespace Tests;

use Faker\Factory;
use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends TestCase
{
    protected $faker;
    public    $mockConsoleOutput = false;
    public    $token;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
        $this->artisan('db:seed');
        $this->faker = Factory::create();
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
                                               'twitterUrl',
                                               'mastodonUrl',
                                               'privateProfile',
                                               'privacyHideDays',
                                               'preventIndex',
                                               'role',
                                               'home',
                                               'dbl',
                                               'language',
                                           ]
                                       ]);
    }
}
