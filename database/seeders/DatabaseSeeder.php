<?php

namespace Database\Seeders;

use App\Models\EventSuggestion;
use App\Models\HafasOperator;
use App\Models\Trip;
use App\Models\Station;
use Database\Seeders\Constants\PermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void {
        //Seed obligatory constants
        $this->call(PermissionSeeder::class);

        //Seed example data for development
        $this->call(UsersTableSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(FollowTableSeeder::class);
        Station::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        Trip::factory()->count(50)->create();
        $this->call(CheckinSeeder::class);
        $this->call(PrivacyAgreementSeeder::class);
        EventSuggestion::factory(['user_id' => 1])->count(5)->create();
        HafasOperator::factory()->count(300)->create();
    }
}
