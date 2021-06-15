<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class BlogpostSeeder extends Seeder
{

    public function run(): void {
        //TODO: Until we have a new solution for our blog this will be the best way... urgh..
        $this->call(Blogpost20131102::class);
        $this->call(Blogpost20131129::class);
        $this->call(Blogpost20140716::class);
        $this->call(Blogpost20170801::class);
        $this->call(Blogpost20191124::class);
        $this->call(Blogpost20200220::class);
    }
}
