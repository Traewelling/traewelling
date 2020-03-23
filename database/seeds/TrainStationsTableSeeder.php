<?php

use Illuminate\Database\Seeder;

class TrainStationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\TrainStations::class, 50)->create()->each(function ($trainStation) {
            $trainStation->save();
        });
    }
}
