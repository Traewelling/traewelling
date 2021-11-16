<?php

namespace Database\Factories;

use App\Enum\HafasTravelType;
use App\Http\Controllers\TransportController;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HafasTripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HafasTrip::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array {
        $stops     = TrainStation::inRandomOrder()->limit(4)->get();
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
                                ]);
        $polyline = TransportController::getPolylineHash($polyline);
        return [
            'trip_id'     => $this->faker->unique()->numerify('1|######|##|##|') . Carbon::now()->format('dmY'),
            'category'    => $this->faker->randomElement(HafasTravelType::cases())->value,
            'number'      => $this->faker->bothify('??-##'),
            'linename'    => $this->faker->bothify('?? ##'),
            'origin'      => $stops[0]->ibnr,
            'destination' => $stops[3]->ibnr,
            'stopovers'   => json_encode($stopOvers),
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
    public function configure() {
        return $this->afterCreating(function(HafasTrip $hafasTrip) {
            if (!isset($hafasTrip->stopovers)) {
                return;
            }
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
        });
    }
}
