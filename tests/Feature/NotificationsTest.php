<?php

namespace Tests\Feature;

use App\Http\Controllers\TransportController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Like;
use App\Status;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationsTest extends TestCase {
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $response = $this->actingAs($this->user)
                        ->post('/gdpr-ack');
    }

    /**
     * This is mostly copied from Checkin Test and exactly copied from ExportTripsTest.
     */
    protected function checkin($stationname, $ibnr, \DateTime $now) {
        $trainStationboard = TransportController::TrainStationboard($stationname, $now->format('U'), 'express');

        $countDepartures = count($trainStationboard['departures']);
        if($countDepartures == 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationname?");
            return;
        }
        
        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while (
              (isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled
              )
              || count($trainStationboard['departures'][$i]->remarks) != 0
            ) {
            $i++;
            if ($i == $countDepartures) {
                $this->markSkippedForMissingDependecy("Unable to find unbroken train. Is it stormy in $stationname?");
                return;
            }
        }
        $departure = $trainStationboard['departures'][$i];
        CheckinTest::isCorrectHafasTrip($departure, $now);
        
        // Third: Get the trip information
        $trip = TransportController::TrainTrip(
            $departure->tripId,
            $departure->line->name,
            $departure->stop->location->id
        );
        
        // WHEN: User tries to check-in
        $response = $this->actingAs($this->user)
            ->post(route('trains.checkin'), [
                'body' => 'Example Body',
                'tripID' => $departure->tripId,
                'start' => $ibnr,
                'destination' => $trip['stopovers'][0]['stop']['location']['id'],
            ]);
    }
    
    /** @test */
    public function likes_appear_in_notifications() {
        // Given: There is a likable status
        $now = new \DateTime("+2 day 8:00");
        $this->checkin("Essen Hbf", "8000098", $now);
        
        $status = $this->user->statuses->first();

        // When: Someone (e.g. the user itself) likes the status
        $like = $this->actingAs($this->user)
                     ->post(route('like.create'), ['statusId' => $status->id]);
        $like->assertStatus(201); // Created
        
        // Then: The like appears in the notifications
        $notifications = $this->actingAs($this->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one like
        $notifications->assertJsonFragment([
            'type' => "App\\Notifications\\StatusLiked",
            'notifiable_type' => "App\\User",
            'notifiable_id' => (string) $this->user->id
        ]);
    }

    /** @test */
    public function removed_likes_dont_appear_in_notifications() {
        // Given: There is a likable status
        $now = new \DateTime("+2 day 8:00");
        $this->checkin("Essen Hbf", "8000098", $now);
        
        $status = $this->user->statuses->first();
        $like = $this->actingAs($this->user)
                     ->post(route('like.create'), ['statusId' => $status->id]);
        $like->assertStatus(201); // Created
        
        // When: The like is removed
        Like::first()->delete();

        // Then: It does not show up in the notifications anymore
        $notifications = $this->actingAs($this->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0); // no likes left
    }
    
}
