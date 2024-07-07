<?php

namespace Tests\Feature\Frontend\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class RegisterTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testSuccessfulRegistration(): void {
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'alice123']);
        $response = $this->followingRedirects()
                         ->post(route('register', [
                             'username'              => 'alice123',
                             'name'                  => 'Alice',
                             'email'                 => 'alice@traewelling.de',
                             'password'              => 'password',
                             'password_confirmation' => 'password',
                         ]));
        $response->assertOk();
        $response->assertViewIs('legal.privacy-interception');
        $this->assertDatabaseHas('users', ['username' => 'alice123']);
    }
}
