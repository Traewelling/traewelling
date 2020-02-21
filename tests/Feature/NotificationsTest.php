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
    

    /** @test */
    public function following_a_user_should_spawn_a_notification() {
        // Given: Users Alice and Bob
        $alice = $this->user;
        $bob = factory(User::class)->create(); $this->actingAs($bob)->post('/gdpr-ack');

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
            'type' => "App\\Notifications\\UserFollowed",
            'notifiable_type' => "App\\User",
            'notifiable_id' => (string) $bob->id
        ]);
    }

    /** @test */
    public function unfollowing_bob_should_remove_the_notification() {
        // Given: Users Alice and Bob and Alice follows Bob
        $alice = $this->user;
        $bob = factory(User::class)->create(); $this->actingAs($bob)->post('/gdpr-ack');
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // When: Alice unfollows Bob
        $unfollow = $this->actingAs($alice)->post(route('follow.destroy'), ['follow_id' => $bob->id]);
        $unfollow->assertStatus(200);

        // Then: Bob should not see that notification anymore
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0); // no follow no more
    }
}
