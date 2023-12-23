<?php

namespace Database\Seeders;

use App\Enum\StatusTagKey;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\StatusTag;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class TrainCheckinSeeder extends Seeder
{

    public function run(): void {
        foreach (User::all() as $user) {
            $hafasTrip = HafasTrip::all()->random();
            try {
                $checkinResponse = TrainCheckinController::checkin(
                    user:        $user,
                    hafasTrip:   $hafasTrip,
                    origin:      $hafasTrip->originStation,      //Checkin from the first station...
                    departure:   $hafasTrip->departure,
                    destination: $hafasTrip->destinationStation, //...to the last station
                    arrival:     $hafasTrip->arrival,
                    event:       random_int(0, 1) ? Event::all()->random() : null,
                );
                $status               = $checkinResponse['status'];
                StatusTag::factory(['status_id' => $status->id])->create();
            } catch (Exception) {
                continue;
            }
        }
    }
}
