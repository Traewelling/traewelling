<?php

use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $event = new \App\Event([
            'name' => 'Jährliches Modelleisenbahntreffen ' . date('Y'),
            'hashtag' => 'Modellbahn' . date('y'),
            'slug' => 'Modellbahn' . date('y'),
            'host' => 'Modelleisenbahnfreunde Knuffingen',
            'url' => 'https://traewelling.de',
            'begin' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'end' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'trainstation' => \App\TrainStations::all()->random()->ibnr
        ]);
        $event->save();
    }
}
