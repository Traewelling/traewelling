<?php

namespace Tests;

use Illuminate\Testing\TestResponse;

abstract class ApiTestCase extends TestCase
{
    public          $mockConsoleOutput = false;
    private ?string $token             = null;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
    }

    protected function getTokenForTestUser(): string {
        if ($this->token === null) {
            $username = 'john_doe' . time() . rand(111, 999);
            $response = $this->postJson('/api/v1/auth/signup', [
                'username'              => $username,
                'name'                  => 'John Doe',
                'email'                 => $username . '@example.com',
                'password'              => 'thisisnotasecurepassword123',
                'password_confirmation' => 'thisisnotasecurepassword123',
            ]);
            $response->assertCreated();
            $response->assertJsonStructure([
                                               'data' => [
                                                   'token',
                                                   'expires_at',
                                               ]
                                           ]);
            $this->token = $response->json('data.token');

            $response = $this->put('/api/v1/settings/acceptPrivacy', [], [
                'Authorization' => 'Bearer ' . $this->token,
            ]);
            $response->assertNoContent();
        }
        return $this->token;
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
