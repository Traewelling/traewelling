<?php

namespace Feature\Transport;

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

    public function testMostLikedStatuses(): void {
        $user        = User::factory()->create();
        $likingUser1 = User::factory()->create();
        $likingUser2 = User::factory()->create();
        $likingUser3 = User::factory()->create();

        //Create a checkin with 3 likes
        $checkin1 = Checkin::factory()->create(['user_id' => $user->id]);
        StatusBackend::createLike($likingUser1, $checkin1->status);
        StatusBackend::createLike($likingUser2, $checkin1->status);
        StatusBackend::createLike($likingUser3, $checkin1->status);

        //Create a checkin with 2 likes
        $checkin2 = Checkin::factory()->create(['user_id' => $user->id]);
        StatusBackend::createLike($likingUser1, $checkin2->status);
        StatusBackend::createLike($likingUser2, $checkin2->status);

        //Create a checkin with no like (should not be in the result)
        $checkin3 = Checkin::factory()->create(['user_id' => $user->id]);

        //get stats and check result
        $mostLiked = TransportStatsController::getMostLikedStatus($user, Carbon::now()->subYear(), Carbon::now()->addYear());

        $this->assertEquals(3, $mostLiked->filter(static function($row) use ($checkin1) {
            return $row['status']->id === $checkin1->id;
        })->first()['likeCount']);

        $this->assertEquals(2, $mostLiked->filter(static function($row) use ($checkin2) {
            return $row['status']->id === $checkin2->id;
        })->first()['likeCount']);

        //check that checkin3 is not in the result
        $this->assertNull($mostLiked->filter(static function($row) use ($checkin3) {
            return $row['status']->id === $checkin3->id;
        })->first());
    }
}
