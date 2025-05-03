<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class LanguageTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testBrowserHasValidLanguageCode(): void {
        $this->assertGuest();
        $response = $this->get(
            uri:     route('login'),
            headers: ['Accept-Language' => 'de'],
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'de'));
        $this->assertGuest();
    }

    public function testBrowserHasInvalidLanguageCode(): void {
        $this->assertGuest();
        $response = $this->get(
            uri:     route('login'),
            headers: ['Accept-Language' => 'zz'],
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'en'));
        $this->assertGuest();
    }

    public function testRequestHasValidLanguageCode(): void {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login', ['language' => 'de']),
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'de'));
        $this->assertGuest();
    }

    public function testRequestHasInvalidLanguageCode(): void {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login', ['language' => 'zz']),
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $this->assertGuest();
    }

    public function testRequestHasValidLanguageCodeWithLoggedInUser(): void {
        $user     = User::factory()->create();
        $this->assertDatabaseMissing('users', ['username' => $user->username, 'language' => 'de']);
        $response = $this->actingAs($user)
                         ->get(route('dashboard', ['language' => 'de']));
        $response->assertOk();
        $response->assertViewIs('dashboard');
        $this->assertDatabaseHas('users', ['username' => $user->username, 'language' => 'de']);
    }

    public function testLoggedInUsersWithSavedLanguageInProfile(): void {
        $user     = User::factory(['language' => 'de'])->create();
        $response = $this->actingAs($user)
                         ->get(route('settings.profile'));
        $response->assertOk();
        $response->assertViewIs('settings.profile');
        $response->assertSee(__('menu.settings', [], 'de'));
    }
}
