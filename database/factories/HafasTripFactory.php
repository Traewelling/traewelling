<?php

namespace Database\Factories;

use App\Enum\HafasTravelType;
use App\Http\Controllers\TransportController;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use JsonException;

class HafasTripFactory extends Factory
{
    public function definition(): array {
        $stops = TrainStation::inRandomOrder()->limit(4)->get();
        if ($stops->count() < 4) {
            for ($i = 0; $i < 4; $i++) {
                $stops->push(TrainStation::factory()->create());
            }
        }
        $features  = [];
        $stopOvers = [];
        $time      = Carbon::now()->subMinutes(15);
        foreach ($stops as $stop) {
            $products = [];
            foreach (HafasTravelType::cases() as $hafasTravelType) {
                $products[$hafasTravelType->value] = $this->faker->boolean();
            }
            $features[]  = [
                'type'       => 'Feature',
                'properties' => [
                    'type'     => 'stop',
                    'id'       => $stop->ibnr,
                    'name'     => $stop->name,
                    'location' => [
                        'type'      => 'location',
                        'id'        => $stop->ibnr,
                        'latitude'  => $stop->latitude,
                        'longitude' => $stop->longitude,
                    ],
                    'products' => $products,
                ],
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [
                        $stop->longitude,
                        $stop->latitude,
                    ]
                ]
            ];
            $stopOvers[] = [
                'stop'                     => [
                    'type'     => 'stop',
                    'id'       => $stop->ibnr,
                    'name'     => $stop->name,
                    'location' => [
                        'type'      => 'location',
                        'id'        => $stop->ibnr,
                        'latitude'  => $stop->latitude,
                        'longitude' => $stop->longitude,
                    ],
                    'products' => $products,
                ],
                'arrival'                  => $time->toIso8601String(),
                'plannedArrival'           => $time->toIso8601String(),
                'arrivalDelay'             => null,
                'arrivalPlatform'          => null,
                'plannedArrivalPlatform'   => null,
                'departure'                => $time->toIso8601String(),
                'plannedDeparture'         => $time->toIso8601String(),
                'departureDelay'           => null,
                'departurePlatform'        => null,
                'plannedDeparturePlatform' => null,
            ];
            $time->addMinutes(30);
        }

        $polyline = json_encode([
                                    'type'     => 'FeatureCollection',
                                    'features' => $features,
                                ],
                                JSON_THROW_ON_ERROR);
        $polyline = TransportController::getPolylineHash($polyline);
        return [
            'trip_id'     => $this->faker->unique()->numerify('1|######|##|##|') . Carbon::now()->format('dmY'),
            'category'    => $this->faker->randomElement(HafasTravelType::cases())->value,
            'number'      => $this->faker->bothify('??-##'),
            'linename'    => $this->faker->bothify('?? ##'),
            'origin'      => $stops[0]->ibnr,
            'destination' => $stops[3]->ibnr,
            'stopovers'   => json_encode($stopOvers, JSON_THROW_ON_ERROR),
            'departure'   => Carbon::now()->subMinutes(15)->format('c'),
            'arrival'     => Carbon::now()->addMinutes(80)->format('c'),
            'delay'       => null,
            'polyline_id' => $polyline->id,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static {
        return $this->afterCreating(function(HafasTrip $hafasTrip) {
            //TODO: Replace ->stopovers with the new relation (see issue#425)
            if (!isset($hafasTrip->stopovers)) {
                return;
            }
            //TODO: Replace ->stopovers with the new relation (see issue#425)
            $stopOvers = json_decode($hafasTrip->stopovers);
            $startTime = $hafasTrip->departure;
            $endTime   = $hafasTrip->arrival;
            $cnt       = count($stopOvers);
            $interval  = $endTime->diffInSeconds($startTime) / $cnt;
            $add       = $interval;

            for ($i = 1; $i < $cnt; $i++) {
                $stopOvers[$i]->plannedArrival   = $startTime->clone()->addSeconds($add)->format('c');
                $stopOvers[$i]->arrival          = $startTime->clone()->addSeconds($add)->format('c');
                $stopOvers[$i]->plannedDeparture = $stopOvers[$i]->arrival;
                $stopOvers[$i]->departure        = $stopOvers[$i]->arrival;

                $add += $interval;
            }
            $stopOvers[0]->arrival                 = $startTime->format('c');
            $stopOvers[0]->plannedArrival          = $startTime->format('c');
            $stopOvers[0]->departure               = $startTime->format('c');
            $stopOvers[0]->plannedDeparture        = $startTime->format('c');
            $stopOvers[$cnt - 1]->arrival          = $endTime->format('c');
            $stopOvers[$cnt - 1]->plannedArrival   = $endTime->format('c');
            $stopOvers[$cnt - 1]->departure        = $endTime->format('c');
            $stopOvers[$cnt - 1]->plannedDeparture = $endTime->format('c');

            $hafasTrip->update(['stopovers' => json_encode($stopOvers)]);

            foreach ($stopOvers as $legacyStopover) {
                $trainStation = TrainStation::where('ibnr', $legacyStopover->stop->id)->first();
                if ($trainStation === null) {
                    //Some tests specify a stopover that does not exist (because of using real data)
                    $trainStation = TrainStation::factory()->create([
                                                                        'ibnr' => $legacyStopover->stop->id,
                                                                        'name' => $legacyStopover->stop->name,
                                                                    ]);
                }


                TrainStopover::create([
                                          'trip_id'                    => $hafasTrip->trip_id,
                                          'train_station_id'           => $trainStation->id,
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
            $hafasTrip->refresh();
        });
    }
}
