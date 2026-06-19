<?php

namespace Tests\Feature\APIv1;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StatusTagTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_view_non_existing_tags_on_own_status(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status = Status::factory(['user_id' => $user->id])->create();

        $response = $this->get('/api/v1/status/' . $status->id . '/tags');
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');
    }

    public function test_view_tags_on_own_status_with_different_visibilities_and_delete_one(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status = Status::factory(['user_id' => $user->id])->create();
        $tagToDelete = StatusTag::factory(['status_id' => $status->id, 'key' => 'first', 'visibility' => StatusVisibility::PUBLIC->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'second', 'visibility' => StatusVisibility::FOLLOWERS->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'third', 'visibility' => StatusVisibility::PRIVATE->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'fourth', 'visibility' => StatusVisibility::AUTHENTICATED->value])->create();

        $response = $this->get('/api/v1/status/' . $status->id . '/tags');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'key',
                    'value',
                    'visibility',
                ],
            ],
        ]);
        $response->assertJsonCount(4, 'data');

        $this->assertDatabaseHas('status_tags', ['id' => $tagToDelete->id]);

        // Delete StatusTag
        $response = $this->delete('/api/v1/status/' . $status->id . '/tags/' . $tagToDelete->key);
        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('status_tags', ['id' => $tagToDelete->id]);
    }

    public function test_create_and_update_tag(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status = Status::factory(['user_id' => $user->id])->create();

        // Create StatusTag
        $response = $this->post(
            uri: '/api/v1/status/' . $status->id . '/tags',
            data: [
                'key' => 'test',
                'value' => 'test',
                'visibility' => StatusVisibility::PUBLIC->value,
            ],
        );
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'key' => 'test',
                'value' => 'test',
                'visibility' => StatusVisibility::PUBLIC->value,
            ],
        ]);

        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);

        // Update StatusTag and change key and value
        $response = $this->put(
            uri: '/api/v1/status/' . $status->id . '/tags/test',
            data: [
                'key' => 'test2',
                'value' => 'test2',
            ],
        );
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'key' => 'test2',
                'value' => 'test2',
                'visibility' => StatusVisibility::PUBLIC->value,
            ],
        ]);

        $this->assertDatabaseMissing('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);
        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test2', 'value' => 'test2', 'visibility' => StatusVisibility::PUBLIC->value]);
    }

    public function test_social_status_tag_accepts_all_allowed_values(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        foreach (StatusTagKey::SOCIAL_STATUS->allowedValues() as $value) {
            $response = $this->post(
                uri: '/api/v1/status/' . $status->id . '/tags',
                data: [
                    'key' => StatusTagKey::SOCIAL_STATUS->value,
                    'value' => $value,
                    'visibility' => StatusVisibility::PUBLIC->value,
                ],
            );
            $response->assertOk();
            $response->assertJson(['data' => ['key' => StatusTagKey::SOCIAL_STATUS->value, 'value' => $value]]);

            // Clean up so the unique constraint doesn't block the next iteration
            $this->delete('/api/v1/status/' . $status->id . '/tags/' . StatusTagKey::SOCIAL_STATUS->value);
        }
    }

    public function test_social_status_tag_rejects_invalid_value_on_store(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->post(
            uri: '/api/v1/status/' . $status->id . '/tags',
            data: [
                'key' => StatusTagKey::SOCIAL_STATUS->value,
                'value' => 'invalid_value',
                'visibility' => StatusVisibility::PUBLIC->value,
            ],
        );

        $response->assertStatus(400);
        $this->assertDatabaseMissing('status_tags', [
            'status_id' => $status->id,
            'key' => StatusTagKey::SOCIAL_STATUS->value,
        ]);
    }

    public function test_suggestions_requires_authentication(): void
    {
        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertUnauthorized();
    }

    public function test_suggestions_returns_empty_array_when_no_tags(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_suggestions_returns_three_most_recent_tags(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $pairs = [
            ['key' => StatusTagKey::SEAT->value,         'value' => '61',               'created_at' => now()->subDays(10)],
            ['key' => StatusTagKey::TICKET->value,       'value' => 'Deutschlandticket', 'created_at' => now()->subDays(5)],
            ['key' => StatusTagKey::WAGON->value,        'value' => '25',               'created_at' => now()->subDays(2)],
            ['key' => StatusTagKey::TRAVEL_CLASS->value, 'value' => '1',                'created_at' => now()->subDay()],
        ];

        foreach ($pairs as $pair) {
            $status = Status::factory(['user_id' => $user->id])->create();
            StatusTag::factory(['status_id' => $status->id, 'key' => $pair['key'], 'value' => $pair['value'], 'created_at' => $pair['created_at']])->create();
        }

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        $keys = collect($response->json('data'))->pluck('key')->all();
        $this->assertContains(StatusTagKey::TRAVEL_CLASS->value, $keys);
        $this->assertContains(StatusTagKey::WAGON->value, $keys);
        $this->assertContains(StatusTagKey::TICKET->value, $keys);
        $this->assertNotContains(StatusTagKey::SEAT->value, $keys);
    }

    public function test_suggestions_deduplicates_repeated_key_value_pairs(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        foreach (range(1, 5) as $i) {
            $status = Status::factory(['user_id' => $user->id])->create();
            StatusTag::factory(['status_id' => $status->id, 'key' => StatusTagKey::SEAT->value, 'value' => '61'])->create();
        }

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.key', StatusTagKey::SEAT->value);
        $response->assertJsonPath('data.0.value', '61');
    }

    public function test_suggestions_includes_frequent_tags_from_last_three_days(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        foreach (range(1, 2) as $i) {
            $status = Status::factory(['user_id' => $user->id])->create();
            StatusTag::factory([
                'status_id' => $status->id,
                'key' => StatusTagKey::TICKET->value,
                'value' => 'Deutschlandticket',
                'created_at' => now()->subDay(),
            ])->create();
        }

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();

        $keys = collect($response->json('data'))->pluck('key')->all();
        $this->assertContains(StatusTagKey::TICKET->value, $keys);
    }

    public function test_suggestions_excludes_tags_used_only_once_in_frequent_group(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $status = Status::factory(['user_id' => $user->id])->create();
        StatusTag::factory([
            'status_id' => $status->id,
            'key' => StatusTagKey::TICKET->value,
            'value' => 'Einzelticket',
            'created_at' => now()->subHours(12),
        ])->create();

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();

        // Appears in recent group (1 recent entry), but the frequent group must not include it
        // since count = 1 < 2. It should still appear exactly once overall via the recent group.
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.key', StatusTagKey::TICKET->value);
    }

    public function test_suggestions_excludes_old_tags_from_frequent_group(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        foreach (range(1, 3) as $i) {
            $status = Status::factory(['user_id' => $user->id])->create();
            StatusTag::factory([
                'status_id' => $status->id,
                'key' => StatusTagKey::WAGON->value,
                'value' => '12',
                'created_at' => now()->subDays(10),
            ])->create();
        }

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();

        // Appears in recent group but NOT in frequent group (older than 3 days).
        // Should still appear once via recent.
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.key', StatusTagKey::WAGON->value);
    }

    public function test_suggestions_deduplicates_across_recent_and_frequent_groups(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        // This pair qualifies for both groups: it is recent AND used >= 2 times within 3 days.
        foreach (range(1, 2) as $i) {
            $status = Status::factory(['user_id' => $user->id])->create();
            StatusTag::factory([
                'status_id' => $status->id,
                'key' => StatusTagKey::SEAT->value,
                'value' => '61',
                'created_at' => now()->subHours(6),
            ])->create();
        }

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_suggestions_does_not_return_other_users_tags(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $status = Status::factory(['user_id' => $other->id])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => StatusTagKey::SEAT->value, 'value' => '99'])->create();

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_suggestions_response_contains_only_key_and_value(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $status = Status::factory(['user_id' => $user->id])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => StatusTagKey::SEAT->value, 'value' => '61'])->create();

        $response = $this->get('/api/v1/tags/suggestions');
        $response->assertOk();
        $response->assertJsonStructure(['data' => [['key', 'value']]]);

        $item = $response->json('data.0');
        $this->assertArrayHasKey('key', $item);
        $this->assertArrayHasKey('value', $item);
        $this->assertArrayNotHasKey('visibility', $item);
    }

    public function test_social_status_tag_rejects_invalid_value_on_update(): void
    {
        $user = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        StatusTag::factory([
            'status_id' => $status->id,
            'key' => StatusTagKey::SOCIAL_STATUS->value,
            'value' => 'open',
            'visibility' => StatusVisibility::PUBLIC->value,
        ])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->put(
            uri: '/api/v1/status/' . $status->id . '/tags/' . StatusTagKey::SOCIAL_STATUS->value,
            data: ['value' => 'invalid_value'],
        );

        $response->assertStatus(400);
        $this->assertDatabaseHas('status_tags', [
            'status_id' => $status->id,
            'key' => StatusTagKey::SOCIAL_STATUS->value,
            'value' => 'open',
        ]);
    }
}
