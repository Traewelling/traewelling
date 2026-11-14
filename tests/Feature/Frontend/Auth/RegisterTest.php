<?php

namespace Tests\Feature\Frontend\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class RegisterTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_successful_registration(): void
    {
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'alice123']);
        $response = $this->followingRedirects()
            ->post(route('register', [
                'username' => 'alice123',
                'name' => 'Alice',
                'email' => 'alice@traewelling.de',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]));
        $response->assertOk();
        $response->assertViewIs('vue.spa');
        $this->assertDatabaseHas('users', ['username' => 'alice123']);
    }

    public function test_successful_registration_via_json(): void
    {
        $this->postJson(route('register'), [
            'username' => 'alice123',
            'name' => 'Alice',
            'email' => 'alice@traewelling.de',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'alice123']);
    }

    public function test_registration_with_invalid_data_via_json(): void
    {
        $this->postJson(route('register'), [
            'username' => 'alice 123',
            'name' => 'Alice',
            'email' => 'alice@traewelling.de',
            'password' => 'password',
            'password_confirmation' => 'other password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'password']);
        $this->assertGuest();
    }
}
