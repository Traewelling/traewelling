<?php

namespace Database\Seeders;

use App\Models\HafasTrip;
use Illuminate\Database\Seeder;

class HafasTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        HafasTrip::factory()->count(50)->create();
    }
}
