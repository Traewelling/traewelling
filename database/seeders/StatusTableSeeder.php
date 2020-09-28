<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Status::factory()->count(50)->create()->each(function($u) {
            $u->save();
            DB::table('statuses')->insert([
                                              'body'    => rand(1, 50),
                                              'user_id' => rand(1, 50),
                                          ]);
        });
    }
}
