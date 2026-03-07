<?php

namespace Tests\Feature\Transport;

use App\Http\Controllers\Backend\Stats\TransportStatsController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class TransportStatsTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_trips_by_speed_returns_correct_order(): void
    {
        $user = User::factory()->create();
        $from = Carbon::now()->subYear();
        $to = Carbon::now()->addYear();

        // fast trip: 200 km in 120 min → 100 km/h
        $fastCheckin = Checkin::factory()->create([
            'user_id' => $user->id,
            'distance' => 200_000,
            'duration' => 120,
        ]);

        // medium trip: 100 km in 120 min → 50 km/h
        $mediumCheckin = Checkin::factory()->create([
            'user_id' => $user->id,
            'distance' => 100_000,
            'duration' => 120,
        ]);

        // slow trip: 30 km in 120 min → 15 km/h
        $slowCheckin = Checkin::factory()->create([
            'user_id' => $user->id,
            'distance' => 30_000,
            'duration' => 120,
        ]);

        // zero-duration trip should be excluded
        Checkin::factory()->create([
            'user_id' => $user->id,
            'distance' => 500_000,
            'duration' => 0,
        ]);

        $fastest = TransportStatsController::getTripsBySpeed($user, $from, $to, 'desc', 3);
        $slowest = TransportStatsController::getTripsBySpeed($user, $from, $to, 'asc', 3);

        // fastest first, slowest last
        $this->assertEquals($fastCheckin->id, $fastest->first()->id);
        $this->assertEquals($slowCheckin->id, $fastest->last()->id);

        // slowest first, fastest last
        $this->assertEquals($slowCheckin->id, $slowest->first()->id);
        $this->assertEquals($fastCheckin->id, $slowest->last()->id);

        // regression: fastest trip must have higher speed than slowest trip (issue #4199)
        $this->assertGreaterThan($slowest->first()->speed, $fastest->first()->speed);
    }

    public function test_trips_by_speed_excludes_zero_duration(): void
    {
        $user = User::factory()->create();
        $from = Carbon::now()->subYear();
        $to = Carbon::now()->addYear();

        Checkin::factory()->create([
            'user_id' => $user->id,
            'distance' => 100_000,
            'duration' => 0,
        ]);

        $result = TransportStatsController::getTripsBySpeed($user, $from, $to, 'desc');

        $this->assertCount(0, $result);
    }

    public function test_most_liked_statuses(): void
    {
        $user = User::factory()->create();
        $likingUser1 = User::factory()->create();
        $likingUser2 = User::factory()->create();
        $likingUser3 = User::factory()->create();

        // Create a checkin with 3 likes
        $checkin1 = Checkin::factory()->create(['user_id' => $user->id]);
        StatusBackend::createLike($likingUser1, $checkin1->status);
        StatusBackend::createLike($likingUser2, $checkin1->status);
        StatusBackend::createLike($likingUser3, $checkin1->status);

        // Create a checkin with 2 likes
        $checkin2 = Checkin::factory()->create(['user_id' => $user->id]);
        StatusBackend::createLike($likingUser1, $checkin2->status);
        StatusBackend::createLike($likingUser2, $checkin2->status);

        // Create a checkin with no like (should not be in the result)
        $checkin3 = Checkin::factory()->create(['user_id' => $user->id]);

        // get stats and check result
        $mostLiked = TransportStatsController::getMostLikedStatus($user, Carbon::now()->subYear(), Carbon::now()->addYear());

        $this->assertEquals(3, $mostLiked->filter(static function ($row) use ($checkin1) {
            return $row['status']->id === $checkin1->id;
        })->first()['likeCount']);

        $this->assertEquals(2, $mostLiked->filter(static function ($row) use ($checkin2) {
            return $row['status']->id === $checkin2->id;
        })->first()['likeCount']);

        // check that checkin3 is not in the result
        $this->assertNull($mostLiked->filter(static function ($row) use ($checkin3) {
            return $row['status']->id === $checkin3->id;
        })->first());
    }
}
