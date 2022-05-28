<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowTableSeeder extends Seeder
{
    public function run(): void {
        $gertrud = User::where('username', 'Gertrud123')->first();
        $bob     = User::where('username', 'bob')->first();

        Follow::factory([
                            'user_id'   => $gertrud->id,
                            'follow_id' => $bob->id,
                        ])->create();
    }
}
