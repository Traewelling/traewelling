<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(UsersTableSeeder::class);
         $this->call(FollowTableSeeder::class);
         $this->call(TrainStationsTableSeeder::class);
         $this->call(EventSeeder::class);
         $this->call(HafasTripSeeder::class);
         $this->call(TrainCheckinSeeder::class);
    }
}
