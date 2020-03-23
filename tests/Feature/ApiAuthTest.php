<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ApiAuthTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Create a user with the api
     * @test
     */
    public function register_user() {
        $data = [
            'username'         => 'Gertrud456',
            'name'             => 'Gertrud',
            'email'            => 'gertrud1@traewelling.de',
            'password'         => 'thisisnotasecurepassword123',
            'confirm_password' => 'thisisnotasecurepassword123'
        ];

        $response = $this->json('POST', route('api.v0.auth.signup'), $data);
        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'message']);
    }

    /**
     * Logout user via API.
     * @test
     */
    public function logout_user() {
        $this->loginGertrudAndAckGDPR();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('POST', route('api.v0.auth.logout'));
        $response->assertOk();
        $response->assertJson(['message' => 'Successfully logged out.']);
    }

}
