<?php

/** @var Factory $factory */

use App\User;
use App\Status;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;


$factory->define(Status::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'user_id' => function () {
            return factory(App\Status::class)->create()->id;
        }
    ];
});
