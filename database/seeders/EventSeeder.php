<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Station;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{

    public function run(): void {
        Event::factory([
                           'name'    => 'Jährliches Modelleisenbahntreffen ' . date('Y'),
                           'hashtag' => 'Modellbahn' . date('y'),
                           'slug'    => 'Modellbahn' . date('y'),
                           'host'    => 'Modelleisenbahnfreunde Knuffingen',
                           'url'     => 'https://traewelling.de',
                       ])->create();
    }
}
