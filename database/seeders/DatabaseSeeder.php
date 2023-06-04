<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainStation;
use App\Models\User;
use App\Notifications\UserJoinedConnection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void {
        $this->call(UsersTableSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(FollowTableSeeder::class);
        TrainStation::factory()->count(50)->create();
        $this->call(EventSeeder::class);
        HafasTrip::factory()->count(50)->create();
        $this->call(TrainCheckinSeeder::class);
        $this->call(PrivacyAgreementSeeder::class);

        $user   = User::find(1);
        $status = Status::find(1);
        for ($i = 0; $i < 10000; $i++) {
            $user->notify(new UserJoinedConnection($status));
        }
    }
}
