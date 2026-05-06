<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class DuplicateCheckinsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_returns_empty_when_no_duplicates(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $checkin = Checkin::factory()->create(['user_id' => $user->id]);
        $checkin->status->update(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/statuses/duplicates');

        $response->assertOk();
        $response->assertJson(['data' => []]);
    }

    public function test_returns_duplicate_groups(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $first = Checkin::factory()->create(['user_id' => $user->id]);
        $first->status->update(['user_id' => $user->id]);

        $second = Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $first->trip_id,
            'origin_stopover_id' => $first->origin_stopover_id,
            'destination_stopover_id' => $first->destination_stopover_id,
        ]);
        $secondStatus = Status::factory()->create(['user_id' => $user->id]);
        $second->status_id = $secondStatus->id;
        $second->save();

        $response = $this->getJson('/api/v1/statuses/duplicates');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertCount(2, $data[0]['statuses']);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/statuses/duplicates');
        $response->assertUnauthorized();
    }

    public function test_does_not_return_other_users_duplicates(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $first = Checkin::factory()->create(['user_id' => $otherUser->id]);
        $first->status->update(['user_id' => $otherUser->id]);

        $second = Checkin::factory()->create([
            'user_id' => $otherUser->id,
            'trip_id' => $first->trip_id,
            'origin_stopover_id' => $first->origin_stopover_id,
            'destination_stopover_id' => $first->destination_stopover_id,
        ]);
        $secondStatus = Status::factory()->create(['user_id' => $otherUser->id]);
        $second->status_id = $secondStatus->id;
        $second->save();

        $response = $this->getJson('/api/v1/statuses/duplicates');

        $response->assertOk();
        $response->assertJson(['data' => []]);
    }
}
