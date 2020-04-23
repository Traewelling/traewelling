<?php

use App\HafasTrip;
use App\Status;
use Illuminate\Database\Seeder;
use App\Http\Controllers\TransportController;
use App\User;

class TrainCheckinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($cnt = 0; $cnt < 50; $cnt++) {
            $user = User::all()->random();
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
                rand(0,1)
                );
        }
        $user = User::where('username', 'Gertrud123')->first();
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
            rand(0,1)
        );
    }
}
