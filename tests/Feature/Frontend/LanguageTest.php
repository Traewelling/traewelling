<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class LanguageTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_browser_has_valid_language_code(): void
    {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login'),
            headers: ['Accept-Language' => 'de'],
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'de'));
        $this->assertGuest();
    }

    public function test_browser_has_invalid_language_code(): void
    {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login'),
            headers: ['Accept-Language' => 'zz'],
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'en'));
        $this->assertGuest();
    }

    public function test_request_has_valid_language_code(): void
    {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login', ['language' => 'de']),
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee(__('user.forgot-password', [], 'de'));
        $this->assertGuest();
    }

    public function test_request_has_invalid_language_code(): void
    {
        $this->assertGuest();
        $response = $this->get(
            uri: route('login', ['language' => 'zz']),
        );
        $response->assertOk();
        $response->assertViewIs('auth.login');
        $this->assertGuest();
    }

    public function test_request_has_valid_language_code_with_logged_in_user(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseMissing('users', ['username' => $user->username, 'language' => 'de']);
        $response = $this->actingAs($user)
            ->get(route('dashboard', ['language' => 'de']));
        $response->assertOk();
        $response->assertViewIs('dashboard');
        $this->assertDatabaseHas('users', ['username' => $user->username, 'language' => 'de']);
    }

    public function test_logged_in_users_with_saved_language_in_profile(): void
    {
        $user = User::factory(['language' => 'de'])->create();
        $response = $this->actingAs($user)
            ->get(route('settings.profile'));
        $response->assertOk();
        $response->assertViewIs('settings.profile');
        $response->assertSee(__('menu.settings', [], 'de'));
    }
}
