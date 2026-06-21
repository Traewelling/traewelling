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

        $combinations = [];
        foreach ($users as $user) { // prevent unique constraint errors by collecting before
            foreach ($trips as $trip) {
                $originStopover = $trip->stopovers
                    ->where('train_station_id', $trip->originStation->id)
                    ->first();
                $destinationStopover = $trip->stopovers
                    ->where('train_station_id', $trip->destinationStation->id)
                    ->first();

                if ($originStopover === null || $destinationStopover === null) {
                    continue;
                }

                $combinations[] = compact('user', 'trip', 'originStopover', 'destinationStopover');
            }
        }

        foreach (array_slice($combinations, 0, 10) as $combo) {
            $checkin = Checkin::factory()
                ->for($combo['user'])
                ->create([
                    'trip_id' => $combo['trip']->trip_id,
                    'origin_stopover_id' => $combo['originStopover']->id,
                    'destination_stopover_id' => $combo['destinationStopover']->id,
                    'departure' => $combo['trip']->departure,
                    'arrival' => $combo['trip']->arrival,
                ]);

            $checkin->status->update([
                'event_id' => $events->isNotEmpty() ? $events->first()->id : null,
            ]);

            StatusTag::factory(['status_id' => $checkin->status_id])->create();
        }
    }
}
