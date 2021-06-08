<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('blogposts', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug');
            $table->string('author_name');
            $table->string('twitter_handle');
            $table->datetimeTz('published_at')->useCurrent();
            $table->text('body');
            $table->string('category');
            $table->timestamps();
        });


        // Import the existing blogposts
        DB::table('blogposts')->insert([
                                           [
                                               'title'          => 'Wir haben einen Blog, yay!',
                                               'slug'           => 'wir-haben-einen-blog-yay',
                                               'author_name'    => 'Levin Herr',
                                               'twitter_handle' => 'HerrLevin_',
                                               'published_at'   => '2013-11-02 00:00:00',
                                               'created_at'     => '2013-11-02 00:00:00',
                                               'updated_at'     => '2013-11-02 00:00:00',
                                               'body'           => 'In unserem ersten Blogpost möchte ich unsere zwei neuen Mitglieder im #Träwelling-Team recht herzlich begrüßen: @janh97 (Account inzwischen gelöscht) im Bereich “Design&Code” und @nerdhair_ (Account inzwischen gelöscht) im Bereich “Marketing".

Wir arbeiten momentan hart daran, Fernbus-Unternehmen in unser System zu integrieren, die Benutzeroberfläche zu optimieren und viele neue Funktionen einzubauen.

Grüße, Levin.',
                                               'category'       => 'Bekanntmachungen'
                                           ],
                                           [
                                               'title'          => 'Neues Update - 0.1.2.5-0009 β',
                                               'slug'           => 'neues_update',
                                               'author_name'    => 'Levin Herr',
                                               'twitter_handle' => 'HerrLevin_',
                                               'published_at'   => '2013-11-29 00:00:00',
                                               'created_at'     => '2013-11-29 00:00:00',
                                               'updated_at'     => '2013-11-29 00:00:00',
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
                                           ],
                                           [
                                               'title'          => 'Wir haben eine App! Irgendwie...',
                                               'slug'           => 'wir-haben-eine-app',
                                               'author_name'    => 'Levin Herr',
                                               'twitter_handle' => 'HerrLevin_',
                                               'published_at'   => '2014-07-16 00:00:00',
                                               'created_at'     => '2014-07-16 00:00:00',
                                               'updated_at'     => '2014-07-16 00:00:00',
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
                                           ],
                                           [
                                               'title'          => 'API-Probleme im In- & Ausland',
                                               'slug'           => 'api-probleme',
                                               'author_name'    => 'Levin Herr',
                                               'twitter_handle' => 'HerrLevin_',
                                               'published_at'   => '2017-08-01 00:00:00',
                                               'created_at'     => '2017-08-01 00:00:00',
                                               'updated_at'     => '2017-08-01 00:00:00',
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
                                           ],
                                       ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('blogposts');
    }
}
