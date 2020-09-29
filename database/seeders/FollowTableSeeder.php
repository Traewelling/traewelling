<?php

namespace Database\Seeders;

use App\Models\User;
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
                                         'user_id'   => User::all()->random()->id,
                                         'follow_id' => User::all()->random()->id,
                                     ]);
    }
}
