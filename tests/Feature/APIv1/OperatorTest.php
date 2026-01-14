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
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
        ]);
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
