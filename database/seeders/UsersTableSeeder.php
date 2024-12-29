<?php

namespace Database\Seeders;

use App\Models\IcsToken;
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
    public function run(): void {
        // For better Testing, we'll create private and public users.
        if (!User::where("username", "=", "Gertrud123")->count()) {
            $gertrud = User::factory()
                           ->has(IcsToken::factory(rand(0, 2)))
                           ->create([
                                        'username' => 'Gertrud123',
                                        'name'     => 'Gertrud',
                                        'email'    => 'gertrud@traewelling.de',
                                        'password' => Hash::make('thisisnotasecurepassword123'),
                                    ]);
            $gertrud->assignRole('admin');
            $gertrud->assignRole('open-beta');
            $gertrud->assignRole('closed-beta');
        }

        if (!User::where("username", "=", "bob")->count()) {
            User::factory()
                ->create([
                             'username'        => 'bob',
                             'name'            => 'Bob from Alice & Bob',
                             'avatar'          => null, // no avatar
                             'email'           => 'bob@example.com',
                             'private_profile' => true,
                             'password'        => Hash::make('thisisnotasecurepassword123')
                         ]);
        }

        User::factory()
            ->has(IcsToken::factory(rand(0, 2)))
            ->count(10)
            ->create();
    }
}
