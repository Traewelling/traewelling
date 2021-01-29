<?php

use Illuminate\Database\Migrations\Migration;

class InsertBlogpostOpenSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::table('blogposts')->insert([
                                           'title'          => 'Träwelling goes Open Source!',
                                           'slug'           => 'open-source-announcement',
                                           'author_name'    => 'Das Träwelling-Team',
                                           'twitter_handle' => 'traewelling',
                                           'published_at'   => '2020-02-20 00:00:00',
                                           'category'       => 'Bekanntmachungen',
                                           'body'           => 'Seit fast 7 Monaten arbeiten wir in einem kleinen Team an Träwelling und haben eine Menge geschafft: Ein neues User-Interface, eine Detail-Seite für Deinen Check-In und die Möglichkeit, sich mit Mastodon anzumelden, sind die wohl bekanntesten Features. Unter der Haube versuchen wir, die komplexe Bahnwelt zu reflektieren; immerhin werden jeden Tag über 100 Verbindungen in Träwelling getrackt.

Ein Teil unserer Nutzer*innen möchte immer mehr von der Plattform. Auf dem 36C3 und darüber hinaus haben wir viele Zuschriften erhalten, die sich bestimmte Features (Private Reisen, ein besserer Export, etc.) wünschen. Glücklicherweise gibt es eine große Überlappung zwischen der Bahn-Bubble und der Chaos-/Open-Source-Szene, die in der Lage ist - und schon angeboten hat - sich bei Träwelling zu engagieren.

Unter [`github.com/Traewelling/traewelling`](https://github.com/Traewelling/traewelling) haben wir unsere bisherige Arbeit veröffentlicht und würden uns freuen, wenn sich jemensch angesprochen fühlt, die Plattform wachsen zu lassen. Träwelling hat eine tolle Userbase, die sicherlich einige Ideen hat, wie sie die Software - für alle Nutzer*innen und sich selbst - verbessern kann. Das Core-Team hat durch Arbeit, Studium und Träwelln nicht immer die Zeit, die das Projekt verdient.

## Technisches

Ein [Readme](https://github.com/Traewelling/traewelling) zeigt Dir den Weg, eine eigene Träwelling-Instanz aufzusetzen. Du benötigst PHP, eine Datenbank Deiner Wahl* (die Laravel unterstützt) und für Frontend-Änderungen noch NodeJS.

Wenn Du Fragen hast, schreib einfach an [@traewelling](https://twitter.com/traewelling) auf Twitter oder [@traewelling@chaos.social](https://chaos.social/@traewelling) auf Mastodon.

## Lizenz?

Wir haben die [AGPLv3](https://www.gnu.org/licenses/agpl-3.0.html) ausgewählt, weil wir mit unseren Entwicklungen öffentlich sein möchten und das von unseren Entwicklern auch erwarten. Bitte nimm den Code nicht und setze Deine eigene private Instanz - nutze stattdessen Dein Wissen, um Träwelling für alle Nutzer*innen zu verbessern.'
                                       ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
