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

    private User  $user;
    private User  $admin;
    private mixed $postData;

    public function setUp(): void {
        parent::setUp();
        $this->user  = $this->createGDPRAckedUser();
        $this->admin = $this->createAdminUser();

        $this->postData = [
            // For the EventSuggestion POST
            'name'                 => 'eventName',
            'host'                 => 'host',
            'begin'                => '2023-01-03T00:00:00',
            'end'                  => '2023-01-08T00:00:00',

            // For the Event POST
            'suggestionId'         => 1337, // will be replaced later
            'hashtag'              => '#eventName',
            'nearest_station_name' => 'Hannover Hbf',
            'url'                  => 'https://example.com',
        ];

        $this->createSuggestion($this->user);
    }

    public function testSuggestionDeny(): void {
        $this->actingAs($this->admin);

        // Check if admin sees the suggestion
        $res = $this->get('/admin/events/suggestions');
        $res->assertSee($this->postData['name']);

        // Admin denies the event suggestion
        $res = $this->followingRedirects()
                    ->post('/admin/events/suggestions/deny', ['id' => $this->postData['suggestionId']]);
        $res->assertSee('alert-success');

        // List is empty after declining
        $res = $this->get('/admin/events/suggestions');
        $res->assertOk();
        $res->assertSee('text-danger');

        // User gets notification
        $notification = $this->user->notifications()->first();
        $this->assertFalse($notification->data['accepted']);
    }

    public function testSuggestionAccept(): void {
        $this->actingAs($this->admin);

        // Check if admin sees the suggestion
        $res = $this->get('/admin/events/suggestions');
        $res->assertSee($this->postData['name']);

        // Admin can load the form
        $res = $this->get('/admin/events/suggestions/accept/' . $this->postData['suggestionId']);
        $res->assertSee($this->postData["name"]);

        // Location Data for the Event Location
        Http::fake(['/locations*' => Http::response([self::HANNOVER_HBF])]);

        // Admin accepts the event
        $res = $this->post('/admin/events/suggestions/accept', $this->postData);
        $res->assertRedirect();
        $this->followRedirects($res)->assertSee('alert-success');

        // User gets notification
        $notification = $this->user->notifications()->first();
        $this->assertTrue($notification->data["accepted"]);
    }

    private function createSuggestion(User $user): void {
        $this->actingAs($user)->get('/events');

        $res = $this->followingRedirects()
                    ->post(route('events.suggest'), $this->postData);

        $this->postData['suggestionId'] = EventSuggestion::first()->id;
    }
}

