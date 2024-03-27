<?php

namespace Database\Factories;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\TransportController;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    public function definition(): array {
        if (Station::all()->count() > 3) {
            $origin      = Station::inRandomOrder()->first();
            $destination = Station::inRandomOrder()->where('id', '!=', $origin->id)->first();
        } else {
            $origin      = Station::factory()->create();
            $destination = Station::factory()->create();
        }

        return [
            'trip_id'        => $this->faker->unique()->numerify('1|######|##|##|') . now()->format('dmY'),
            'category'       => $this->faker->randomElement(HafasTravelType::cases())->value,
            'number'         => $this->faker->bothify('??-##'),
            'linename'       => $this->faker->bothify('?? ##'),
            'journey_number' => $this->faker->numberBetween(10000, 99999),
            'operator_id'    => HafasOperator::factory()->create()->id,
            'origin_id'      => $origin->id,
            'destination_id' => $destination->id,
            'polyline_id'    => null, //Will be set in the configure function
            'departure'      => now()->subMinutes(15)->format('c'),
            'arrival'        => now()->addMinutes(80)->format('c'),
            'source'         => TripSource::HAFAS,
        ];
    }

    public function configure(): static {
        return $this->afterCreating(function(Trip $trip) {
            $stops = Station::inRandomOrder()
                            ->whereNotIn('id', [$trip->originStation->id, $trip->destinationStation->id])
                            ->limit(2)
                            ->get();
            if ($stops->count() < 2) {
                for ($i = 0; $i < 2; $i++) {
                    $stops->push(Station::factory()->create());
                }
            }

            $time = $trip->departure->clone();

            // Create origin stopover
            Stopover::factory([
                                  'trip_id'           => $trip->trip_id,
                                  'train_station_id'  => $trip->originStation->id,
                                  'arrival_planned'   => $trip->departure,
                                  'departure_planned' => $trip->departure,
                              ])->create();

            // Create intermediate stopovers
            foreach ($stops as $stop) {
                $time = $time->clone()->addMinutes(15);
                Stopover::factory([
                                      'trip_id'           => $trip->trip_id,
                                      'train_station_id'  => $stop->id,
                                      'arrival_planned'   => $time,
                                      'departure_planned' => $time,
                                  ])->create();
            }

            // Create destination stopover
            Stopover::factory([
                                  'trip_id'           => $trip->trip_id,
                                  'train_station_id'  => $trip->destinationStation->id,
                                  'arrival_planned'   => $trip->arrival,
                                  'departure_planned' => $trip->arrival,
                              ])->create();

            self::createPolyline($trip);
            $trip->refresh();
        });
    }

    public static function createPolyline(Trip $trip) {
        $time     = now()->subMinutes(15);
        $features = [];
        foreach ($trip->stopovers as $stopover) {
            $products = [];
            foreach (HafasTravelType::cases() as $hafasTravelType) {
                $products[$hafasTravelType->value] = rand(0, 1);
            }
            $features[] = [
                'type'       => 'Feature',
                'properties' => [
                    'type'     => 'stop',
                    'id'       => $stopover->station->ibnr,
                    'name'     => $stopover->station->name,
                    'location' => [
                        'type'      => 'location',
                        'id'        => $stopover->station->ibnr,
                        'latitude'  => $stopover->station->latitude,
                        'longitude' => $stopover->station->longitude,
                    ],
                    'products' => $products,
                ],
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [
                        $stopover->station->longitude,
                        $stopover->station->latitude,
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

        $trip->update(['polyline_id' => $polyline->id]);
    }
}
