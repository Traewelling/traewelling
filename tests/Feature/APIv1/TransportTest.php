<?php

namespace Tests\Feature\APIv1;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_set_home_by_uuid(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $station = Station::factory()->create();

        $response = $this->put('/api/v1/station/' . $station->uuid . '/home');
        $response->assertOk();
        $response->assertJsonPath('data.uuid', $station->uuid);

        $this->assertEquals($station->id, $user->fresh()?->home_id);
    }

    public function test_set_home_with_unknown_identifier(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->put('/api/v1/station/not-an-identifier/home')->assertNotFound();
        $this->put('/api/v1/station/' . Str::uuid() . '/home')->assertNotFound();

        $this->assertNull($user->fresh()?->home_id);
    }

    public function test_delete_home(): void
    {
        $station = Station::factory()->create();
        $user = User::factory()->create(['home_id' => $station->id]);
        Passport::actingAs($user, ['*']);

        $response = $this->delete('/api/v1/station/home');
        $response->assertNoContent();

        $user->refresh();
        $this->assertNull($user->home_id);
        $this->assertNull($user->home);
    }

    public function test_delete_home_without_home_station(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $this->delete('/api/v1/station/home')->assertNoContent();

        $this->assertNull($user->fresh()?->home_id);
    }
}
