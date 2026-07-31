<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use Carbon\Carbon;
use Tests\Unit\UnitTestCase;

class StopoverTest extends UnitTestCase
{
    public function test_coordinate_prefers_the_station_identifier_over_the_station(): void
    {
        $stopover = $this->stopover(
            station: new Station(['latitude' => 52.5, 'longitude' => 13.4]),
            identifier: new StationIdentifier(['latitude' => 48.1, 'longitude' => 11.5]),
        );

        $coordinate = $stopover->coordinate;

        $this->assertSame(48.1, $coordinate?->latitude);
        $this->assertSame(11.5, $coordinate?->longitude);
    }

    public function test_coordinate_falls_back_to_the_station(): void
    {
        $stopover = $this->stopover(station: new Station(['latitude' => 52.5, 'longitude' => 13.4]));

        $coordinate = $stopover->coordinate;

        $this->assertSame(52.5, $coordinate?->latitude);
        $this->assertSame(13.4, $coordinate?->longitude);
    }

    public function test_coordinate_is_null_without_any_location(): void
    {
        $this->assertNull($this->stopover()->coordinate);
    }

    public function test_planned_seconds_until_measures_departure_to_arrival(): void
    {
        $start = new Stopover([
            'arrival_planned' => Carbon::parse('2026-07-31 09:58:00'),
            'departure_planned' => Carbon::parse('2026-07-31 10:00:00'),
        ]);
        $end = new Stopover([
            'arrival_planned' => Carbon::parse('2026-07-31 10:04:00'),
            'departure_planned' => Carbon::parse('2026-07-31 10:06:00'),
        ]);

        $this->assertSame(240, $start->plannedSecondsUntil($end));
    }

    public function test_planned_seconds_until_falls_back_to_the_other_timestamp(): void
    {
        // A terminus has no departure, a starting point no arrival.
        $start = new Stopover(['arrival_planned' => Carbon::parse('2026-07-31 10:00:00')]);
        $end = new Stopover(['departure_planned' => Carbon::parse('2026-07-31 10:05:00')]);

        $this->assertSame(300, $start->plannedSecondsUntil($end));
    }

    public function test_planned_seconds_until_returns_minus_one_without_timestamps(): void
    {
        $this->assertSame(-1, new Stopover()->plannedSecondsUntil(new Stopover()));
    }

    private function stopover(?Station $station = null, ?StationIdentifier $identifier = null): Stopover
    {
        return new Stopover()
            ->setRelation('station', $station)
            ->setRelation('stationIdentifier', $identifier);
    }
}
