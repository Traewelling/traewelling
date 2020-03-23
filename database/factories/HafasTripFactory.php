<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\HafasTrip;
use App\TrainStations;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\TransportController;

$factory->define(HafasTrip::class, function (Faker $faker) {
    $stops     = [
        TrainStations::all()->random(),
        TrainStations::all()->random(),
        TrainStations::all()->random(),
        TrainStations::all()->random(),
    ];
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
                     'nationalExpress' => $faker->boolean($chanceOfGettingTrue = 50),
                     'national'        => $faker->boolean($chanceOfGettingTrue = 50),
                     'regionalExp'     => $faker->boolean($chanceOfGettingTrue = 50),
                     'regional'        => $faker->boolean($chanceOfGettingTrue = 50),
                     'suburban'        => $faker->boolean($chanceOfGettingTrue = 50),
                     'bus'             => $faker->boolean($chanceOfGettingTrue = 50),
                     'ferry'           => $faker->boolean($chanceOfGettingTrue = 50),
                     'subway'          => $faker->boolean($chanceOfGettingTrue = 50),
                     'tram'            => $faker->boolean($chanceOfGettingTrue = 50),
                     'taxi'            => $faker->boolean($chanceOfGettingTrue = 50),
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
                    'nationalExpress' => $faker->boolean($chanceOfGettingTrue = 50),
                    'national'        => $faker->boolean($chanceOfGettingTrue = 50),
                    'regionalExp'     => $faker->boolean($chanceOfGettingTrue = 50),
                    'regional'        => $faker->boolean($chanceOfGettingTrue = 50),
                    'suburban'        => $faker->boolean($chanceOfGettingTrue = 50),
                    'bus'             => $faker->boolean($chanceOfGettingTrue = 50),
                    'ferry'           => $faker->boolean($chanceOfGettingTrue = 50),
                    'subway'          => $faker->boolean($chanceOfGettingTrue = 50),
                    'tram'            => $faker->boolean($chanceOfGettingTrue = 50),
                    'taxi'            => $faker->boolean($chanceOfGettingTrue = 50),
                ],
            ],
            'arrival'           => date('Y-m-d\TH:i:s+01:00', strtotime($time . 'min')),
            'arrivalDelay'      => null,
            'arrivalPlatform'   => null,
            'departure'         => date('Y-m-d\TH:i:s+01:00', strtotime($time . 'min')),
            'departureDelay'    => null,
            'departurePlatform' => null,
        ]);
        $time += 30;
    }

    $polyline     = json_encode(['type'     => 'FeatureCollection',
                             'features' => $features]);
    $polylineHash = TransportController::getPolylineHash($polyline);
    return [
        'trip_id'     => $faker->unique()->numerify('1|######|##|##|') . date('dmY'),
        'category'    => DB::table('pointscalculation')->where('type', 'train')->get()->random()->transport_type,
        'number'      => $faker->bothify('??-##'),
        'linename'    => $faker->bothify('?? ##'),
        'origin'      => $stops[0]->ibnr,
        'destination' => $stops[3]->ibnr,
        'stopovers'   => json_encode($stopOvers),
        'departure'   => date('Y-m-d H:i:s', strtotime('-15min')),
        'arrival'     => date('Y-m-d H:i:s', strtotime('+80min')),
        'delay'       => null,
        'polyline'    => $polylineHash,
    ];
});
