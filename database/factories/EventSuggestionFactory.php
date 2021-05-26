<?php

namespace Database\Factories;

use App\Models\EventSuggestion;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class EventSuggestionFactory extends Factory
{

    protected $model = EventSuggestion::class;

    #[ArrayShape([
        'user_id'          => "\Illuminate\Database\Eloquent\Factories\Factory",
        'name'             => "string",
        'hashtag'          => "string",
        'host'             => "string",
        'url'              => "string",
        'train_station_id' => "\Illuminate\Database\Eloquent\Factories\Factory",
        'begin'            => "string",
        'end'              => "string"
    ])]
    public function definition(): array {
        $begin = Carbon::today()->addDays(rand(0, 30));
        $end   = $begin->clone()->addDays(3);

        return [
            'user_id'          => User::factory(),
            'name'             => $this->faker->word,
            'hashtag'          => $this->faker->word,
            'host'             => $this->faker->company,
            'url'              => $this->faker->url,
            'train_station_id' => TrainStation::factory(),
            'begin'            => $begin->toIso8601String(),
            'end'              => $end->toIso8601String(),
        ];
    }
}
