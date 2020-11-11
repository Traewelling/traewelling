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
     * Verify that there are statuses enroute
     * @test
     */
    public function statuses_enroute() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->get(route('api.v0.statuses.enroute'));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
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
     * Get Statuses for a specific event
     * @test
     */
    public function get_event_statuses() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->get(route('api.v0.statuses.event', ['statusId' => '1']));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        $firstTrain = json_decode($response->getContent(), true)['data'][0];
        $this->assertTrue($firstTrain['event']['id'] == 1);
        $this->assertTrue($firstTrain['event']['name'] == 'Jährliches Modelleisenbahntreffen ' . date('Y'));
        $this->assertTrue($firstTrain['event']['slug'] == 'Modellbahn' . date('y'));
        $this->assertTrue($firstTrain['event']['hashtag'] == 'Modellbahn' . date('y'));
        $this->assertTrue($firstTrain['event']['host'] == 'Modelleisenbahnfreunde Knuffingen');
        $this->assertTrue($firstTrain['event']['url'] == 'https://traewelling.de');
        $this->assertTrue($firstTrain['event']['trainstation'] != '');
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

    /**
     * Test the functionality of the create/destroy functionality of likes as well as the listing of likes for a status.
     * @test
     */
    public function check_like_dislike_listlike_functionality() {
        //First check if there are no likes for a status
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->get(route('api.v0.statuses.likes', ['statusId' => '1']));
        $response->assertOk();
        $this->assertTrue(empty(json_decode($response->getContent())->data));

        //Like the status
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->post(route('api.v0.statuses.like', ['statusId' => '1']));
        $response->assertOk();
        $this->assertTrue($response->getContent() == 'true');

        //Try to like.. the liked Status
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->post(route('api.v0.statuses.like', ['statusId' => '1']));
        $response->assertStatus(400);
        $this->assertTrue($response->getContent() == '{"error":false}');

        //check if the like has been created
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->get(route('api.v0.statuses.likes', ['statusId' => '1']));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent())->data));

        //Delete the like
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->delete(route('api.v0.statuses.like', ['statusId' => '1']));
        $response->assertOk();
        $this->assertTrue($response->getContent() == 'true');

        //check if the like has been deleted
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json'])
            ->get(route('api.v0.statuses.likes', ['statusId' => '1']));
        $response->assertOk();
        $this->assertTrue(empty(json_decode($response->getContent())->data));

    }
}
