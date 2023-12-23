<?php

namespace Database\Factories;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\TransportController;
use App\Models\HafasOperator;
use App\Models\HafasTrip;
use App\Models\Station;
use App\Models\Stopover;
use Illuminate\Database\Eloquent\Factories\Factory;

class HafasTripFactory extends Factory
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
            'origin'         => $origin->ibnr,
            'destination'    => $destination->ibnr,
            'polyline_id'    => null, //Will be set in the configure function
            'departure'      => now()->subMinutes(15)->format('c'),
            'arrival'        => now()->addMinutes(80)->format('c'),
            'delay'          => 0, //TODO: is deprecated? used?
            'source'         => TripSource::HAFAS,
        ];
    }

    public function configure(): static {
        return $this->afterCreating(function(HafasTrip $hafasTrip) {
            $stops = Station::inRandomOrder()
                            ->whereNotIn('id', [$hafasTrip->originStation->id, $hafasTrip->destinationStation->id])
                            ->limit(2)
                            ->get();
            if ($stops->count() < 2) {
                for ($i = 0; $i < 2; $i++) {
                    $stops->push(Station::factory()->create());
                }
            }

            $time = $hafasTrip->departure->clone();

            // Create origin stopover
            Stopover::factory([
                                       'trip_id'           => $hafasTrip->trip_id,
                                       'train_station_id'  => $hafasTrip->originStation->id,
                                       'arrival_planned'   => $hafasTrip->departure,
                                       'departure_planned' => $hafasTrip->departure,
                                   ])->create();

            // Create intermediate stopovers
            foreach ($stops as $stop) {
                $time = $time->clone()->addMinutes(15);
                Stopover::factory([
                                           'trip_id'           => $hafasTrip->trip_id,
                                           'train_station_id'  => $stop->id,
                                           'arrival_planned'   => $time,
                                           'departure_planned' => $time,
                                       ])->create();
            }

            // Create destination stopover
            Stopover::factory([
                                       'trip_id'           => $hafasTrip->trip_id,
                                       'train_station_id'  => $hafasTrip->destinationStation->id,
                                       'arrival_planned'   => $hafasTrip->arrival,
                                       'departure_planned' => $hafasTrip->arrival,
                                   ])->create();

            self::createPolyline($hafasTrip);
            $hafasTrip->refresh();
        });
    }

    public static function createPolyline(HafasTrip $hafasTrip) {
        $time     = now()->subMinutes(15);
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

        $hafasTrip->update(['polyline_id' => $polyline->id]);
    }
}
