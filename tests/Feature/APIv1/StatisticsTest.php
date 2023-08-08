<?php

namespace Tests\Feature\APIv1;

use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class StatisticsTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testDailyStatistics(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $checkin   = TrainCheckin::factory(['user_id' => $user->id])->create();

        $response = $this->get(
            uri:     '/api/v1/statistics/daily/' . $checkin->departure->format('Y-m-d'),
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'statuses',
                                               'polylines',
                                               'totalDistance',
                                               'totalDuration',
                                               'totalPoints',
                                           ]
                                       ]);
        $this->assertNull($response->json('data.polylines'));
    }

    public function testDailyStatisticsWithPolylines(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $checkin   = TrainCheckin::factory(['user_id' => $user->id])->create();

        $response = $this->get(
            uri:     '/api/v1/statistics/daily/' . $checkin->departure->format('Y-m-d') . '?withPolylines',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'statuses',
                                               'polylines',
                                               'totalDistance',
                                               'totalDuration',
                                               'totalPoints',
                                           ]
                                       ]);
        $this->assertNotNull($response->json('data.polylines'));
    }
}
