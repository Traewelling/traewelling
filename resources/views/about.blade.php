@extends('layouts.app')

@section('title')
    FAQ
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8" id="about-page">
                <h1>About us</h1>
                <blockquote class="blockquote"><p>Träwelling ist ein kostenloser Check-in Service, mit dem du deinen Freunden mitteilen kannst, wo du gerade mit der Bahn unterwegs bist. Kurz gesagt: Man kann in Züge einchecken und bekommt dafür Punkte.</p></blockquote>

                <h2>F.A.Q. <small class="text-muted">H&auml;ufig gestellte Fragen</small></h2>

                <h3>Wer entwickelt Träwelling?</h3>
                <p class="lead">@<a href="https://twitter.com/herrlevin_">HerrLevin_</a> hat einen großen Teil der ersten Version (seit 2013), der mobilen Version (seit 2014) und der neuen Version (seit 2019) entwickelt. Unterstützt wird er von @<a href="https://twitter.com/confuzd_">confuzd_</a>, @<a href="https://twitter.com/aledjones">aledjones</a>, @<a href="https://twitter.com/der_Karl">der_Karl</a> und @<a href="https://twitter.com/quantatheist">quantatheist</a>.

                <h3>Woher kommt der Name?</h3>
                <p class="lead">Der Name ist eine Anspielung auf das allseits bekannte <i>"Senk ju for träwelling wis Deutsche Bahn"</i>, was man eigentlich in fast jedem Fernverkehrszug der Deutschen Bahn gehört haben sollte.<br />Auf die Idee kam der nette @<a href="https://twitter.com/mrpelz">mrpelz</a>.</p>

                <h3>Warum wird mein Zug nicht aufgelistet?</h3>
                <p class="lead">Wir verwenden eine Schnittstelle der Deutschen Bahn, bei der nicht alle Angebote direkt dargestellt werden. Leider können wir da auch nicht viel dran tun, wenn dein Zug nicht dabei ist.</p>

                <h3>Da fehlt etwas! Wieso wurde dieses Feature entfernt?</h3>
                <p class="lead">In dieser Version von Träwelling fangen wir einmal ganz von neu an &ndash; und dabei kann es sein, dass wenig-verwendete Funktionen noch nicht implementiert sind. <strong>Übrigens:</strong> Wenn Du ein Feature vorschlagen möchtest, schreib einfach eine E-Mail an <a href="mailto:traewelling@herrlev.in">traewelling@herrlev.in</a>.</p>

                <h3>Wie werden Punkte berechnet?</h3>
                <p class="lead">
                    Die Punkte setzen sich aus der Produktklasse und der Entfernung deiner Reise zusammen.
                </p>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Produktklasse</th>
                            <th scope="col">Basispunkte</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th scope="row">Tram / Stadtbahn, Bus, U-Bahn</th><td>2</td></tr>
                        <tr><th scope="row">S-Bahn, Fähre</th><td>3</td></tr>
                        <tr><th scope="row">Regionalbahn /-express</th><td>5</td></tr>
                        <tr><th scope="row">InterCity, EuroCity</th><td>10</td></tr>
                        <tr><th scope="row">InterCityExpress, TGV, RailJet</th><td>10</td></tr>
                    </tbody>
                </table>

                <p class="lead">Die Entfernung wird auf die nächsten 10km gerundet und dann durch 10 geteilt. ein, anschließend werden die Punkte addiert.<br />
                Eine ICE-Reise von 143km bringt dir also <code>10 + aufrunden(143/10) = 10 + 15 = 25</code>&nbsp;Punkte, für eine S-Bahn-Fahrt von 8km gibt es <code>2 + aufrunden(8/10) = 2 + 1 = 3</code>&nbsp;Punkte.<br /></p>
                
            </div>
        </div>
    </div><!--- /container -->
@endsection
