<?php

namespace Database\Seeders;

use App\Models\TrainStation;
use Database\Seeders\Blogposts\BlogpostSeeder;
use Database\Seeders\Privacy\PrivacyAgreementSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void {
        $this->call(UsersTableSeeder::class);
        $this->call(FollowTableSeeder::class);
        TrainStation::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        $this->call(HafasTripSeeder::class);
        $this->call(TrainCheckinSeeder::class);

        $this->call(PrivacyAgreementSeeder::class);
        $this->call(BlogpostSeeder::class);
    }
}
