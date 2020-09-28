<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        User::factory()->create([
                                    'username' => 'Gertrud123',
                                    'name'     => 'Gertrud',
                                    'email'    => 'gertrud@traewelling.de',
                                    'password' => Hash::make('thisisnotasecurepassword123')
                                ]);

        User::factory()->count(10)->create();
    }
}
