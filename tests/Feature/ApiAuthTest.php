<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ApiAuthTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Create a user with the api and test the gdpr-intercept
     * @test
     */
    public function register_user_and_test_gdpr_middleware() {
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
        //$response = $this->json('POST', route('api.v0.auth.login'), $data);

        //Test GDPR-Middleware
        $userToken = json_decode($response->getContent())->token;
        $response  = $this->withHeaders(['Authorization' => 'Bearer ' . $userToken])
            ->json('GET', route('api.v0.getUser'));
        $response->assertStatus(406);
        $response->assertJsonStructure([
            'error',
            'updated',
            'german',
            'english']);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $userToken])
            ->json('PUT', route('api.v0.user.accept_privacy'));
        $response->assertStatus(202);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $userToken])
            ->get(route('api.v0.getUser'));
        $response->assertOk();
    }

    /**
     * Logout user via API.
     * @test
     */
    public function logout_user() {
        $this->loginGertrudAndAckGDPR();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('POST', route('api.v0.auth.logout'));
        $response->assertOk();
        $response->assertJson(['message' => 'Successfully logged out.']);
    }
}
