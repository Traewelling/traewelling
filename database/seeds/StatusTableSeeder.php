<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Status::class, 50)->create()->each(function ($u) {
            $u->save();
            DB::table('statuses')->insert([
                                             'body' => rand(1,50),
                                             'user_id' => rand(1,50),
                                         ]);
        });
    }
}
