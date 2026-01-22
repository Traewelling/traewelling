<?php

namespace Tests\Feature\Frontend\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

class LoginTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_successful_login(): void
    {
        $user = User::factory(['password' => Hash::make('password')])->create();
        $this->assertGuest();
        $response = $this->followingRedirects()
            ->post(route('login', [
                'login' => $user->username,
                'password' => 'password',
            ]));
        $response->assertOk();
        $response->assertViewIs('vue.dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_with_wrong_credentials(): void
    {
        $user = User::factory(['password' => Hash::make('password')])->create();
        $this->assertGuest();
        $response = $this->post(route('login', [
            'login' => $user->username,
            'password' => 'wrong password',
        ]));
        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_too_many_login_attempts(): void
    {
        $user = User::factory()->create();
        $this->assertGuest();
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login', [
                'login' => $user->username,
                'password' => 'wrong password',
            ]));
            $response->assertRedirectToRoute('login');
            $this->assertGuest();
        }
        $response = $this->post(route('login', [
            'login' => $user->username,
            'password' => 'wrong password',
        ]));
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
