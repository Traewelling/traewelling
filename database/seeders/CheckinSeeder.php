<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Event;
use App\Models\StatusTag;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $trips = Trip::with('stopovers')->get();
        $events = Event::all();

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $trip = $trips->random();

            $originStopover = $trip->stopovers->where('train_station_id', $trip->originStation->id)->first();
            $destinationStopover = $trip->stopovers->where('train_station_id', $trip->destinationStation->id)->first();

            if ($originStopover === null || $destinationStopover === null) {
                continue;
            }

            $checkin = Checkin::factory()
                ->for($user)
                ->create([
                    'trip_id' => $trip->trip_id,
                    'origin_stopover_id' => $originStopover->id,
                    'destination_stopover_id' => $destinationStopover->id,
                    'departure' => $trip->departure,
                    'arrival' => $trip->arrival,
                ]);

            $checkin->status->update([
                'event_id' => random_int(0, 1) ? $events->random()->id : null,
            ]);

            StatusTag::factory(['status_id' => $checkin->status_id])->create();
        }
    }
}
