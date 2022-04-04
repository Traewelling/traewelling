<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use Illuminate\Database\Seeder;

class HafasTripSeeder extends Seeder
{

    public function run(): void {
        HafasTrip::factory()->count(50)->create();
    }
}
