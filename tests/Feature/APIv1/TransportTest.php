<?php

namespace Tests\Feature\APIv1;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class TransportTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_set_home(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $station = Station::factory()->create();

        $this->assertNull($user->home);

        $response = $this->put('/api/v1/station/' . $station->id . '/home');
        $response->assertOk();
        $user->refresh();
        $this->assertEquals($station->name, $user->home?->name);
    }
}
