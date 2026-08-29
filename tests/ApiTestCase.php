<?php

namespace Tests;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\TripSource;
use App\Models\Checkin;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;

abstract class ApiTestCase extends FeatureTestCase
{
    public $mockConsoleOutput = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install', ['--no-interaction' => true]);
        $this->artisan('passport:keys', ['--no-interaction' => true]);
    }

    protected function actAsApiUserWithAllScopes(?User $user = null): void
    {
        if ($user === null) {
            $user = User::factory()->create();
        }
        Passport::actingAs($user, ['*']);
    }

    /**
     * Creates a manual trip (`source = user`) owned by the given user.
     */
    protected function createManualTrip(User $owner): Trip
    {
        return Trip::factory()->create([
            'source' => TripSource::USER,
            'user_id' => $owner->id,
        ]);
    }

    /**
     * Creates a trip that came from a data provider and therefore belongs to nobody.
     */
    protected function createProviderTrip(): Trip
    {
        return Trip::factory()->create(['source' => TripSource::TRANSITOUS]);
    }

    /**
     * Checks the given user into a trip, from its first to its last stopover.
     * Passing a visibility or a purpose of travel also moves the resulting status to that user
     * and applies them.
     */
    protected function checkinOnTrip(
        Trip $trip,
        User $user,
        ?StatusVisibility $visibility = null,
        ?Business $business = null,
    ): Checkin {
        $checkin = Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $trip->stopovers->first()->id,
            'destination_stopover_id' => $trip->stopovers->last()->id,
            'departure' => $trip->departure,
            'arrival' => $trip->arrival,
        ]);

        $status = [];
        if ($visibility !== null) {
            $status['visibility'] = $visibility->value;
        }
        if ($business !== null) {
            $status['business'] = $business->value;
        }

        if ($status === []) {
            return $checkin;
        }

        $checkin->status->update(array_merge(['user_id' => $user->id], $status));

        return $checkin->fresh();
    }

    protected function assertUserResource(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data' => [
                'id',
                'displayName',
                'username',
                'profilePicture',
                'trainDistance',
                'trainDuration',
                'points',
                'mastodonUrl',
                'privateProfile',
                'preventIndex',
                'home',
                'language',
            ],
        ]);
    }

    protected function assertEventResource(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'hashtag',
                'host',
                'url',
                'begin',
                'end',
                'station' => [
                    'id',
                    'name',
                    'latitude',
                    'longitude',
                    'ibnr',
                    'rilIdentifier',
                ],
            ],
        ]);
    }

    protected function assertEventDetailsResource(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'trainDistance',
                'trainDuration',
            ],
        ]);
    }
}
