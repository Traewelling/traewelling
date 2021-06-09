<?php

namespace Database\Seeders;

use App\Enum\StatusVisibility;
use App\Exceptions\CheckInCollisionException;
use App\Http\Controllers\TransportController;
use App\Models\HafasTrip;
use App\Models\User;
use Illuminate\Database\Seeder;

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
            try {
                TransportController::TrainCheckin(
                    $trip->trip_id,
                    $trip->origin,
                    $trip->destination,
                    '',
                    $user,
                    0,
                    0,
                    0,
                    StatusVisibility::PUBLIC,
                    rand(0, 1)
                );
            } catch (CheckInCollisionException $e) {
                continue;
            }
        }
    }
}
