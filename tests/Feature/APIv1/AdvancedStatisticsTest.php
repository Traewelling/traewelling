<?php

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class AdvancedStatisticsTest extends ApiTestCase
{
    use RefreshDatabase;

    private function createCheckinForUser(User $user, int $distance = 50000): Checkin
    {
        $checkin = Checkin::factory(['user_id' => $user->id, 'distance' => $distance])->create();
        $checkin->status->update(['user_id' => $user->id]);

        return $checkin;
    }

    public function test_overview_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/statistics/overview');
        $response->assertUnauthorized();
    }

    public function test_overview_returns_summary_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);

        $response = $this->getJson('/api/v1/statistics/overview');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'summary' => [
                    'total_checkins',
                    'active_days',
                    'total_distance_km',
                    'mean_distance_km',
                    'longest_checkin_by_distance',
                    'shortest_checkin_by_distance',
                    'longest_checkin_by_duration',
                    'shortest_checkin_by_duration',
                ],
            ],
        ]);
    }

    public function test_overview_with_no_checkins_returns_zeros(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/overview');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'summary' => [
                    'total_checkins' => 0,
                    'active_days' => 0,
                    'total_distance_km' => 0.0,
                ],
            ],
        ]);
        $this->assertNull($response->json('data.summary.longest_checkin_by_distance'));
        $this->assertNull($response->json('data.summary.shortest_checkin_by_distance'));
        $this->assertNull($response->json('data.summary.longest_checkin_by_duration'));
        $this->assertNull($response->json('data.summary.shortest_checkin_by_duration'));
    }

    public function test_overview_duration_fields_return_correct_checkins(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $short = Checkin::factory([
            'user_id' => $user->id,
            'distance' => 10000,
            'departure' => now()->subHour(),
            'arrival' => now(),
        ])->create();
        $short->status->update(['user_id' => $user->id]);

        $long = Checkin::factory([
            'user_id' => $user->id,
            'distance' => 20000,
            'departure' => now()->subHours(5),
            'arrival' => now(),
        ])->create();
        $long->status->update(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/statistics/overview');

        $response->assertOk();
        $this->assertSame(
            $long->status_id,
            $response->json('data.summary.longest_checkin_by_duration.id'),
        );
        $this->assertSame(
            $short->status_id,
            $response->json('data.summary.shortest_checkin_by_duration.id'),
        );
    }

    public function test_overview_counts_only_own_checkins(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);
        $this->createCheckinForUser($user);
        $this->createCheckinForUser($other);

        $response = $this->getJson('/api/v1/statistics/overview');

        $response->assertOk();
        $this->assertSame(2, $response->json('data.summary.total_checkins'));
    }

    public function test_overview_accepts_date_range_params(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/overview?from=2024-01-01&until=2024-12-31');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['summary']]);
    }

    public function test_overview_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/overview?from=2024-12-31&until=2024-01-01');

        $response->assertStatus(422);
    }

    public function test_history_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/statistics/history');
        $response->assertUnauthorized();
    }

    public function test_history_returns_period_breakdown(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);

        $response = $this->getJson('/api/v1/statistics/history');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'yearly',
                'monthly',
                'weekly',
            ],
        ]);
    }

    public function test_history_yearly_entry_has_correct_shape(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);

        $response = $this->getJson('/api/v1/statistics/history');

        $response->assertOk();
        $yearly = $response->json('data.yearly');
        $this->assertNotEmpty($yearly);
        $this->assertArrayHasKey('period', $yearly[0]);
        $this->assertArrayHasKey('period_type', $yearly[0]);
        $this->assertArrayHasKey('checkin_count', $yearly[0]);
        $this->assertArrayHasKey('distance_km', $yearly[0]);
        $this->assertSame('year', $yearly[0]['period_type']);
    }

    public function test_history_returns_empty_arrays_when_no_checkins(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/history');

        $response->assertOk();
        $this->assertEmpty($response->json('data.yearly'));
        $this->assertEmpty($response->json('data.monthly'));
        $this->assertEmpty($response->json('data.weekly'));
    }

    public function test_favorites_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/statistics/favorites');
        $response->assertUnauthorized();
    }

    public function test_favorites_returns_stations_lines_routes(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);

        $response = $this->getJson('/api/v1/statistics/favorites');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'stations',
                'lines',
                'routes',
            ],
        ]);
    }

    public function test_favorites_station_entry_has_correct_shape(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($user);

        $response = $this->getJson('/api/v1/statistics/favorites');

        $response->assertOk();
        $stations = $response->json('data.stations');
        $this->assertNotEmpty($stations);
        $this->assertArrayHasKey('station_id', $stations[0]);
        $this->assertArrayHasKey('name', $stations[0]);
        $this->assertArrayHasKey('count', $stations[0]);
    }

    public function test_favorites_returns_empty_arrays_when_no_checkins(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/favorites');

        $response->assertOk();
        $this->assertEmpty($response->json('data.stations'));
        $this->assertEmpty($response->json('data.lines'));
        $this->assertEmpty($response->json('data.routes'));
    }

    public function test_favorites_accepts_date_range_params(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/statistics/favorites?from=2024-01-01&until=2024-12-31');

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['stations', 'lines', 'routes']]);
    }

    public function test_favorites_only_shows_own_data(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $this->createCheckinForUser($other);

        $response = $this->getJson('/api/v1/statistics/favorites');

        $response->assertOk();
        $this->assertEmpty($response->json('data.stations'));
    }
}
