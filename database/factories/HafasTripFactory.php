<?php

namespace Database\Factories;

use App\Enum\HafasTravelType;
use App\Http\Controllers\TransportController;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HafasTripFactory extends Factory
{
    public function definition(): array {
        if (TrainStation::all()->count() > 3) {
            $origin      = TrainStation::inRandomOrder()->first();
            $destination = TrainStation::inRandomOrder()->where('id', '!=', $origin->id)->first();
        } else {
            $origin      = TrainStation::factory()->create();
            $destination = TrainStation::factory()->create();
        }

        return [
            'trip_id'        => $this->faker->unique()->numerify('1|######|##|##|') . Carbon::now()->format('dmY'),
            'category'       => $this->faker->randomElement(HafasTravelType::cases())->value,
            'number'         => $this->faker->bothify('??-##'),
            'linename'       => $this->faker->bothify('?? ##'),
            'journey_number' => $this->faker->numberBetween(10000, 99999),
            'operator_id'    => HafasOperator::factory()->create()->id,
            'origin'         => $origin->ibnr,
            'destination'    => $destination->ibnr,
            'polyline_id'    => null, //Will be set in the configure function
            'departure'      => Carbon::now()->subMinutes(15)->format('c'),
            'arrival'        => Carbon::now()->addMinutes(80)->format('c'),
            'delay'          => 0, //TODO: is deprecated? used?
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static {
        return $this->afterCreating(function(HafasTrip $hafasTrip) {
            $stops = TrainStation::inRandomOrder()
                                 ->whereNotIn('id', [$hafasTrip->originStation->id, $hafasTrip->destinationStation->id])
                                 ->limit(2)
                                 ->get();
            if ($stops->count() < 2) {
                for ($i = 0; $i < 2; $i++) {
                    $stops->push(TrainStation::factory()->create());
                }
            }

            $time = $hafasTrip->departure->clone();

            // Create origin stopover
            TrainStopover::factory([
                                       'trip_id'           => $hafasTrip->trip_id,
                                       'train_station_id'  => $hafasTrip->originStation->id,
                                       'arrival_planned'   => $hafasTrip->departure->toIso8601String(),
                                       'arrival_real'      => $hafasTrip->departure->toIso8601String(),
                                       'departure_planned' => $hafasTrip->departure->toIso8601String(),
                                       'departure_real'    => $hafasTrip->departure->toIso8601String(),
                                   ])->create();

            // Create intermediate stopovers
            foreach ($stops as $stop) {
                $time->addMinutes(15);
                TrainStopover::factory([
                                           'trip_id'           => $hafasTrip->trip_id,
                                           'train_station_id'  => $stop->id,
                                           'arrival_planned'   => $time->toIso8601String(),
                                           'arrival_real'      => $time->toIso8601String(),
                                           'departure_planned' => $time->toIso8601String(),
                                           'departure_real'    => $time->toIso8601String(),
                                       ])->create();
            }

            // Create destination stopover
            $time->addMinutes(15);
            TrainStopover::factory([
                                       'trip_id'           => $hafasTrip->trip_id,
                                       'train_station_id'  => $hafasTrip->destinationStation->id,
                                       'arrival_planned'   => $hafasTrip->arrival->toIso8601String(),
                                       'arrival_real'      => $hafasTrip->arrival->toIso8601String(),
                                       'departure_planned' => $hafasTrip->arrival->toIso8601String(),
                                       'departure_real'    => $hafasTrip->arrival->toIso8601String(),
                                   ])->create();

            self::createPolyline($hafasTrip);
            $hafasTrip->refresh();
        });
    }

    public static function createPolyline(HafasTrip $hafasTrip) {
        $time     = Carbon::now()->subMinutes(15);
        $features = [];
        foreach ($hafasTrip->stopovers as $stopover) {
            $products = [];
            foreach (HafasTravelType::cases() as $hafasTravelType) {
                $products[$hafasTravelType->value] = rand(0, 1);
            }
            $features[] = [
                'type'       => 'Feature',
                'properties' => [
                    'type'     => 'stop',
                    'id'       => $stopover->trainStation->ibnr,
                    'name'     => $stopover->trainStation->name,
                    'location' => [
                        'type'      => 'location',
                        'id'        => $stopover->trainStation->ibnr,
                        'latitude'  => $stopover->trainStation->latitude,
                        'longitude' => $stopover->trainStation->longitude,
                    ],
                    'products' => $products,
                ],
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [
                        $stopover->trainStation->longitude,
                        $stopover->trainStation->latitude,
                    ]
                ]
            ];
            $time->addMinutes(30);
        }

        $polyline = json_encode([
                                    'type'     => 'FeatureCollection',
                                    'features' => $features,
                                ],
                                JSON_THROW_ON_ERROR);
        $polyline = TransportController::getPolylineHash($polyline);

        $hafasTrip->update(['polyline_id' => $polyline->id]);
    }
}
