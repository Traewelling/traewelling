<?php

declare(strict_types=1);

namespace Tests\Unit\DataProviders;

use App\DataProviders\Motis;
use App\Enum\DataProvider;
use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Models\StationIdentifier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Unit\UnitTestCase;

/**
 * Issue #4910: the departure board of a station must not show the departures of another stop that
 * took over the station's stop id in a later export.
 */
class MotisStaleIdentifierTest extends UnitTestCase
{
    use RefreshDatabase;

    private const string REUSED_IDENTIFIER = 'de-DELFI_000000000001_G';

    private const string FRESH_IDENTIFIER = 'de-DELFI_000000000001';

    private function createStationWithIdentifier(string $name, float $lat, float $lon, string $identifier): Station
    {
        $station = Station::factory()->create(['name' => $name, 'latitude' => $lat, 'longitude' => $lon]);
        StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'identifier' => $identifier,
            'type' => StationIdentifierType::MOTIS,
            'origin' => DataProvider::TRANSITOUS->value,
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lon,
        ]);

        return $station;
    }

    /**
     * @return array<string, mixed>
     */
    private function place(string $stopId, string $name, float $lat, float $lon): array
    {
        return ['stopId' => $stopId, 'name' => $name, 'lat' => $lat, 'lon' => $lon];
    }

    public function test_reused_identifier_is_retired_and_the_station_is_resolved_again(): void
    {
        $magdeburgerforth = $this->createStationWithIdentifier('Magdeburgerforth, Kleinbahnhof', 52.2325, 12.1959, self::REUSED_IDENTIFIER);

        Http::fake([
            'api.transitous.org/api/v6/stoptimes*' => function (Request $request) {
                return $request['stopId'] === self::REUSED_IDENTIFIER
                    ? Http::response(['place' => $this->place(self::REUSED_IDENTIFIER, 'Benndorf (Mansfelder Land), Bahnhof', 51.575455, 11.492014), 'stopTimes' => []])
                    : Http::response(['place' => $this->place(self::FRESH_IDENTIFIER, 'Magdeburgerforth (Kleinbahn)', 52.23358, 12.196803), 'stopTimes' => []]);
            },
            'api.transitous.org/api/v6/map/stops*' => Http::response([
                $this->place(self::FRESH_IDENTIFIER, 'Magdeburgerforth (Kleinbahn)', 52.23358, 12.196803),
            ]),
        ]);

        $motis = new Motis(DataProvider::TRANSITOUS);
        $departures = $motis->getFilteredDepartures($magdeburgerforth, Carbon::parse('2026-09-25 10:00'));

        $this->assertCount(0, $departures->departures, 'The departures of the stop that took over the id must not be shown');
        $this->assertNull(StationIdentifier::where('identifier', self::REUSED_IDENTIFIER)->first());
        $retiredIdentifier = StationIdentifier::where('identifier', 'like', self::REUSED_IDENTIFIER . StationIdentifier::RETIRED_MARKER . '%')->firstOrFail();
        $this->assertSame($magdeburgerforth->id, $retiredIdentifier->station_id);
        $this->assertSame(-9_000, $retiredIdentifier->relevance);

        // Without a usable identifier the next request resolves the station through the nearest stop
        $motis->getFilteredDepartures($magdeburgerforth->fresh(), Carbon::parse('2026-09-25 10:00'));
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'stoptimes') && $request['stopId'] === self::FRESH_IDENTIFIER);
        $this->assertSame('Magdeburgerforth, Kleinbahnhof', $magdeburgerforth->fresh()->name);
    }

    public function test_identifier_of_a_large_station_within_the_threshold_is_kept(): void
    {
        $station = $this->createStationWithIdentifier('Amsterdam Centraal', 52.3791, 4.9003, 'nl-OpenOV_3152088');

        Http::fake([
            'api.transitous.org/api/v6/stoptimes*' => Http::response([
                'place' => $this->place('nl-OpenOV_3152088', 'Amsterdam Centraal', 52.3782, 4.9055),
                'stopTimes' => [],
            ]),
        ]);

        new Motis(DataProvider::TRANSITOUS)->getFilteredDepartures($station, Carbon::parse('2026-09-25 10:00'));

        $this->assertSame($station->id, StationIdentifier::where('identifier', 'nl-OpenOV_3152088')->firstOrFail()->station_id);
        Http::assertSentCount(1);
    }
}
