<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FollowTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('follows')->insert([
                                         'user_id'   => rand(1, 50),
                                         'follow_id' => rand(1, 50),
                                     ]);
    }
}
