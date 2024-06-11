<?php

namespace Tests\Feature\APIv1;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

/**
 * Cases to consider:
 *                  |   |   |   |   |  Now  |   |   |   |   |   |
 *     Base:        |   |   |   |   |   ░   |   |   |   |   |   |
 *                  |   |   |   |   |   |   |   |   |   |   |   |
 *     Past:        |   |▓▓▓▓▓▓▓|   |   |   |   |   |   |   |   |
 *                  |   |   |   |   |   |   |   |   |   |   |   |
 *     Future:      |   |   |   |   |   |   |   |▓▓▓▓▓▓▓|   |   |
 *                  |   |   |   |   |   |   |   |   |   |   |   |
 *     Active:      |   |   |   |   |▓▓▓▓▓▓▓|   |   |   |   |   |
 *                  |   |   |   |   |   |   |   |   |   |   |   |
 */
class EventTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testEventSelection(): void {
        $pastEvent   = Event::factory(['checkin_start' => now()->subWeeks(2), 'checkin_end' => now()->subWeek()])->create();
        $activeEvent = Event::factory(['checkin_start' => now()->subDay(), 'checkin_end' => now()->addDay()])->create();
        $futureEvent = Event::factory(['checkin_start' => now()->addWeek(), 'checkin_end' => now()->addWeeks(2)])->create();

        // 1. Without any arguments, the API should return events that are currently active
        $response = $this->get('/api/v1/events');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($activeEvent->slug, $response->json('data.0.slug'));

        // 2. With a timestamp (for now) the result should be the same
        $response = $this->get('/api/v1/events?timestamp=' . now());
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($activeEvent->slug, $response->json('data.0.slug'));

        // 3. With a timestamp in the past, the API should return the past event only
        $response = $this->get('/api/v1/events?timestamp=' . now()->subDays(9));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($pastEvent->slug, $response->json('data.0.slug'));

        // 4. Same as 3, but with upcoming events (should return all three events)
        $response = $this->get('/api/v1/events?timestamp=' . now()->subDays(9) . '&upcoming=true');
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        //check correct order (ascending by begin date)
        $this->assertEquals($pastEvent->slug, $response->json('data.0.slug'));
        $this->assertEquals($activeEvent->slug, $response->json('data.1.slug'));
        $this->assertEquals($futureEvent->slug, $response->json('data.2.slug'));

        // 5. Future timestamp without any events
        $response = $this->get('/api/v1/events?timestamp=' . now()->addDays(3));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        // 6. Same as 5 and upcoming = false (should return nothing)
        $response = $this->get('/api/v1/events?timestamp=' . now()->addDays(3) . '&upcoming=false');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function testEventDetails(): void {
        $event = Event::factory()->create();

        $response = $this->get('/api/v1/event/' . $event->slug);
        $response->assertOk();
        $this->assertEventResource($response);

        $response = $this->get('/api/v1/event/' . $event->slug . '/details');
        $response->assertOk();
        $this->assertEventDetailsResource($response);

        $response = $this->get('/api/v1/event/' . $event->slug . '/statuses');
        $response->assertOk();
        //TODO: Test content for status endpoint
    }

    public function testEventSuggestion(): void {
        $this->actAsApiUserWithAllScopes();
        $response = $this->postJson('/api/v1/event');
        $response->assertUnprocessable();

        $response = $this->postJson('/api/v1/event', [
            'name'  => 'Testevent',
            'begin' => Carbon::tomorrow()->toDateString(),
            'end'   => Carbon::tomorrow()->addWeek()->toDateString(),
        ]);
        $response->assertCreated();
    }
}
