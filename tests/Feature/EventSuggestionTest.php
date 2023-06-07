<?php

namespace Tests\Feature;

use App\Models\EventSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EventSuggestionTest extends TestCase
{
    use RefreshDatabase;

    private User            $user;
    private User            $admin;
    private EventSuggestion $eventSuggestion;

    public function setUp(): void {
        parent::setUp();
        $this->user            = User::factory()->create();
        $this->admin           = User::factory(['role' => 10])->create();
        $this->eventSuggestion = EventSuggestion::factory(['user_id' => $this->user->id])->create();
    }

    public function testSuggestionDeny(): void {
        $this->actingAs($this->admin);

        // Check if admin sees the suggestion
        $res = $this->get('/admin/events/suggestions');
        $res->assertSee($this->eventSuggestion->name);

        // Admin denies the event suggestion
        $res = $this->followingRedirects()
                    ->post('/admin/events/suggestions/deny', ['id' => $this->eventSuggestion->id]);
        $res->assertSee('alert-success');

        // List is empty after declining
        $res = $this->get('/admin/events/suggestions');
        $res->assertOk();
        $res->assertSee('text-danger');

        // User gets notification
        $notification = $this->user->notifications->first();
        $this->assertFalse($notification->data['accepted']);
    }

    public function testSuggestionAccept(): void {
        $this->actingAs($this->admin);

        // Check if admin sees the suggestion
        $res = $this->get('/admin/events/suggestions');
        $res->assertSee($this->eventSuggestion->name);

        // Admin can load the form
        $res = $this->get('/admin/events/suggestions/accept/' . $this->eventSuggestion->id);
        $res->assertSee($this->eventSuggestion->name);

        // Location Data for the Event Location
        Http::fake(['/locations*' => Http::response([self::HANNOVER_HBF])]);

        // Admin accepts the event
        $res = $this->followingRedirects()
                    ->post('/admin/events/suggestions/accept', [
                        'suggestionId'         => $this->eventSuggestion->id,
                        'name'                 => $this->eventSuggestion->name,
                        'hashtag'              => 'somehashtag',
                        'host'                 => $this->eventSuggestion->host,
                        'url'                  => 'https://traewelling.de/events',
                        'nearest_station_name' => 'Hannover Hbf',
                        'begin'                => $this->eventSuggestion->begin,
                        'end'                  => $this->eventSuggestion->end,
                    ]);
        $res->assertSee('alert-success');

        // User gets notification
        $notification = $this->user->notifications()->first();
        $this->assertTrue($notification->data['accepted']);
    }
}
