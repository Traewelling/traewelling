<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Illuminate\Database\Seeder;

class HafasTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void {
        $hafasTrips = HafasTrip::factory()->count(50)->create();
        foreach ($hafasTrips as $hafasTrip) {
            $legacyStopovers = json_decode($hafasTrip->stopovers);
            foreach ($legacyStopovers as $legacyStopover) {
                TrainStopover::create([
                                          'trip_id'                    => $hafasTrip->trip_id,
                                          'train_station_id'           => TrainStation::where('ibnr', $legacyStopover->stop->id)->first()->id,
                                          'arrival_planned'            => $legacyStopover->plannedArrival,
                                          'arrival_real'               => $legacyStopover->arrival,
                                          'arrival_platform_planned'   => $legacyStopover->plannedArrivalPlatform,
                                          'arrival_platform_real'      => $legacyStopover->arrivalPlatform,
                                          'departure_planned'          => $legacyStopover->plannedDeparture,
                                          'departure_real'             => $legacyStopover->departure,
                                          'departure_platform_planned' => $legacyStopover->plannedDeparturePlatform,
                                          'departure_platform_real'    => $legacyStopover->departurePlatform,
                                          'cancelled'                  => false,
                                      ]);
            }
        }
    }
}
