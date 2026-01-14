<?php

namespace Tests\Feature\Transport;

use App\Models\Checkin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class CheckinDurationTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_checkin_duration_without_any_additional_timings(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
        ])->create();
        $this->assertEquals(60, $checkin->duration);
    }

    public function test_checkin_duration_with_delayed_departure(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
        ])->create();

        $checkin->originStopover->departure_real = '2021-01-01T00:10:00+01:00';

        $this->assertEquals(50, $checkin->duration);
    }

    public function test_checkin_duration_with_delayed_arrival(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
        ])->create();

        $checkin->destinationStopover->arrival_real = '2021-01-01T01:10:00+01:00';

        $this->assertEquals(70, $checkin->duration);
    }

    public function test_checkin_duration_with_delayed_departure_and_arrival(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
        ])->create();

        $checkin->originStopover->departure_real = '2021-01-01T00:10:00+01:00';
        $checkin->destinationStopover->arrival_real = '2021-01-01T01:10:00+01:00';

        $this->assertEquals(60, $checkin->duration);
    }

    public function test_checkin_duration_with_manual_departure(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'manual_departure' => '2021-01-01T00:10:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
        ])->create();

        $this->assertEquals(50, $checkin->duration);
    }

    public function test_checkin_duration_with_manual_arrival(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
            'manual_arrival' => '2021-01-01T01:10:00+01:00',
        ])->create();

        $this->assertEquals(70, $checkin->duration);
    }

    public function test_checkin_duration_with_manual_departure_and_arrival(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'manual_departure' => '2021-01-01T00:05:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
            'manual_arrival' => '2021-01-01T01:10:00+01:00',
        ])->create();

        $this->assertEquals(65, $checkin->duration);
    }

    public function test_checkin_duration_with_manual_departure_and_arrival_and_general_delayed_departure(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'manual_departure' => '2021-01-01T00:05:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
            'manual_arrival' => '2021-01-01T01:10:00+01:00',
        ])->create();

        // This should be ignored, because the real departure is already set by user
        $checkin->originStopover->departure_real = '2021-01-01T00:30:00+01:00';

        $this->assertEquals(65, $checkin->duration);
    }

    public function test_checkin_duration_with_manual_departure_and_arrival_and_general_delayed_arrival(): void
    {
        $checkin = Checkin::factory([
            'departure' => '2021-01-01T00:00:00+01:00',
            'manual_departure' => '2021-01-01T00:05:00+01:00',
            'arrival' => '2021-01-01T01:00:00+01:00',
            'manual_arrival' => '2021-01-01T01:10:00+01:00',
        ])->create();

        // This should be ignored, because the real arrival is already set by user
        $checkin->destinationStopover->arrival_real = '2021-01-01T01:50:00+01:00';

        $this->assertEquals(65, $checkin->duration);
    }
}
