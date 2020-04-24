<?php

namespace Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class ApiTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $faker;
    public $mockConsoleOutput = false;
    public $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
        $this->artisan('db:seed');
        $this->faker = Factory::create();
    }


    public function loginGertrudAndAckGDPR()
    {
        $data = [
            'email'    => 'gertrud@traewelling.de',
            'password' => 'thisisnotasecurepassword123'
        ];

        $response    = $this->json('POST', route('api.v0.auth.login'), $data);
        $this->token = json_decode($response->getContent())->token;

        //Accept the privacy policy
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->json('PUT', route('api.v0.user.accept_privacy'));
    }
}
