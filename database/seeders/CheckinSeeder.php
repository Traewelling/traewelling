<?php

namespace Database\Seeders;

use App\Enum\StatusTagKey;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Event;
use App\Models\Trip;
use App\Models\StatusTag;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class CheckinSeeder extends Seeder
{

    public function run(): void {
        foreach (User::all() as $user) {
            $trip = Trip::all()->random();
            try {
                $checkinResponse = TrainCheckinController::checkin(
                    user:        $user,
                    trip:        $trip,
                    origin:      $trip->originStation,      //Checkin from the first station...
                    departure:   $trip->departure,
                    destination: $trip->destinationStation, //...to the last station
                    arrival:     $trip->arrival,
                    event:       random_int(0, 1) ? Event::all()->random() : null,
                );
                $status          = $checkinResponse['status'];
                StatusTag::factory(['status_id' => $status->id])->create();
            } catch (Exception) {
                continue;
            }
        }
    }
}
