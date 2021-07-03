<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class Blogpost20131129 extends Seeder
{

    public function run(): void {
        Blogpost::create([
                             'title'          => 'Neues Update - 0.1.2.5-0009 β',
                             'slug'           => 'neues_update',
                             'author_name'    => 'Levin Herr',
                             'twitter_handle' => 'HerrLevin_',
                             'published_at'   => '2013-11-29 00:00:00',
                             'body'           => '
Heute habe ich ein kleines Update (Version 0.1.2.5-0009 β) herausgebracht, mit einem Feature, das “vieles” verändern wird.

Bis jetzt wurde der Nutzerrang immer nach den bis jetzt erreichten Punkten ausgerechnet, wodurch diejenigen profitierten, die #Träwelling schon etwas länger haben. Weshalb die Nutzer mit 1000 Punkten+ eigentlich fast nicht zu erreichen waren.

Im neuen Update wird der Rang nach “Wochenpunktzahl” ausgerechnet.
Wie es der Name schon sagt, bleiben die Punkte etwa eine Woche bestehen.

Jeden Freitag um etwa 23:50 Uhr werden die Punkte auf ein fünfundzwanzigstel minimiert, was anderen Nutzern die Möglichkeit geben soll, schneller im Rang aufsteigen zu können.

An dem Problem, dass ein Status keine Start-/Ankunftszeit, sowie Punkte hat wird noch gearbeitet.

Weiteres:
* Jeder neue Tweet enthält ab jetzt die URL [traewelling.de](https://traewelling.de)
* Auf dem Server wurde ein wenig aufgeräumt, wodurch das alles (hoffentlich) etwas schneller läuft.

Grüße, Levin',
                             'category'       => 'Update'
                         ]);
    }
}
