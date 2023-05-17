<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
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
                         ->get(route('globaldashboard', ['language' => 'de']));
        $response->assertOk();
        $response->assertViewIs('dashboard');
        $this->assertDatabaseHas('users', ['username' => $user->username, 'language' => 'de']);
    }

    public function testLoggedInUsersWithSavedLanguageInProfile(): void {
        $user     = User::factory(['language' => 'de'])->create();
        $response = $this->actingAs($user)
                         ->get(route('settings'));
        $response->assertOk();
        $response->assertViewIs('settings.settings');
        $response->assertSee(__('menu.settings', [], 'de'));
    }
}
