<?php

namespace Database\Factories;

use App\Models\HafasTrip;
use App\Http\Controllers\TransportController;
use App\Models\TrainStation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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
    public function definition() {

        $stops     = TrainStation::inRandomOrder()->limit(4)->get();
        $features  = [];
        $stopOvers = [];
        $time      = -15;
        foreach ($stops as $stop) {
            array_push($features, [
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
                    'products' => [
                        'nationalExpress' => $this->faker->boolean(50),
                        'national'        => $this->faker->boolean(50),
                        'regionalExp'     => $this->faker->boolean(50),
                        'regional'        => $this->faker->boolean(50),
                        'suburban'        => $this->faker->boolean(50),
                        'bus'             => $this->faker->boolean(50),
                        'ferry'           => $this->faker->boolean(50),
                        'subway'          => $this->faker->boolean(50),
                        'tram'            => $this->faker->boolean(50),
                        'taxi'            => $this->faker->boolean(50),
                    ],
                ],
                'geometry'   => [
                    'type'        => 'Point',
                    'coordinates' => [
                        $stop->longitude,
                        $stop->latitude,
                    ]
                ]
            ]);
            array_push($stopOvers, [
                'stop'              => [
                    'type'     => 'stop',
                    'id'       => $stop->ibnr,
                    'name'     => $stop->name,
                    'location' => [
                        'type'      => 'location',
                        'id'        => $stop->ibnr,
                        'latitude'  => $stop->latitude,
                        'longitude' => $stop->longitude,
                    ],
                    'products' => [
                        'nationalExpress' => $this->faker->boolean(50),
                        'national'        => $this->faker->boolean(50),
                        'regionalExp'     => $this->faker->boolean(50),
                        'regional'        => $this->faker->boolean(50),
                        'suburban'        => $this->faker->boolean(50),
                        'bus'             => $this->faker->boolean(50),
                        'ferry'           => $this->faker->boolean(50),
                        'subway'          => $this->faker->boolean(50),
                        'tram'            => $this->faker->boolean(50),
                        'taxi'            => $this->faker->boolean(50),
                    ],
                ],
                'arrival'           => date('Y-m-d\TH:i:sP', strtotime($time . 'min')),
                'arrivalDelay'      => null,
                'arrivalPlatform'   => null,
                'departure'         => date('Y-m-d\TH:i:sP', strtotime($time . 'min')),
                'departureDelay'    => null,
                'departurePlatform' => null,
            ]);
            $time += 30;
        }

        $polyline     = json_encode(['type'     => 'FeatureCollection',
                                     'features' => $features]);
        $polylineHash = TransportController::getPolylineHash($polyline)->hash;
        return [
            'trip_id'     => $this->faker->unique()->numerify('1|######|##|##|') . date('dmY'),
            'category'    => DB::table('pointscalculation')->where('type', 'train')->get()->random()->transport_type,
            'number'      => $this->faker->bothify('??-##'),
            'linename'    => $this->faker->bothify('?? ##'),
            'origin'      => $stops[0]->ibnr,
            'destination' => $stops[3]->ibnr,
            'stopovers'   => json_encode($stopOvers),
            'departure'   => date('Y-m-d H:i:s', strtotime('-15min')),
            'arrival'     => date('Y-m-d H:i:s', strtotime('+80min')),
            'delay'       => null,
            'polyline'    => $polylineHash,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure() {
        return $this->afterCreating(function(HafasTrip $hafasTrip) {
            $stopOvers = json_decode($hafasTrip->stopovers);
            $startTime = $hafasTrip->departure;
            $endTime   = $hafasTrip->arrival;
            $cnt       = count($stopOvers);
            $interval  = (strtotime($endTime) - strtotime($startTime)) / $cnt;
            $add       = $interval;

            for ($i = 1; $i < $cnt; $i++) {
                $stopOvers[$i]->arrival   = date('Y-m-d H:i:s', strtotime($startTime) + $add);
                $stopOvers[$i]->departure = date('Y-m-d H:i:s', strtotime($startTime) + $add);
                $add                      += $interval;
            }
            $stopOvers[0]->arrival          = date('Y-m-d H:i:s', strtotime($startTime));
            $stopOvers[0]->departure        = date('Y-m-d H:i:s', strtotime($startTime));
            $stopOvers[$cnt - 1]->arrival   = date('Y-m-d H:i:s', strtotime($endTime));
            $stopOvers[$cnt - 1]->departure = date('Y-m-d H:i:s', strtotime($endTime));


            $hafasTrip->stopovers = json_encode($stopOvers);
            $hafasTrip->save();
        });
    }

}
