<?php

namespace Tests\Feature\APIv1;

use App\Models\Operator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class OperatorTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_operators_index(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        Operator::factory()->count(3)->create();

        $response = $this->get('/api/v1/operators');
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
        ]);
    }

    public function test_operators_search_returns_matching_results(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        Operator::factory()->create(['name' => 'DB Fernverkehr']);
        Operator::factory()->create(['name' => 'DB Regio']);
        Operator::factory()->create(['name' => 'Österreichische Bundesbahnen']);

        $response = $this->get('/api/v1/operators?query=DB');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.name', 'DB Fernverkehr');
        $response->assertJsonPath('data.1.name', 'DB Regio');
    }

    public function test_operators_search_requires_at_least_two_characters(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        Operator::factory()->count(3)->create();

        $response = $this->get('/api/v1/operators?query=D');
        $response->assertOk();
        // Short query is ignored — returns all operators (up to page limit)
        $response->assertJsonCount(3, 'data');
    }

    public function test_operators_search_returns_empty_for_no_match(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        Operator::factory()->create(['name' => 'Deutsche Bahn AG']);

        $response = $this->get('/api/v1/operators?query=Flixbus');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_merge_operators(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $oldOperator = Operator::factory()->create();
        $newOperator = Operator::factory()->create();

        $response = $this->put('/api/v1/operators/' . $oldOperator->id . '/merge/' . $newOperator->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('hafas_operators', [
            'id' => $oldOperator->id,
        ]);
    }

    public function test_admin_can_merge_station(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);

        $oldOperator = Operator::factory()->create();
        $newOperator = Operator::factory()->create();

        $response = $this->put('/api/v1/operators/' . $oldOperator->id . '/merge/' . $newOperator->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('hafas_operators', [
            'id' => $oldOperator->id,
        ]);
    }

    public function test_user_cant_access_operators_list_backend(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/operators');
        $response->assertForbidden();
    }

    public function test_admin_can_access_operators_list_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $response = $this->actingAs($user)->get('/admin/operators');
        $response->assertOk();
    }
}
