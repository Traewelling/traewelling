<?php

namespace Database\Factories;

use App\Faker\StationFaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    public function definition(): array {
        $this->faker->addProvider(new StationFaker($this->faker));

        /** @var StationFaker $station */
        $station = $this->faker->unique()->station();
        $ifopt = $this->getIfopt($station['ifopt'] ?? '');
        return [
            'ibnr'          => $station['ibnr'] ?? null,
            'wikidata_id'   => $station['wikidata_id'],
            'ifopt_a'       => $ifopt['ifopt_a'],
            'ifopt_b'       => $ifopt['ifopt_b'],
            'ifopt_c'       => $ifopt['ifopt_c'],
            'ifopt_d'       => $ifopt['ifopt_d'],
            'ifopt_e'       => $ifopt['ifopt_e'],
            'rilIdentifier' => $station['rl100'] ?? null,
            'name'          => $station['name'],
            'latitude'      => $station['latitude'],
            'longitude'     => $station['longitude'],
            'time_offset'   => null,
            'shift_time'    => null,
        ];
    }

    private function getIfopt(string $ifopt): array {
        $splitIfopt = explode(':', $ifopt);
        return [
            'ifopt_a' => $splitIfopt[0] ?? null,
            'ifopt_b' => $splitIfopt[1] ?? null,
            'ifopt_c' => $splitIfopt[2] ?? null,
            'ifopt_d' => $splitIfopt[3] ?? null,
            'ifopt_e' => $splitIfopt[4] ?? null,
        ];
    }
}
