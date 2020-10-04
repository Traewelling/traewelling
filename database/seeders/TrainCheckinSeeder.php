<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use Illuminate\Database\Seeder;
use App\Http\Controllers\TransportController;
use App\Models\User;

class TrainCheckinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        foreach (User::all() as $user) {
            $trip = HafasTrip::all()->random();
            TransportController::TrainCheckin(
                $trip->trip_id,
                $trip->origin,
                $trip->destination,
                '',
                $user,
                0,
                0,
                0,
                rand(0, 1)
            );
        }
    }
}
