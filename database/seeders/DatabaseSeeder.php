<?php

namespace Database\Seeders;

use App\Models\TrainStation;
use Database\Seeders\Blogposts\Blogpost20131102;
use Database\Seeders\Blogposts\Blogpost20131129;
use Database\Seeders\Blogposts\Blogpost20140716;
use Database\Seeders\Blogposts\Blogpost20170801;
use Database\Seeders\Blogposts\Blogpost20191124;
use Database\Seeders\Blogposts\Blogpost20200220;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void {
        $this->call(PointsCalculationSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(FollowTableSeeder::class);
        TrainStation::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        $this->call(HafasTripSeeder::class);
        $this->call(TrainCheckinSeeder::class);

        //TODO: Until we have a new solution for our blog this will be the best way... urgh..
        $this->call(Blogpost20131102::class);
        $this->call(Blogpost20131129::class);
        $this->call(Blogpost20140716::class);
        $this->call(Blogpost20170801::class);
        $this->call(Blogpost20191124::class);
        $this->call(Blogpost20200220::class);
    }
}
