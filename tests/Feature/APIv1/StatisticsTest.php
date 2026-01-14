<?php

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StatisticsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_daily_statistics(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $checkin = Checkin::factory(['user_id' => $user->id])->create();

        $response = $this->get('/api/v1/statistics/daily/' . $checkin->departure->format('Y-m-d'));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'statuses',
                'polylines',
                'totalDistance',
                'totalDuration',
                'totalPoints',
            ],
        ]);
        $this->assertNull($response->json('data.polylines'));
    }

    public function test_daily_statistics_with_polylines(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $checkin = Checkin::factory(['user_id' => $user->id])->create();

        $response = $this->get('/api/v1/statistics/daily/' . $checkin->departure->format('Y-m-d') . '?withPolylines=true');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'statuses',
                'polylines',
                'totalDistance',
                'totalDuration',
                'totalPoints',
            ],
        ]);
        $this->assertNotNull($response->json('data.polylines'));
    }
}
