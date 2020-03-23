<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 50)->create()->each(function ($u) {
            $u->save();
        });
        $user = new \App\User([
                          'username'          => 'Gertrud123',
                          'name'              => 'Gertrud',
                          'email'             => 'gertrud@traewelling.de',
                          'email_verified_at' => now(),
                          'password'          => Hash::make('thisisnotasecurepassword123')]);
        $user->save();
    }
}
