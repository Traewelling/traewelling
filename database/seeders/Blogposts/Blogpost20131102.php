<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class Blogpost20131102 extends Seeder
{

    public function run(): void {
        Blogpost::create([

                             'title'          => 'Wir haben einen Blog, yay!',
                             'slug'           => 'wir-haben-einen-blog-yay',
                             'author_name'    => 'Levin Herr',
                             'twitter_handle' => 'HerrLevin_',
                             'published_at'   => '2013-11-02 00:00:00',
                             'body'           => 'In unserem ersten Blogpost möchte ich unsere zwei neuen Mitglieder im #Träwelling-Team recht herzlich begrüßen: @janh97 (Account inzwischen gelöscht) im Bereich “Design&Code” und @nerdhair_ (Account inzwischen gelöscht) im Bereich “Marketing".

Wir arbeiten momentan hart daran, Fernbus-Unternehmen in unser System zu integrieren, die Benutzeroberfläche zu optimieren und viele neue Funktionen einzubauen.

Grüße, Levin.',
                             'category'       => 'Bekanntmachungen'
                         ]);
    }
}
