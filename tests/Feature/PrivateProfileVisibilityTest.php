<?php

namespace Tests\Feature;

use App\Exceptions\CheckInCollisionException;
use App\Http\Controllers\TransportController;
use App\Models\Follow;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Database\Seeders\HafasTripSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestCase;
use Tests\TestCase;

class PrivateProfileVisibilityTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $gertrud;
    private User $bob;
    private User $alice;

    /**
     * We want to test, if a private profile and/or status is visible to the user itself, a following user, a
     * not-following user and a guest. Therefore we create three users: bob (private profile), gertrud (following bob),
     * and alice (not following bob). Also Gertrud and bob will have their own seperate check-ins.
     */
    public function setUp(): void {
        parent::setUp();
        TrainStation::factory()->count(5)->create();
        HafasTrip::factory()->count(2)->create();
        $this->gertrud = $this->createGDPRAckedUser();
        $this->bob     = $this->createGDPRAckedUser();
        $this->alice   = $this->createGDPRAckedUser();
        $this->bob->update(['private_profile' => 'true']);
        $trip = HafasTrip::all()->random();
        try {
            TransportController::TrainCheckin(
                $trip->trip_id,
                $trip->origin,
                $trip->destination,
                '',
                $this->bob,
                0,
                0,
                0
            );
        } catch (CheckInCollisionException $e) {
            $this->fail($e);
        }
        Follow::create(['user_id' => $this->gertrud->id, 'follow_id' => $this->bob->id]);
    }

    /**
     * @test
     */
    public function view_profile_without_following() {
        $statuses = $this->bob->statuses()->get();
        $this->actingAs($this->gertrud);
    }
}
