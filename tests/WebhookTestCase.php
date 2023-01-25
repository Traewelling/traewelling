<?php

namespace Tests;

abstract class WebhookTestCase extends TestCase {
    public function setUp(): void {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
        $this->artisan('db:seed');
    }
}
