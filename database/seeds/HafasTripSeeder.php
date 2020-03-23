<?php

use Illuminate\Database\Seeder;

class HafasTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\HafasTrip::class, 10)->create()->each(function ($trainStation)
        {
            $trainStation->save();
        });
    }
}
