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
    public function test_basic_test()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
