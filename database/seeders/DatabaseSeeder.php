<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use App\Models\TrainStation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call(UsersTableSeeder::class);
        $this->call(FollowTableSeeder::class);
        TrainStation::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        HafasTrip::factory()->count(50)->create();
        $this->call(TrainCheckinSeeder::class);
    }
}
