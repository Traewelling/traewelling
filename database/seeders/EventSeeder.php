<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\StationIdentifierType;
use App\Models\Event;
use App\Models\Station;
use App\Models\StationIdentifier;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        $identifier = StationIdentifier::where('type', StationIdentifierType::IFOPT)
            ->where('identifier', 'de:02000:11907')
            ->first();

        if ($identifier) {
            $station = $identifier->station;
        } else {
            $station = Station::create([
                'name' => 'Baumwall (U)',
                'latitude' => 53.544193,
                'longitude' => 9.981119,
            ]);

            StationIdentifier::create([
                'station_id' => $station->id,
                'type' => StationIdentifierType::IFOPT,
                'identifier' => 'de:02000:11907',
                'name' => 'Baumwall (U)',
                'relevance' => 0,
            ]);
        }

        if (!Event::where('slug', 'Modellbahn' . date('y'))->exists()) {
            Event::factory([
                'name' => 'Jährliches Modelleisenbahntreffen ' . $year,
                'hashtag' => 'Modellbahn' . date('y'),
                'slug' => 'Modellbahn' . date('y'),
                'host' => 'Miniatur Wunderland Hamburg',
                'url' => 'https://traewelling.de',
                'station_id' => $station->id,
                'checkin_start' => "{$year}-01-01",
                'checkin_end' => "{$year}-12-31",
                'event_start' => "{$year}-01-01",
                'event_end' => "{$year}-12-31",
            ])->create();
        }
    }
}
