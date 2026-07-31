<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ImportProviderPolyline;
use App\Models\Station;
use App\Models\Trip;
use App\Models\User;
use App\Services\Checkin\CheckinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\FeatureTestCase;
use Tests\Helpers\CheckinRequestTestHydrator;

class ImportProviderPolylineTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_checking_in_queues_the_provider_polyline_import_for_the_trip(): void
    {
        Queue::fake();
        Station::factory()->count(2)->create();
        $user = User::factory()->create();
        $trip = Trip::factory()->create();

        app(CheckinService::class)->checkin(new CheckinRequestTestHydrator($user)->hydrateFromTrip($trip));

        Queue::assertPushed(
            ImportProviderPolyline::class,
            fn (ImportProviderPolyline $job) => $job->uniqueId() === (string) $trip->id,
        );
    }
}
