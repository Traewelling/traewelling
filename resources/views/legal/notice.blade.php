@extends(minimalLayout())

@section('title', __('menu.legal-notice'))
@section('meta-robots', 'noindex')

@section('content')
    @if(isNewLayout())
        <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto py-4">
            <div class="container mx-auto md:px-4 py-2 md:py-24">
                <div class="grid gap-2 mb-4">
                    <a href="/" class="link link-hover">
                        <img src="/images/icons/logo.svg" class="h-12 w-12" alt="Träwelling Logo" style="stroke: #c72730;"/>
                    </a>
                    <a href="/" class="link link-hover">
                        {{ __('menu.gohome') }}
                    </a>
                </div>
                <h1 class="font-bold text-xl mb-1">
                    {{ __('menu.legal-notice') }}
                </h1>

                <h2 class="text-lg font-bold">Angaben gem&auml;&szlig; &sect; 5 DDG</h2>
                <p class="mb-2">{{config('app.legal.name')}}<br/>
                    {{config('app.legal.address1')}}<br/>
                    {{config('app.legal.address2')}}</p>

                <h2 class="text-lg font-bold mt-4">Kontakt</h2>
                @if(config('app.legal.email'))
                    <p class="mb-2">E-Mail: {{config('app.legal.email')}}</p>
                @endif
                @if(config('app.legal.tel'))
                    <p class="mb-2">Tel.: {{config('app.legal.tel')}}</p>
                @endif

                <h3 class="text-md font-bold mt-4">Haftung f&uuml;r Inhalte</h3>
                <p class="mb-2">Als Diensteanbieter sind wir gem&auml;&szlig; &sect; 7 Abs.1 DDG f&uuml;r eigene Inhalte
                    auf
                    diesen
                    Seiten nach den allgemeinen Gesetzen verantwortlich. Nach &sect;&sect; 8 bis 10 DDG sind wir als
                    Diensteanbieter jedoch nicht verpflichtet, &uuml;bermittelte oder gespeicherte fremde Informationen
                    zu &uuml;berwachen oder nach Umst&auml;nden zu forschen, die auf eine rechtswidrige T&auml;tigkeit
                    hinweisen.</p>
                <p class="mb-2">Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den
                    allgemeinen
                    Gesetzen bleiben hiervon unber&uuml;hrt. Eine diesbez&uuml;gliche Haftung ist jedoch erst ab dem
                    Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m&ouml;glich. Bei Bekanntwerden von
                    entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
                <h3 class="text-md font-bold mt-4">Haftung f&uuml;r Links</h3>
                <p class="mb-2">Unser Angebot enth&auml;lt Links zu externen Websites Dritter, auf deren Inhalte wir
                    keinen
                    Einfluss
                    haben. Deshalb k&ouml;nnen wir f&uuml;r diese fremden Inhalte auch keine Gew&auml;hr &uuml;bernehmen.
                    F&uuml;r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der
                    Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m&ouml;gliche
                    Rechtsverst&ouml;&szlig;e &uuml;berpr&uuml;ft. Rechtswidrige Inhalte waren zum Zeitpunkt der
                    Verlinkung nicht erkennbar.</p>
                <p class="mb-2">Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete
                    Anhaltspunkte
                    einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige
                    Links umgehend entfernen.</p>
                <h3 class="text-md font-bold mt-4">Urheberrecht</h3>
                <p class="mb-2">Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen
                    dem
                    deutschen Urheberrecht. Die Vervielf&auml;ltigung, Bearbeitung, Verbreitung und jede Art der
                    Verwertung au&szlig;erhalb der Grenzen des Urheberrechtes bed&uuml;rfen der schriftlichen Zustimmung
                    des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f&uuml;r den
                    privaten, nicht kommerziellen Gebrauch gestattet.</p>
                <p class="mb-2">Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die
                    Urheberrechte
                    Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie
                    trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden
                    Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend
                    entfernen.</p>

                <p class="mb-2">Quelle: <a href="https://www.e-recht24.de">eRecht24</a></p>

                <h3 class="text-md font-bold mt-4">Thanks</h3>
                <p class="mb-2">This page uses Nunito Regular – Copyright 2014 The Nunito Project Authors
                    (contact@sansoxygen.com)</p>

                <p class="mb-2">This page uses data from OpenStreetMap, a free project with the aim of collecting freely
                    utilisable
                    geo-data and making it available in a database for general use (open data). In order to display the
                    map to you, information about use of the website including your IP address is communicated to
                    OpenStreetMap. These services are operated by OpenStreetMap Foundation (OSMF), 132 Maney Hill Road,
                    Sutton Coldfield, West Midlands, B72 1JU, United Kingdom, on behalf of the OSM community.</p>

            </div>
        </main
    @else
        @include('legal.partials.notice-old')
    @endif
@endsection
