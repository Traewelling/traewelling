<?php

namespace Database\Seeders;

use App\Http\Controllers\UserController;
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
        $gertrud = User::first();
        $bob     = User::where('username', 'bob')->first();

        DB::table('follows')->insert([
                                         'user_id'   => User::all()->random()->id,
                                         'follow_id' => User::all()->random()->id,
                                     ]);
    }
}
