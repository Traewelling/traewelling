<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class Blogpost20170801 extends Seeder
{

    public function run(): void {
        Blogpost::create([
                             'title'          => 'API-Probleme im In- & Ausland',
                             'slug'           => 'api-probleme',
                             'author_name'    => 'Levin Herr',
                             'twitter_handle' => 'HerrLevin_',
                             'published_at'   => '2017-08-01 00:00:00',
                             'body'           => '
Bei uns ist intern einiges schief gelaufen. Es ging total an uns vorbei, dass opendata.ch ihre [Api abgeändert hat](https://opendata.ch/2017/06/search-ch-rettet-transport-opendata-ch/). So dachten wir, die Fehler, wie fehlende Abfahrten an gewissen Bahnhöfen, die gestern und heute (ab 31.07.2017) auftraten, waren ein typischer Schluckauf der SBB-API. Weit gefehlt.

![Nachricht auf der API-Webseite, alles kaputt.](/img/blog/api-umstellung-sbb.png)

Wir konnten jetzt kurzfristig auf eine temporäre Lösung umstellen: eine selbstgehostete Schnittstelle der API. **So ist die volle Funktion leider auch nicht gewährleistet.**

Wir müssen uns jetzt nach einer neuen Lösung umsehen, wie zum Beispiel das [Opendata-Programm der Bahn](https://data.deutschebahn.com/). Nur leider haben die für Züge keine Live-Daten, sondern nur [Soll](https://data.deutschebahn.com/dataset/api-fahrplan).

Wir werden euch auf jeden Fall auf dem Laufenden halten und sollten weiterhin solche großen Bugs auftreten (nicht, wenn ein Zug nicht geht – das passiert häufig mit der API), meldet euch direkt per Twitter an [@HerrLevin_](https://twitter.com/HerrLevin_).

Bis dahin euch allen noch eine gute Reise und viel Spaß mit Träwelling.

Grüße
Levin',
                             'category'       => 'Fehlerbehebung',
                         ]);
    }
}
