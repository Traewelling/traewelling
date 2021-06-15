<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointsCalculationSeeder extends Seeder
{

    public function run(): void {
        $rows = [
            ['type' => 'train', 'transport_type' => 'tram', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'bus', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'subway', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'suburban', 'value' => '3'],
            ['type' => 'train', 'transport_type' => 'ferry', 'value' => '3'],
            ['type' => 'train', 'transport_type' => 'regional', 'value' => '5'],
            ['type' => 'train', 'transport_type' => 'regionalExp', 'value' => '6'],
            ['type' => 'train', 'transport_type' => 'express', 'value' => '10'],
            ['type' => 'train', 'transport_type' => 'national', 'value' => '10'],
            ['type' => 'train', 'transport_type' => 'nationalExpress', 'value' => '10'],
        ];

        DB::table('pointscalculation')->insert($rows);
    }
}
