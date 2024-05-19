<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Http\Controllers\Backend\WebFingerController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class WebFingerTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testWebFingerFullUrl(): void {
        $serverName = WebFingerController::getServerName(config('app.url'));

        $user = User::factory()->create();
        $route = route('profile', ['username' => $user->username]);
        $response = $this->get('/.well-known/webfinger?resource=' . $route);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/jrd+json');

        $avatarUrl = ProfilePictureController::getUrl($user);
        $response->assertJson([
            'subject' => 'acct:' . $user->username . '@' . $serverName,
            'aliases' => [
                route('profile', ['username' => $user->username]),
            ],
            'links' => [
                [
                    'rel' => 'http://webfinger.net/rel/profile-page',
                    'type' => 'text/html',
                    'href' => route('profile', ['username' => $user->username]),
                ],
                [
                    'rel' => 'http://webfinger.net/rel/avatar',
                    'type' => 'image/png',
                    'href' => $avatarUrl,
                ],
            ],
        ]);
    }

    public function testWebFingerShortUri(): void {
        $serverName = WebFingerController::getServerName(config('app.url'));

        $user = User::factory()->create();
        $response = $this->get('/.well-known/webfinger?resource=' . 'acct:' . $user->username . '@' . $serverName);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/jrd+json');

        $avatarUrl = ProfilePictureController::getUrl($user);
        $response->assertJson([
            'subject' => 'acct:' . $user->username . '@' . $serverName,
            'aliases' => [
                route('profile', ['username' => $user->username]),
            ],
            'links' => [
                [
                    'rel' => 'http://webfinger.net/rel/profile-page',
                    'type' => 'text/html',
                    'href' => route('profile', ['username' => $user->username]),
                ],
                [
                    'rel' => 'http://webfinger.net/rel/avatar',
                    'type' => 'image/png',
                    'href' => $avatarUrl,
                ],
            ],
        ]);
    }
}
