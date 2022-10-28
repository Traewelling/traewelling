<?php

namespace Tests;

use Faker\Factory;

abstract class ApiTestCase extends TestCase
{
    protected $faker;
    public    $mockConsoleOutput = false;
    public    $token;

    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
        $this->artisan('db:seed');
        $this->faker = Factory::create();
    }
}
