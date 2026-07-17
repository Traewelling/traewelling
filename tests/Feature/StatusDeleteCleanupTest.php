<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\PolyLine;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class StatusDeleteCleanupTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_deleting_last_status_of_trip_deletes_trip_stopovers_and_polyline(): void
    {
        $checkin = Checkin::factory()->create();
        $trip = $checkin->trip;
        $polylineId = $trip->polyline_id;

        $this->actAsApiUserWithAllScopes($checkin->user);
        $this->deleteJson("/api/v1/status/{$checkin->status_id}")->assertNoContent();

        $this->assertDatabaseMissing('hafas_trips', ['trip_id' => $trip->trip_id]);
        $this->assertDatabaseMissing('train_stopovers', ['trip_id' => $trip->trip_id]);
        $this->assertDatabaseMissing('poly_lines', ['id' => $polylineId]);
    }

    public function test_deleting_status_keeps_trip_when_other_checkins_exist(): void
    {
        $checkin = Checkin::factory()->create();
        $trip = $checkin->trip;
        Checkin::factory()->create([
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $checkin->origin_stopover_id,
            'destination_stopover_id' => $checkin->destination_stopover_id,
        ]);

        $this->actAsApiUserWithAllScopes($checkin->user);
        $this->deleteJson("/api/v1/status/{$checkin->status_id}")->assertNoContent();

        $this->assertDatabaseHas('hafas_trips', ['trip_id' => $trip->trip_id]);
        $this->assertDatabaseHas('poly_lines', ['id' => $trip->polyline_id]);
    }

    public function test_deleting_last_status_keeps_polyline_when_shared_with_other_trip(): void
    {
        $checkin = Checkin::factory()->create();
        $trip = $checkin->trip;
        $polylineId = $trip->polyline_id;

        $otherTrip = Trip::factory()->create();
        $otherTrip->update(['polyline_id' => $polylineId]);

        $this->actAsApiUserWithAllScopes($checkin->user);
        $this->deleteJson("/api/v1/status/{$checkin->status_id}")->assertNoContent();

        $this->assertDatabaseMissing('hafas_trips', ['trip_id' => $trip->trip_id]);
        $this->assertDatabaseHas('poly_lines', ['id' => $polylineId]);
    }

    public function test_deleting_last_status_also_deletes_unused_parent_polyline(): void
    {
        $checkin = Checkin::factory()->create();
        $trip = $checkin->trip;
        $parentPolyline = PolyLine::factory()->create(['polyline' => '[]']);
        PolyLine::where('id', $trip->polyline_id)->update(['parent_id' => $parentPolyline->id]);

        $this->actAsApiUserWithAllScopes($checkin->user);
        $this->deleteJson("/api/v1/status/{$checkin->status_id}")->assertNoContent();

        $this->assertDatabaseMissing('poly_lines', ['id' => $trip->polyline_id]);
        $this->assertDatabaseMissing('poly_lines', ['id' => $parentPolyline->id]);
    }
}
