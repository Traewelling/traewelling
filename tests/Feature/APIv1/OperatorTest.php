<?php

namespace Tests\Feature\APIv1;

use App\Models\HafasOperator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class OperatorTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testOperatorsIndex(): void {
        Passport::actingAs(User::factory()->create(), ['*']);

        HafasOperator::factory()->count(3)->create();

        $response = $this->get('/api/v1/operators');
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
                                           'data'  => [
                                               '*' => [
                                                   'id',
                                                   'name',
                                               ]
                                           ],
                                           'links' => [
                                               'first',
                                               'last',
                                               'prev',
                                               'next',
                                           ],
                                           'meta'  => [
                                               'path',
                                               'per_page',
                                               'next_cursor',
                                               'prev_cursor',
                                           ],
                                       ]);
    }
}
