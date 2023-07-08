<?php

namespace Database\Factories;

use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

class TrainCheckinFactory extends Factory
{
    public function definition(): array {
        return [
            'status_id'   => Status::factory(),
            'user_id'     => User::factory(),
            'trip_id'     => HafasTrip::factory()->create()->trip_id,
            'origin'      => TrainStation::factory()->create()->ibnr,
            'destination' => TrainStation::factory()->create()->ibnr,
            'distance'    => $this->faker->randomFloat(2, 0, 100),
            'departure'   => Date::now()->subHour(),
            'arrival'     => Date::now()->addHour(),
            'points'      => $this->faker->numberBetween(0, 100),
        ];
    }

    public function configure(): static {
        //Update corresponding models so that the created checkins is consistent
        return $this->afterCreating(static function(TrainCheckin $checkin) {
            $checkin->status->update(['user_id' => $checkin->user_id]);

            $checkin->HafasTrip->update([
                                            'origin'      => $checkin->originStation->ibnr,
                                            'destination' => $checkin->destinationStation->ibnr,
                                            'departure'   => $checkin->departure,
                                            'arrival'     => $checkin->arrival,
                                        ]);

            //Create (or update) origin stopover
            TrainStopover::updateOrCreate(
                [
                    'trip_id'          => $checkin->HafasTrip->trip_id,
                    'train_station_id' => $checkin->originStation->id,
                ],
                [
                    'arrival_planned'   => $checkin->departure,
                    'departure_planned' => $checkin->departure,
                ]
            );

            //Create (or update) destination stopover
            TrainStopover::updateOrCreate(
                [
                    'trip_id'          => $checkin->HafasTrip->trip_id,
                    'train_station_id' => $checkin->destinationStation->id,
                ],
                [
                    'arrival_planned'   => $checkin->arrival,
                    'departure_planned' => $checkin->arrival,
                ]
            );
        });
    }
}
