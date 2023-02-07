<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use App\Models\Location;
use App\Models\TrainStation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    public function run(): void {
        $this->call(UsersTableSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(FollowTableSeeder::class);
        TrainStation::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        HafasTrip::factory()->count(50)->create();
        $this->call(TrainCheckinSeeder::class);
        $this->call(PrivacyAgreementSeeder::class);
        Location::factory(10)->create();
    }
}
