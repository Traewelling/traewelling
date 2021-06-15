<?php

namespace Database\Seeders\Blogposts;

use App\Models\Blogpost;
use Illuminate\Database\Seeder;

class Blogpost20191124 extends Seeder
{

    public function run(): void {
        Blogpost::create([
                             'title'          => 'Träwelling-Release 1.0 - 2019',
                             'slug'           => 'traewelling-release-1-0',
                             'author_name'    => 'Levin Herr',
                             'twitter_handle' => 'HerrLevin_',
                             'published_at'   => '2019-11-24 00:00:00',
                             'category'       => 'Bekanntmachungen',
                             'body'           => 'Lange hat sich an Träwelling nichts getan, obwohl es seit 2017 mehrere Anläufe gab.

August hat uns dann der Tatendrang gepackt und jetzt, gut drei Monate später, sind wir beim ersten vollen Release von Träwelling – sechs Jahre nach Beginn des Projekts.

_Wir?!_ – ja, denn neben Levin arbeiten jetzt auch Jannik und Karl an dem Projekt.

## Was hat sich geändert?

Wir haben die meisten Features, die Träwelling besaß, wieder aufgegriffen und auf einer neuen, erweiterbaren Plattform auf Basis von Laravel reimplementiert. Desweiteren haben wir uns in der Abhängigkeit von Twitter komplett gelöst; das bedeutet, dass Träwelling ab jetzt ohne Twitter-Account genutzt werden kann.

Die meisten Änderungen gibt es hier im Überblick:

### Login- und User-Management

Neben Twitter gibt es nun auch die Möglichkeit, sich mit Mastodon oder einfach per E-Mail anzumelden. Dabei lösen wir die Abhängigkeit des Träwelling-Accounts von Twitter, sodass dieser auch gewechselt werden kann. Nebenbei machen wir den Weg für ein _Sign-in with Apple_ frei.

Die Follows haben wir ebenfalls entkoppelt: Du kannst nun Leuten folgen, denen Du auf Twitter nicht folgst und umgekehrt. Auch ist es möglich, ein anderes Profilbild als auf dem Twitter-Account einzurichten.

Das Löschen eines Profiles ist jetzt außerdem einfacher geworden. Zudem bist du nicht nur an einen einzigen Anmeldedienst gebunden – du kannst sowohl Twitter, als auch Mastodon und Email/Passwort mit deinem Account verknüpfen! (Diesmal ist auch die Twitter-Imtegration in ihren Rechten geschrumpft, sodass wir keinen Zugriff zu deinen DMs mehr brauchen.)

### Neues User Interface

Das Design ist in die Gegenwart geholt worden: Wir haben eine neue Profil-Seite, die Deine Statistik nochmal hervorhebt, und vieles baut auf den Status-Karten auf, die Du über die Anwendung verteilt findest. Gestalterisch orientieren wir uns dabei stark an Googles [Material Design](https://material.io/), arbeiten aber auf Basis von [mdbootstrap](https://mdbootstrap.com/).

Dennoch haben wir uns bemüht, die Menüpunkte ähnlich zum ursprünglichen Träwelling Desktop zu halten.

#### Neue Unterwegs-Seite

Die Unterwegs-Seite zeigt wie zuvor alle Reisen an, die mit Trävelling aktuell getrackt werden.

Neu hingegen ist die Live-Karte, die genau zeigt, wie weit die Reise gerade ist. Genauso wie der Progress-Indicator in der Status-Karte wird die Live-Karte regelmäßig aktualisiert und so der Verlauf einer Reise dargestellt.

#### Status-Seite

Wer seine Reisen twittert (_oder tootet, das geht ja jetzt auch_), wird sich über das leicht veränderte Schema wundern – denn am Ende des Tweets (_Toots_) befindet sich ein Link auf die neue Status-Seite. Dort findest Du eine Karte mit dem Zugverlauf und eine Auflistung der Leute, die deinen Status auf der Träwelling-Seite gefavt haben. Wait - faven?

### Faven!

Wir wollen Träwelling zu einem eigenen Netzwerk machen – und da gehört die bitweise Gutheißung eines Status natürlich dazu. In Anlehnung an das alte Twitter (aus der Zeit, als zehntausende Menschen Träwelling nutzten) sind das bei uns die Favs. **Bring back the stars!**

### Alles auch auf Englisch.

Wir möchten Träwelling für möglichst viele Nutzer nutzbar machen. Darum haben wir eine Translations-Engine verwendet und bieten die komplette Webseite auch auf Englisch an. Unsere Zugdaten umspannen ganz Europa, warum nicht auch unsere Nutzer? Wenn Du eine andere europäische Sprache sprichst und bereit wärst, Träwelling über mehrere Releases hinweg in diese Sprache zu übersetzen, schreib uns gerne!

### Bye Bye Gertrud!

Gertrud (oder auch "GErtrUd" – die Genaue Error Aufklärung) haben wir abgeschafft, weil normale Fehlermeldungen deutlich einfacher zu gestalten waren. Komplett weg ist sie aber nicht. Vielleicht findest Du sie auch an der ein oder anderen Stelle.

### Wohin geht\'s weiter?

Vor dem Release wurde die neue Version von Träwelling von mehreren Beta-Testern durchgetestet. [@claudiobickel98](https://twitter.com/claudiobickel98), [@davossyfrau](https://twitter.com/davossyfrau), [@dergeschworene](https://twitter.com/dergeschworene), [@JuliusVieth](https://twitter.com/JuliusVieth), [@iJol\_](https://twitter.com/iJol_), [@\_fipsi\_](https://twitter.com/_fipsi_), und [@\_MaxDev\_](https://twitter.com/_MaxDev_) – danke für euer Feedback und Anregungen.

Auch Du kannst Dein Feedback äußern, schreib uns dafür einfach einen Tweet an [@traewelling](https://twitter.com/traewelling), einen Toot an [@traewelling@chaos.social](https://chaos.social/@traewelling) oder eine Mail an [hi@traewelling.de](mailto:hi@traewelling.de). Dort darfst Du auch neue Features anfordern – was auch immer Du Dir schon immer von Träwelling gewünscht hast.

Viel Spaß beim "träwelln", das bestehende Entwicklungsteam,  
Levin[<i class="fab fa-twitter"></i>](https://twitter.com/HerrLevin_)[<i class="fab fa-mastodon"></i>](https://uelfte.club/@HerrLevin_)  
Jannik[<i class="fab fa-twitter"></i>](https://twitter.com/confuzd_)[<i class="fab fa-mastodon"></i>](https://uelfte.club/@jannik)  
Karl[<i class="fab fa-mastodon"></i>](https://uelfte.club/@der_karl)

P.S.: Wir sind derzeit etwas am "ausmisten": Wir haben die Absicht, alle alten Accounts, die mit nicht mehr existenten Twitter-Accounts verbunden sind, zu deaktivieren. Bitte meldet euch, wenn ihr einen alten Account habt, den ihr gerne mit einem aktuellen Twitter-Account verbinden möchtet.'
                         ]);
    }
}
