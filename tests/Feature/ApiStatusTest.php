<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ApiStatusTest extends ApiTestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
    }

    /**
     * Test the statuses route
     * @test
     */
    public function get_global_statuses() {
        //test global dashboard
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->get(route('api.v0.statuses.index'));
        $response->assertOk();

        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        //test page 2
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->json('GET', route('api.v0.statuses.index'), ['page' => 2]);
        $response->assertOk();

        $this->assertFalse(empty(json_decode($response->getContent(), true)));

        //test gertrud posts
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            //->get(route('api.v0.statuses.index'), ['view' => 'user', 'username' => 'gertrud123']);
                         ->json('GET', route('api.v0.statuses.index'), ['view' => 'user', 'username' => 'gertrud123']);
        $response->assertOk();


        $this->assertFalse(empty(json_decode($response->getContent(), true)));
    }

    /**
     * Test single status
     * @test
     */
    public function get_status_by_id() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->get(route('api.v0.statuses.show', ['status' => '1']));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
    }
}
