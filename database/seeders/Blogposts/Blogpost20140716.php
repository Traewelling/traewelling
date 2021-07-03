<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class Blogpost20140716 extends Seeder
{

    public function run(): void {
        Blogpost::create([
                             'title'          => 'Wir haben eine App! Irgendwie...',
                             'slug'           => 'wir-haben-eine-app',
                             'author_name'    => 'Levin Herr',
                             'twitter_handle' => 'HerrLevin_',
                             'published_at'   => '2014-07-16 00:00:00',
                             'body'           => 'Ihr wartet alle schon sehnsüchtig auf eine App und endlich habe ich (Levin) es geschafft, eine total crappige App zu schreiben. Yay!

Um diese App auf Herz und Nieren zu testen, brauchen wir natürlich ein paar Alpha-/Beta-Tester und dafür brauchen wir Dich! Ja, genau Dich!

Jetzt fragst du dich sicher: “Was muss ich dazu tun? Ihr wollt doch sicher Geld oder meine Seele! D:" Gut, Geld wollen wir immer, aber das ist eine andere Sache.

Es ist eigentlich ganz einfach:

**Die Registration ist inzwischen geschlossen.**

* Registriere dich auf traewelling.de mit deinem Twitter-Account (falls du das noch nicht getan hast)
* Fülle das Formular auf [goo.gl/QimitR](https://goo.gl/QimitR) aus
* Warte auf unsere Antwort
* Teste drauf los

Wir freuen uns auf euere Bewerbungen.
',
                             'category'       => 'Bekanntmachungen',
                         ]);
    }
}
