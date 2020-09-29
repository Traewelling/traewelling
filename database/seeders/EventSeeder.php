<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TrainStation;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $event = new Event([
                               'name'         => 'Jährliches Modelleisenbahntreffen ' . date('Y'),
                               'hashtag'      => 'Modellbahn' . date('y'),
                               'slug'         => 'Modellbahn' . date('y'),
                               'host'         => 'Modelleisenbahnfreunde Knuffingen',
                               'url'          => 'https://traewelling.de',
                               'begin'        => date('Y-m-d H:i:s', strtotime('-1 day')),
                               'end'          => date('Y-m-d H:i:s', strtotime('+3 days')),
                               'trainstation' => TrainStation::all()->random()->id
                           ]);
        $event->save();
    }
}
