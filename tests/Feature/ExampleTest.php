<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class ExampleTest extends FeatureTestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function testBasicTest() {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
