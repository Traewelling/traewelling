<?php

namespace Tests\Feature\Frontend\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

class LoginTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testSuccessfulLogin(): void {
        $user = User::factory(['password' => Hash::make('password')])->create();
        $this->assertGuest();
        $response = $this->followingRedirects()
                         ->post(route('login', [
                             'login'    => $user->username,
                             'password' => 'password',
                         ]));
        $response->assertOk();
        $response->assertViewIs('dashboard');
        $this->assertAuthenticated();
    }

    public function testLoginWithWrongCredentials(): void {
        $user = User::factory(['password' => Hash::make('password')])->create();
        $this->assertGuest();
        $response = $this->post(route('login', [
            'login'    => $user->username,
            'password' => 'wrong password',
        ]));
        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function testTooManyLoginAttempts(): void {
        $user = User::factory()->create();
        $this->assertGuest();
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login', [
                'login'    => $user->username,
                'password' => 'wrong password',
            ]));
            $response->assertRedirectToRoute('login');
            $this->assertGuest();
        }
        $response = $this->post(route('login', [
            'login'    => $user->username,
            'password' => 'wrong password',
        ]));
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
