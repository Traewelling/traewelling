<?php

namespace Tests\Feature\APIv1;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class EventTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testEventDetails(): void {
        Event::factory()->create();
        $response = $this->get('/api/v1/events');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $eventResource = $response->json('data')[0];

        $response = $this->get('/api/v1/activeEvents', [
            'Authorization' => 'Bearer ' . $this->getTokenForTestUser(),
        ]);
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response = $this->get('/api/v1/event/' . $eventResource['slug']);
        $response->assertOk();
        $this->assertEventResource($response);

        $response = $this->get('/api/v1/event/' . $eventResource['slug'] . '/details');
        $response->assertOk();
        $this->assertEventDetailsResource($response);

        $response = $this->get('/api/v1/event/' . $eventResource['slug'] . '/statuses');
        $response->assertOk();
        //TODO: Test content for status endpoint
    }

    public function testEventSuggestion(): void {
        $response = $this->postJson('/api/v1/event', [], [
            'Authorization' => 'Bearer ' . $this->getTokenForTestUser(),
        ]);
        $response->assertUnprocessable();

        $response = $this->postJson('/api/v1/event', [
            'name'  => 'Testevent',
            'begin' => Carbon::tomorrow()->toDateString(),
            'end'   => Carbon::tomorrow()->addWeek()->toDateString(),
        ],                          [
                                        'Authorization' => 'Bearer ' . $this->getTokenForTestUser(),
                                    ]);
        $response->assertCreated();
    }
}
