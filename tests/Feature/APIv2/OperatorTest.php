<?php

declare(strict_types=1);

namespace Tests\Feature\APIv2;

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

        $operator = Operator::factory()->create(['name' => 'DB Regio']);
        Operator::factory()->create(['name' => 'Österreichische Bundesbahnen']);

        $this->get('/api/v2/operators')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['type', 'id', 'name']], 'links', 'meta' => ['next_cursor']]);

        $this->get('/api/v2/operators?query=DB')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $operator->id);
    }

    public function test_operators_require_authentication(): void
    {
        $this->getJson('/api/v2/operators')->assertUnauthorized();
    }
}
