<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\TrainStations;
use Faker\Generator as Faker;

$factory->define(TrainStations::class, function (Faker $faker) {
    return [
        'ibnr'      => $faker->unique()->numberBetween(8000001, 8999999),
        'name'      => $faker->unique()->city,
        'latitude'  => $faker->latitude,
        'longitude' => $faker->longitude
    ];
});
