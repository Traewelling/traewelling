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
        $trip = HafasTrip::factory()->create();
        return [
            'status_id'   => Status::factory(),
            'user_id'     => User::factory(),
            'trip_id'     => $trip->trip_id,
            'origin'      => $trip->originStation->ibnr,
            'destination' => $trip->destinationStation->ibnr,
            'distance'    => $this->faker->randomFloat(2, 0, 100),
            'departure'   => $trip->departure,
            'arrival'     => $trip->arrival,
            'points'      => $this->faker->numberBetween(0, 100),
        ];
    }

    public function configure(): static {
        //Update corresponding models so that the created checkins is consistent
        return $this->afterCreating(static function(TrainCheckin $checkin) {
            $checkin->status->update(['user_id' => $checkin->user_id]);
        });
    }
}
