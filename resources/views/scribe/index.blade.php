<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Träwelling Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=PT+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("vendor/scribe/css/style.css") }}" media="screen"/>
    <link rel="stylesheet" href="{{ asset("vendor/scribe/css/print.css") }}" media="print"/>
    <script src="{{ asset("vendor/scribe/js/all.js") }}"></script>

    <link rel="stylesheet" href="{{ asset("vendor/scribe/css/highlight-darcula.css") }}" media=""/>
    <script src="{{ asset("vendor/scribe/js/highlight.pack.js") }}"></script>
    <script>hljs.initHighlightingOnLoad();</script>

</head>

<body class="" data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">
<a href="#" id="nav-button">
      <span>
        NAV
            <img src="{{ asset("vendor/scribe/images/navbar.png") }}" alt="-image" class=""/>
      </span>
</a>
<div class="tocify-wrapper">
    <div class="lang-selector">
        <a href="#" data-language-name="bash">bash</a>
        <a href="#" data-language-name="javascript">javascript</a>
    </div>
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>
    <ul class="search-results"></ul>

    <ul id="toc">
    </ul>

    <ul class="toc-footer" id="toc-footer">
        <li><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
        <li><a href="{{ route("scribe.openapi") }}">View OpenAPI (Swagger) spec</a></li>
        <li><a href='http://github.com/knuckleswtf/scribe'>Documentation powered by Scribe ✍</a></li>
    </ul>
    <ul class="toc-footer" id="last-updated">
        <li>Last updated: February 15 2021</li>
    </ul>
</div>
<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1>Introduction</h1>
        <p>This documentation aims to provide all the information you need to work with our API.</p>
        <aside>As you scroll, you'll see code examples for working with the API in different programming languages in
            the dark area to the right (or as part of the content on mobile).
            You can switch the language used with the tabs at the top right (or from the nav menu at the top left on
            mobile).
        </aside>
        <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>
        <script>
            var baseUrl = "http://localhost";
        </script>
        <script src="{{ asset("vendor/scribe/js/tryitout-2.4.2.js") }}"></script>
        <blockquote>
            <p>Base URL</p>
        </blockquote>
        <pre><code class="language-yaml">http://localhost</code></pre>
        <h1>Authenticating requests</h1>
        <p>This API is authenticated by sending an <strong><code>Authorization</code></strong> header with the value
            <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
        <p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation
            below.</p>
        <p>You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.</p>
        <h1>Notifications</h1>
        <h2>List Notifications</h2>
        <p>Display a listing of the resource.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/notifications" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/notifications"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "id": "87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0",
        "type": "App\\Notifications\\MastodonNotSent",
        "notifiable_type": "App\\User",
        "notifiable_id": "1",
        "data": {
            "error": "string",
            "status_id": 10
        },
        "read_at": "2020-02-29 13:37:00",
        "created_at": "2020-02-29 13:37:00",
        "updated_at": "2020-02-29 13:37:00",
        "detail": {
            "status": {
                "id": 10,
                "created_at": "2019-11-24 15:44:16",
                "updated_at": "2019-11-24 15:44:16",
                "body": "This is my first checkin!",
                "type": "hafas",
                "event_id": 1,
                "likes_count": 15,
                "favorited": true,
                "user": {
                    "id": 1,
                    "name": "J. Doe",
                    "username": "jdoe",
                    "train_distance": "454.59",
                    "train_duration": "317",
                    "points": "66",
                    "averageSpeed": 100.5678954
                },
                "train_checkin": {
                    "id": 0,
                    "status_id": 10,
                    "trip_id": "1|1937395|17|80|24112019",
                    "origin": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "destination": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "distance": 3.606,
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "points": 3,
                    "delay": 0,
                    "hafas_trip": {
                        "id": 16,
                        "trip_id": "1|1937395|17|80|24112019",
                        "category": "bus",
                        "number": "bus-62",
                        "linename": "62",
                        "origin": "8079041",
                        "destination": "8079041",
                        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                        "polyline": "cac715508e44ae253f424562fe5d286e",
                        "departure": "2019-11-24 15:44:16",
                        "arrival": "2019-11-24 15:44:16",
                        "delay": 0
                    }
                },
                "event": {
                    "id": 1,
                    "name": "Weihnachten 2019",
                    "slug": "weihnachten_2019",
                    "hashtag": "MerryTräwellingMas",
                    "host": "Welt",
                    "url": "https:\/\/www.weihnachten.de\/",
                    "trainstation": "8079041",
                    "begin": "2019-12-24 00:00:00",
                    "end": "2019-12-24 23:59:59"
                }
            }
        }
    }
]</code></pre>
        <div id="execution-results-GETapi-v0-notifications" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-notifications"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-notifications"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-notifications" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-notifications"></code></pre>
        </div>
        <form id="form-GETapi-v0-notifications" data-method="GET" data-path="api/v0/notifications" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-notifications', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-notifications" onclick="tryItOut('GETapi-v0-notifications');">Try it
                    out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-notifications" onclick="cancelTryOut('GETapi-v0-notifications');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-notifications" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/notifications</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-notifications" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="GETapi-v0-notifications" data-component="header"></label>
            </p>
        </form>
        <h2>Read/Unread notification</h2>
        <p>sets the current notification to &quot;read&quot;</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/notifications/87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/notifications/87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (201, new state = read):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": "87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0",
    "type": "App\\Notifications\\MastodonNotSent",
    "notifiable_type": "App\\User",
    "notifiable_id": "1",
    "data": {
        "error": "string",
        "status_id": 10
    },
    "read_at": "2020-02-29 13:37:00",
    "created_at": "2020-02-29 13:37:00",
    "updated_at": "2020-02-29 13:37:00",
    "detail": {
        "status": {
            "id": 10,
            "created_at": "2019-11-24 15:44:16",
            "updated_at": "2019-11-24 15:44:16",
            "body": "This is my first checkin!",
            "type": "hafas",
            "event_id": 1,
            "likes_count": 15,
            "favorited": true,
            "user": {
                "id": 1,
                "name": "J. Doe",
                "username": "jdoe",
                "train_distance": "454.59",
                "train_duration": "317",
                "points": "66",
                "averageSpeed": 100.5678954
            },
            "train_checkin": {
                "id": 0,
                "status_id": 10,
                "trip_id": "1|1937395|17|80|24112019",
                "origin": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "destination": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "distance": 3.606,
                "departure": "2019-11-24 15:44:16",
                "arrival": "2019-11-24 15:44:16",
                "points": 3,
                "delay": 0,
                "hafas_trip": {
                    "id": 16,
                    "trip_id": "1|1937395|17|80|24112019",
                    "category": "bus",
                    "number": "bus-62",
                    "linename": "62",
                    "origin": "8079041",
                    "destination": "8079041",
                    "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                    "polyline": "cac715508e44ae253f424562fe5d286e",
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "delay": 0
                }
            },
            "event": {
                "id": 1,
                "name": "Weihnachten 2019",
                "slug": "weihnachten_2019",
                "hashtag": "MerryTräwellingMas",
                "host": "Welt",
                "url": "https:\/\/www.weihnachten.de\/",
                "trainstation": "8079041",
                "begin": "2019-12-24 00:00:00",
                "end": "2019-12-24 23:59:59"
            }
        }
    }
}</code></pre>
        <blockquote>
            <p>Example response (202, new state = unread):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": "87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0",
    "type": "App\\Notifications\\MastodonNotSent",
    "notifiable_type": "App\\User",
    "notifiable_id": "1",
    "data": {
        "error": "string",
        "status_id": 10
    },
    "read_at": "2020-02-29 13:37:00",
    "created_at": "2020-02-29 13:37:00",
    "updated_at": "2020-02-29 13:37:00",
    "detail": {
        "status": {
            "id": 10,
            "created_at": "2019-11-24 15:44:16",
            "updated_at": "2019-11-24 15:44:16",
            "body": "This is my first checkin!",
            "type": "hafas",
            "event_id": 1,
            "likes_count": 15,
            "favorited": true,
            "user": {
                "id": 1,
                "name": "J. Doe",
                "username": "jdoe",
                "train_distance": "454.59",
                "train_duration": "317",
                "points": "66",
                "averageSpeed": 100.5678954
            },
            "train_checkin": {
                "id": 0,
                "status_id": 10,
                "trip_id": "1|1937395|17|80|24112019",
                "origin": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "destination": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "distance": 3.606,
                "departure": "2019-11-24 15:44:16",
                "arrival": "2019-11-24 15:44:16",
                "points": 3,
                "delay": 0,
                "hafas_trip": {
                    "id": 16,
                    "trip_id": "1|1937395|17|80|24112019",
                    "category": "bus",
                    "number": "bus-62",
                    "linename": "62",
                    "origin": "8079041",
                    "destination": "8079041",
                    "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                    "polyline": "cac715508e44ae253f424562fe5d286e",
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "delay": 0
                }
            },
            "event": {
                "id": 1,
                "name": "Weihnachten 2019",
                "slug": "weihnachten_2019",
                "hashtag": "MerryTräwellingMas",
                "host": "Welt",
                "url": "https:\/\/www.weihnachten.de\/",
                "trainstation": "8079041",
                "begin": "2019-12-24 00:00:00",
                "end": "2019-12-24 23:59:59"
            }
        }
    }
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-PUTapi-v0-notifications--notification-" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-PUTapi-v0-notifications--notification-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-notifications--notification-"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-notifications--notification-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-notifications--notification-"></code></pre>
        </div>
        <form id="form-PUTapi-v0-notifications--notification-" data-method="PUT"
              data-path="api/v0/notifications/{notification}" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-notifications--notification-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-notifications--notification-"
                        onclick="tryItOut('PUTapi-v0-notifications--notification-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-notifications--notification-"
                        onclick="cancelTryOut('PUTapi-v0-notifications--notification-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-notifications--notification-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/notifications/{notification}</code></b>
            </p>
            <p>
                <small class="badge badge-purple">PATCH</small>
                <b><code>api/v0/notifications/{notification}</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-notifications--notification-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="PUTapi-v0-notifications--notification-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>notification</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="notification" data-endpoint="PUTapi-v0-notifications--notification-"
                       data-component="url" required hidden>
                <br>
                The ID of the to-be-changed notification.</p>
        </form>
        <h2>Delete the notification</h2>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X DELETE \
    "http://localhost/api/v0/notifications/87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/notifications/87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (Ok. Notification has been deleted):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": "87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0",
    "type": "App\\Notifications\\MastodonNotSent",
    "notifiable_type": "App\\User",
    "notifiable_id": "1",
    "data": {
        "error": "string",
        "status_id": 10
    },
    "read_at": "2020-02-29 13:37:00",
    "created_at": "2020-02-29 13:37:00",
    "updated_at": "2020-02-29 13:37:00",
    "detail": {
        "status": {
            "id": 10,
            "created_at": "2019-11-24 15:44:16",
            "updated_at": "2019-11-24 15:44:16",
            "body": "This is my first checkin!",
            "type": "hafas",
            "event_id": 1,
            "likes_count": 15,
            "favorited": true,
            "user": {
                "id": 1,
                "name": "J. Doe",
                "username": "jdoe",
                "train_distance": "454.59",
                "train_duration": "317",
                "points": "66",
                "averageSpeed": 100.5678954
            },
            "train_checkin": {
                "id": 0,
                "status_id": 10,
                "trip_id": "1|1937395|17|80|24112019",
                "origin": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "destination": {
                    "id": 3,
                    "ibnr": "8079041",
                    "name": "Karlsruhe Bahnhofsvorplatz",
                    "latitude": 48.994348,
                    "longitude": 48.994348
                },
                "distance": 3.606,
                "departure": "2019-11-24 15:44:16",
                "arrival": "2019-11-24 15:44:16",
                "points": 3,
                "delay": 0,
                "hafas_trip": {
                    "id": 16,
                    "trip_id": "1|1937395|17|80|24112019",
                    "category": "bus",
                    "number": "bus-62",
                    "linename": "62",
                    "origin": "8079041",
                    "destination": "8079041",
                    "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                    "polyline": "cac715508e44ae253f424562fe5d286e",
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "delay": 0
                }
            },
            "event": {
                "id": 1,
                "name": "Weihnachten 2019",
                "slug": "weihnachten_2019",
                "hashtag": "MerryTräwellingMas",
                "host": "Welt",
                "url": "https:\/\/www.weihnachten.de\/",
                "trainstation": "8079041",
                "begin": "2019-12-24 00:00:00",
                "end": "2019-12-24 23:59:59"
            }
        }
    }
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-DELETEapi-v0-notifications--notification-" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-DELETEapi-v0-notifications--notification-"></span>:
            </blockquote>
            <pre class="json"><code
                        id="execution-response-content-DELETEapi-v0-notifications--notification-"></code></pre>
        </div>
        <div id="execution-error-DELETEapi-v0-notifications--notification-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-DELETEapi-v0-notifications--notification-"></code></pre>
        </div>
        <form id="form-DELETEapi-v0-notifications--notification-" data-method="DELETE"
              data-path="api/v0/notifications/{notification}" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v0-notifications--notification-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-DELETEapi-v0-notifications--notification-"
                        onclick="tryItOut('DELETEapi-v0-notifications--notification-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-DELETEapi-v0-notifications--notification-"
                        onclick="cancelTryOut('DELETEapi-v0-notifications--notification-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-DELETEapi-v0-notifications--notification-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-red">DELETE</small>
                <b><code>api/v0/notifications/{notification}</code></b>
            </p>
            <p>
                <label id="auth-DELETEapi-v0-notifications--notification-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="DELETEapi-v0-notifications--notification-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>notification</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="notification" data-endpoint="DELETEapi-v0-notifications--notification-"
                       data-component="url" required hidden>
                <br>
                The ID of the to-be-deleted notification.</p>
        </form>
        <h1>Statuses</h1>
        <h2>Show active statuses</h2>
        <p>Returns all statuses of currently active trains</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/statuses/enroute/all" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/enroute/all"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "id": 10,
        "created_at": "2019-11-24 15:44:16",
        "updated_at": "2019-11-24 15:44:16",
        "body": "This is my first checkin!",
        "type": "hafas",
        "event_id": 1,
        "likes_count": 15,
        "favorited": true,
        "user": {
            "id": 1,
            "name": "J. Doe",
            "username": "jdoe",
            "train_distance": "454.59",
            "train_duration": "317",
            "points": "66",
            "averageSpeed": 100.5678954
        },
        "train_checkin": {
            "id": 0,
            "status_id": 10,
            "trip_id": "1|1937395|17|80|24112019",
            "origin": {
                "id": 3,
                "ibnr": "8079041",
                "name": "Karlsruhe Bahnhofsvorplatz",
                "latitude": 48.994348,
                "longitude": 48.994348
            },
            "destination": {
                "id": 3,
                "ibnr": "8079041",
                "name": "Karlsruhe Bahnhofsvorplatz",
                "latitude": 48.994348,
                "longitude": 48.994348
            },
            "distance": 3.606,
            "departure": "2019-11-24 15:44:16",
            "arrival": "2019-11-24 15:44:16",
            "points": 3,
            "delay": 0,
            "hafas_trip": {
                "id": 16,
                "trip_id": "1|1937395|17|80|24112019",
                "category": "bus",
                "number": "bus-62",
                "linename": "62",
                "origin": "8079041",
                "destination": "8079041",
                "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                "polyline": "cac715508e44ae253f424562fe5d286e",
                "departure": "2019-11-24 15:44:16",
                "arrival": "2019-11-24 15:44:16",
                "delay": 0
            }
        },
        "event": {
            "id": 1,
            "name": "Weihnachten 2019",
            "slug": "weihnachten_2019",
            "hashtag": "MerryTräwellingMas",
            "host": "Welt",
            "url": "https:\/\/www.weihnachten.de\/",
            "trainstation": "8079041",
            "begin": "2019-12-24 00:00:00",
            "end": "2019-12-24 23:59:59"
        }
    }
]</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-statuses-enroute-all" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses-enroute-all"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-statuses-enroute-all"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-statuses-enroute-all" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-statuses-enroute-all"></code></pre>
        </div>
        <form id="form-GETapi-v0-statuses-enroute-all" data-method="GET" data-path="api/v0/statuses/enroute/all"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses-enroute-all', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-statuses-enroute-all"
                        onclick="tryItOut('GETapi-v0-statuses-enroute-all');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-statuses-enroute-all"
                        onclick="cancelTryOut('GETapi-v0-statuses-enroute-all');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-statuses-enroute-all" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/statuses/enroute/all</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-statuses-enroute-all" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="GETapi-v0-statuses-enroute-all" data-component="header"></label>
            </p>
        </form>
        <h2>Event-Statuses</h2>
        <p>Displays all statuses concerning a specific event as a paginated object.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/statuses/event/voluptas" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/event/voluptas"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-statuses-event--statusId-" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-GETapi-v0-statuses-event--statusId-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-statuses-event--statusId-"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-statuses-event--statusId-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-statuses-event--statusId-"></code></pre>
        </div>
        <form id="form-GETapi-v0-statuses-event--statusId-" data-method="GET"
              data-path="api/v0/statuses/event/{statusId}" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses-event--statusId-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-statuses-event--statusId-"
                        onclick="tryItOut('GETapi-v0-statuses-event--statusId-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-statuses-event--statusId-"
                        onclick="cancelTryOut('GETapi-v0-statuses-event--statusId-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-statuses-event--statusId-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/statuses/event/{statusId}</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-statuses-event--statusId-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-statuses-event--statusId-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>statusId</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="statusId" data-endpoint="GETapi-v0-statuses-event--statusId-"
                       data-component="url" required hidden>
                <br>
            </p>
            <p>
                <b><code>eventID</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="eventID" data-endpoint="GETapi-v0-statuses-event--statusId-"
                       data-component="url" required hidden>
                <br>
                the slug of the event</p>
        </form>
        <h2>Like a Status</h2>
        <p>Creates a like for a given status</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X POST \
    "http://localhost/api/v0/statuses/3/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/3/like"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200, Like successfully created):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;true&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a
                status of another user.)):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-POSTapi-v0-statuses--statusId--like" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v0-statuses--statusId--like"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v0-statuses--statusId--like"></code></pre>
        </div>
        <div id="execution-error-POSTapi-v0-statuses--statusId--like" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v0-statuses--statusId--like"></code></pre>
        </div>
        <form id="form-POSTapi-v0-statuses--statusId--like" data-method="POST"
              data-path="api/v0/statuses/{statusId}/like" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-statuses--statusId--like', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v0-statuses--statusId--like"
                        onclick="tryItOut('POSTapi-v0-statuses--statusId--like');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v0-statuses--statusId--like"
                        onclick="cancelTryOut('POSTapi-v0-statuses--statusId--like');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v0-statuses--statusId--like" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-black">POST</small>
                <b><code>api/v0/statuses/{statusId}/like</code></b>
            </p>
            <p>
                <label id="auth-POSTapi-v0-statuses--statusId--like" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="POSTapi-v0-statuses--statusId--like"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="statusId" data-endpoint="POSTapi-v0-statuses--statusId--like"
                       data-component="url" required hidden>
                <br>
                id for the to-be-liked status</p>
        </form>
        <h2>Unlike a Status</h2>
        <p>Removes a like for a given status</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X DELETE \
    "http://localhost/api/v0/statuses/5/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/5/like"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200, Like successfully destroyed):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;true&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a
                status of another user.)):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-DELETEapi-v0-statuses--statusId--like" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-DELETEapi-v0-statuses--statusId--like"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-DELETEapi-v0-statuses--statusId--like"></code></pre>
        </div>
        <div id="execution-error-DELETEapi-v0-statuses--statusId--like" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-DELETEapi-v0-statuses--statusId--like"></code></pre>
        </div>
        <form id="form-DELETEapi-v0-statuses--statusId--like" data-method="DELETE"
              data-path="api/v0/statuses/{statusId}/like" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v0-statuses--statusId--like', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-DELETEapi-v0-statuses--statusId--like"
                        onclick="tryItOut('DELETEapi-v0-statuses--statusId--like');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-DELETEapi-v0-statuses--statusId--like"
                        onclick="cancelTryOut('DELETEapi-v0-statuses--statusId--like');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-DELETEapi-v0-statuses--statusId--like" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-red">DELETE</small>
                <b><code>api/v0/statuses/{statusId}/like</code></b>
            </p>
            <p>
                <label id="auth-DELETEapi-v0-statuses--statusId--like" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="DELETEapi-v0-statuses--statusId--like"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="statusId" data-endpoint="DELETEapi-v0-statuses--statusId--like"
                       data-component="url" required hidden>
                <br>
                id for the to-be-unliked status</p>
        </form>
        <h2>Retrieve Likes</h2>
        <p>Retrieves all likes for a status</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/statuses/20/likes?page=18" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/20/likes"
);

let params = {
    "page": "18",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "created_at": "2019-11-24 15:44:16",
            "updated_at": "2019-11-24 15:44:16",
            "user_id": "1",
            "status_id": "1",
            "user": {
                "id": 1,
                "name": "J. Doe",
                "username": "jdoe",
                "train_distance": "454.59",
                "train_duration": "317",
                "points": "66",
                "averageSpeed": 100.5678954
            }
        }
    ],
    "first_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses\/1\/likes?page=1",
    "from": 1,
    "next_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses\/1\/likes?page=2",
    "path": "https:\/\/traewelling.de\/api\/v0\/statuses",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-statuses--statusId--likes" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-GETapi-v0-statuses--statusId--likes"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-statuses--statusId--likes"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-statuses--statusId--likes" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-statuses--statusId--likes"></code></pre>
        </div>
        <form id="form-GETapi-v0-statuses--statusId--likes" data-method="GET"
              data-path="api/v0/statuses/{statusId}/likes" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses--statusId--likes', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-statuses--statusId--likes"
                        onclick="tryItOut('GETapi-v0-statuses--statusId--likes');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-statuses--statusId--likes"
                        onclick="cancelTryOut('GETapi-v0-statuses--statusId--likes');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-statuses--statusId--likes" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/statuses/{statusId}/likes</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-statuses--statusId--likes" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-statuses--statusId--likes"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="statusId" data-endpoint="GETapi-v0-statuses--statusId--likes"
                       data-component="url" required hidden>
                <br>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>page</code></b>&nbsp;&nbsp;<small>integer</small> <i>optional</i> &nbsp;
                <input type="number" name="page" data-endpoint="GETapi-v0-statuses--statusId--likes"
                       data-component="query" hidden>
                <br>
                Needed to display the specified page</p>
        </form>
        <h2>Dashboard &amp; User-statuses</h2>
        <p>Retrieves either the (global) dashboard for the logged in user or all statuses of a specified user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/statuses?view=user&amp;username=gertrud123&amp;page=4" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses"
);

let params = {
    "view": "user",
    "username": "gertrud123",
    "page": "4",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "created_at": "2019-11-24 15:44:16",
                "updated_at": "2019-11-24 15:44:16",
                "body": "This is my first checkin!",
                "type": "hafas",
                "event_id": 1,
                "likes_count": 15,
                "favorited": true,
                "user": {
                    "id": 1,
                    "name": "J. Doe",
                    "username": "jdoe",
                    "train_distance": "454.59",
                    "train_duration": "317",
                    "points": "66",
                    "averageSpeed": 100.5678954
                },
                "train_checkin": {
                    "id": 0,
                    "status_id": 10,
                    "trip_id": "1|1937395|17|80|24112019",
                    "origin": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "destination": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "distance": 3.606,
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "points": 3,
                    "delay": 0,
                    "hafas_trip": {
                        "id": 16,
                        "trip_id": "1|1937395|17|80|24112019",
                        "category": "bus",
                        "number": "bus-62",
                        "linename": "62",
                        "origin": "8079041",
                        "destination": "8079041",
                        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                        "polyline": "cac715508e44ae253f424562fe5d286e",
                        "departure": "2019-11-24 15:44:16",
                        "arrival": "2019-11-24 15:44:16",
                        "delay": 0
                    }
                },
                "event": {
                    "id": 1,
                    "name": "Weihnachten 2019",
                    "slug": "weihnachten_2019",
                    "hashtag": "MerryTräwellingMas",
                    "host": "Welt",
                    "url": "https:\/\/www.weihnachten.de\/",
                    "trainstation": "8079041",
                    "begin": "2019-12-24 00:00:00",
                    "end": "2019-12-24 23:59:59"
                }
            }
        ],
        "first_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses?page=1",
        "from": 1,
        "next_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses?page=2",
        "path": "https:\/\/traewelling.de\/api\/v0\/statuses",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15
    }
]</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-statuses" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses"></span>:</blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-statuses"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-statuses" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-statuses"></code></pre>
        </div>
        <form id="form-GETapi-v0-statuses" data-method="GET" data-path="api/v0/statuses" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-statuses" onclick="tryItOut('GETapi-v0-statuses');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-statuses" onclick="cancelTryOut('GETapi-v0-statuses');" hidden>
                    Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-statuses" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/statuses</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-statuses" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses"
                            data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>view</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="view" data-endpoint="GETapi-v0-statuses" data-component="query" hidden>
                <br>
                (i.e. the user’s dashboard). Can be user,global or personal.</p>
            <p>
                <b><code>username</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="username" data-endpoint="GETapi-v0-statuses" data-component="query" hidden>
                <br>
                Only required if view is set to user.</p>
            <p>
                <b><code>page</code></b>&nbsp;&nbsp;<small>integer</small> <i>optional</i> &nbsp;
                <input type="number" name="page" data-endpoint="GETapi-v0-statuses" data-component="query" hidden>
                <br>
                Needed to display the specified page</p>
        </form>
        <h2>Retrieve Status</h2>
        <p>Retrieves a single status.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/statuses/veniam" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/veniam"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": 10,
    "created_at": "2019-11-24 15:44:16",
    "updated_at": "2019-11-24 15:44:16",
    "body": "This is my first checkin!",
    "type": "hafas",
    "event_id": 1,
    "likes_count": 15,
    "favorited": true,
    "user": {
        "id": 1,
        "name": "J. Doe",
        "username": "jdoe",
        "train_distance": "454.59",
        "train_duration": "317",
        "points": "66",
        "averageSpeed": 100.5678954
    },
    "train_checkin": {
        "id": 0,
        "status_id": 10,
        "trip_id": "1|1937395|17|80|24112019",
        "origin": {
            "id": 3,
            "ibnr": "8079041",
            "name": "Karlsruhe Bahnhofsvorplatz",
            "latitude": 48.994348,
            "longitude": 48.994348
        },
        "destination": {
            "id": 3,
            "ibnr": "8079041",
            "name": "Karlsruhe Bahnhofsvorplatz",
            "latitude": 48.994348,
            "longitude": 48.994348
        },
        "distance": 3.606,
        "departure": "2019-11-24 15:44:16",
        "arrival": "2019-11-24 15:44:16",
        "points": 3,
        "delay": 0,
        "hafas_trip": {
            "id": 16,
            "trip_id": "1|1937395|17|80|24112019",
            "category": "bus",
            "number": "bus-62",
            "linename": "62",
            "origin": "8079041",
            "destination": "8079041",
            "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
            "polyline": "cac715508e44ae253f424562fe5d286e",
            "departure": "2019-11-24 15:44:16",
            "arrival": "2019-11-24 15:44:16",
            "delay": 0
        }
    },
    "event": {
        "id": 1,
        "name": "Weihnachten 2019",
        "slug": "weihnachten_2019",
        "hashtag": "MerryTräwellingMas",
        "host": "Welt",
        "url": "https:\/\/www.weihnachten.de\/",
        "trainstation": "8079041",
        "begin": "2019-12-24 00:00:00",
        "end": "2019-12-24 23:59:59"
    }
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-statuses--status-" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses--status-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-statuses--status-"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-statuses--status-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-statuses--status-"></code></pre>
        </div>
        <form id="form-GETapi-v0-statuses--status-" data-method="GET" data-path="api/v0/statuses/{status}"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses--status-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-statuses--status-" onclick="tryItOut('GETapi-v0-statuses--status-');">
                    Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-statuses--status-"
                        onclick="cancelTryOut('GETapi-v0-statuses--status-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-statuses--status-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/statuses/{status}</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-statuses--status-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-statuses--status-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>status</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="status" data-endpoint="GETapi-v0-statuses--status-" data-component="url"
                       required hidden>
                <br>
            </p>
            <p>
                <b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="statusId" data-endpoint="GETapi-v0-statuses--status-" data-component="url"
                       required hidden>
                <br>
                The id of a status.</p>
        </form>
        <h2>Update status</h2>
        <p>Updates the status text that a user previously posted</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/statuses/1" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"{}":"This is an updated status body! \ud83e\udd73\nToDo: This accepts plaintext as body, not a key=&gt;value pair."}'
</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/1"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "{}": "This is an updated status body! \ud83e\udd73\nToDo: This accepts plaintext as body, not a key=&gt;value pair."
}

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200, The status object has been modified on the server (i.e. the status text was
                changed). The response contains the modified version of the status.):</p>
        </blockquote>
        <pre><code class="language-json">
{"This is an updated status body! 🥳"}</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a
                status of another user.)):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-PUTapi-v0-statuses--status-" hidden>
            <blockquote>Received response<span id="execution-response-status-PUTapi-v0-statuses--status-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-statuses--status-"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-statuses--status-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-statuses--status-"></code></pre>
        </div>
        <form id="form-PUTapi-v0-statuses--status-" data-method="PUT" data-path="api/v0/statuses/{status}"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-statuses--status-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-statuses--status-" onclick="tryItOut('PUTapi-v0-statuses--status-');">
                    Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-statuses--status-"
                        onclick="cancelTryOut('PUTapi-v0-statuses--status-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-statuses--status-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/statuses/{status}</code></b>
            </p>
            <p>
                <small class="badge badge-purple">PATCH</small>
                <b><code>api/v0/statuses/{status}</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-statuses--status-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="PUTapi-v0-statuses--status-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>status</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="status" data-endpoint="PUTapi-v0-statuses--status-" data-component="url"
                       required hidden>
                <br>
                ID of the status</p>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <p>
                <b><code>{}</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="{}" data-endpoint="PUTapi-v0-statuses--status-" data-component="body" hidden>
                <br>
                New body of the status.</p>

        </form>
        <h2>Destroy status</h2>
        <p>Removes a status that a user has posted previously.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X DELETE \
    "http://localhost/api/v0/statuses/6" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/statuses/6"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (204, No content. The status with the given ID has been deleted. Nothing further needs
                to be said, so the response will not have any content.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Empty response&gt;</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-DELETEapi-v0-statuses--status-" hidden>
            <blockquote>Received response<span id="execution-response-status-DELETEapi-v0-statuses--status-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-DELETEapi-v0-statuses--status-"></code></pre>
        </div>
        <div id="execution-error-DELETEapi-v0-statuses--status-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-DELETEapi-v0-statuses--status-"></code></pre>
        </div>
        <form id="form-DELETEapi-v0-statuses--status-" data-method="DELETE" data-path="api/v0/statuses/{status}"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v0-statuses--status-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-DELETEapi-v0-statuses--status-"
                        onclick="tryItOut('DELETEapi-v0-statuses--status-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-DELETEapi-v0-statuses--status-"
                        onclick="cancelTryOut('DELETEapi-v0-statuses--status-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-DELETEapi-v0-statuses--status-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-red">DELETE</small>
                <b><code>api/v0/statuses/{status}</code></b>
            </p>
            <p>
                <label id="auth-DELETEapi-v0-statuses--status-" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="DELETEapi-v0-statuses--status-" data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>status</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="status" data-endpoint="DELETEapi-v0-statuses--status-" data-component="url"
                       required hidden>
                <br>
                ID of the status</p>
        </form>
        <h1>Trains</h1>
        <p>This category handles the search of trainstations, train departures, line runs and the creation of train
            check ins.</p>
        <h2>Autocomplete</h2>
        <p>This endpoint can be called multiple times in succession when searching stations by name to provide
            suggestions
            for the user to select from. Please provide at least 3 characters when retrieving suggestions. Otherwise,
            only call this endpoint with less than 3 characters if the user explicitly requested a search.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/autocomplete/Kar" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/autocomplete/Kar"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "provider": "train"
    }
]</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-autocomplete--station-" hidden>
            <blockquote>Received response<span
                        id="execution-response-status-GETapi-v0-trains-autocomplete--station-"></span>:
            </blockquote>
            <pre class="json"><code
                        id="execution-response-content-GETapi-v0-trains-autocomplete--station-"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-autocomplete--station-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-autocomplete--station-"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-autocomplete--station-" data-method="GET"
              data-path="api/v0/trains/autocomplete/{station}" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-autocomplete--station-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-autocomplete--station-"
                        onclick="tryItOut('GETapi-v0-trains-autocomplete--station-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-autocomplete--station-"
                        onclick="cancelTryOut('GETapi-v0-trains-autocomplete--station-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-autocomplete--station-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/autocomplete/{station}</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-autocomplete--station-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-trains-autocomplete--station-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>station</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="station" data-endpoint="GETapi-v0-trains-autocomplete--station-"
                       data-component="url" required hidden>
                <br>
                String to be searched for in the stations</p>
        </form>
        <h2>Stations nearby</h2>
        <p>Searches for nearby train stations</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/nearby?latitude=48.994348&amp;longitude=48.994348" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/nearby"
);

let params = {
    "latitude": "48.994348",
    "longitude": "48.994348",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "type": "station",
    "id": "8000191",
    "name": "Karlsruhe Hbf",
    "location": {
        "type": "location",
        "id": "8079041",
        "latitude": 48.994348,
        "longitude": 8.399583
    },
    "products": {
        "nationalExpress": true,
        "national": true,
        "regionalExp": true,
        "regional": true,
        "suburban": true,
        "bus": true,
        "ferry": true,
        "subway": true,
        "tram": true,
        "taxi": true
    }
}</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-nearby" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-nearby"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-trains-nearby"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-nearby" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-nearby"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-nearby" data-method="GET" data-path="api/v0/trains/nearby" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-nearby', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-nearby" onclick="tryItOut('GETapi-v0-trains-nearby');">Try it
                    out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-nearby" onclick="cancelTryOut('GETapi-v0-trains-nearby');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-nearby" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/nearby</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-nearby" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="GETapi-v0-trains-nearby" data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>latitude</code></b>&nbsp;&nbsp;<small>number</small> &nbsp;
                <input type="number" name="latitude" data-endpoint="GETapi-v0-trains-nearby" data-component="query"
                       required hidden>
                <br>
                min:-180, max:180</p>
            <p>
                <b><code>longitude</code></b>&nbsp;&nbsp;<small>number</small> &nbsp;
                <input type="number" name="longitude" data-endpoint="GETapi-v0-trains-nearby" data-component="query"
                       required hidden>
                <br>
                min:-180, max:180</p>
        </form>
        <h2>Stationboard</h2>
        <p>Returns the trains that will depart from a station in the near future or at a specific point in time.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <p>Responses can be filtered for types of public transport e.g. busses, regional and national trains.</p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/stationboard?station=Karlsruhe&amp;when=2019-12-01T21%3A03%3A00%2B01%3A00&amp;travelType=express" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/stationboard"
);

let params = {
    "station": "Karlsruhe",
    "when": "2019-12-01T21:03:00+01:00",
    "travelType": "express",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "station": {
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "provider": "train"
    },
    "when": 1575230596,
    "departures": {
        "trip_id": "1|1937395|17|80|24112019",
        "stop": {
            "type": "stop",
            "id": "8079041",
            "name": "Karlsruhe Bahnhofsvorplatz",
            "location": {
                "type": "location",
                "id": "8079041",
                "latitude": 48.994348,
                "longitude": 8.399583
            },
            "products": {
                "nationalExpress": true,
                "national": true,
                "regionalExp": true,
                "regional": true,
                "suburban": true,
                "bus": true,
                "ferry": true,
                "subway": true,
                "tram": true,
                "taxi": true
            },
            "station": {
                "type": "station",
                "id": "8000191",
                "name": "Karlsruhe Hbf",
                "location": {
                    "type": "location",
                    "id": "8079041",
                    "latitude": 48.994348,
                    "longitude": 8.399583
                },
                "products": {
                    "nationalExpress": true,
                    "national": true,
                    "regionalExp": true,
                    "regional": true,
                    "suburban": true,
                    "bus": true,
                    "ferry": true,
                    "subway": true,
                    "tram": true,
                    "taxi": true
                }
            },
            "when": "2019-12-01T21:03:00+01:00",
            "directoin": "3 Tivoli",
            "line": {
                "type": "line",
                "id": "re-6",
                "fahrtNr": "12042",
                "name": "RE 6",
                "public": true,
                "operator": {
                    "type": "operator",
                    "id": "db-regio-ag-mitte",
                    "name": "DB Regio AG Mitte"
                },
                "additionalName": "RE 6"
            },
            "remarks": "string",
            "delay": 60,
            "platform": "string"
        }
    }
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-stationboard" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-stationboard"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-trains-stationboard"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-stationboard" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-stationboard"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-stationboard" data-method="GET" data-path="api/v0/trains/stationboard"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-stationboard', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-stationboard"
                        onclick="tryItOut('GETapi-v0-trains-stationboard');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-stationboard"
                        onclick="cancelTryOut('GETapi-v0-trains-stationboard');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-stationboard" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/stationboard</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-stationboard" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-trains-stationboard"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>station</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="station" data-endpoint="GETapi-v0-trains-stationboard" data-component="query"
                       required hidden>
                <br>
                The name of the train station</p>
            <p>
                <b><code>when</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="when" data-endpoint="GETapi-v0-trains-stationboard" data-component="query"
                       hidden>
                <br>
                date nullable Timestamp of the query</p>
            <p>
                <b><code>travelType</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="travelType" data-endpoint="GETapi-v0-trains-stationboard"
                       data-component="query" hidden>
                <br>
                nullable Must be one of the following: 'nationalExpress', 'express', 'regionalExp', 'regional',
                'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi'</p>
        </form>
        <h2>Train trip</h2>
        <p>Returns the stopovers and other details of a specific train.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/trip?tripID=1%7C1937395%7C17%7C80%7C24112019&amp;lineName=62&amp;start=8079041" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/trip"
);

let params = {
    "tripID": "1|1937395|17|80|24112019",
    "lineName": "62",
    "start": "8079041",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "station": {
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "provider": "train"
    },
    "start": "Karlsruhe Hbf",
    "destination": "Menzingen(Baden)",
    "train": {
        "id": 16,
        "trip_id": "1|1937395|17|80|24112019",
        "category": "bus",
        "number": "bus-62",
        "linename": "62",
        "origin": "8079041",
        "destination": "8079041",
        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
        "polyline": "cac715508e44ae253f424562fe5d286e",
        "departure": "2019-11-24 15:44:16",
        "arrival": "2019-11-24 15:44:16",
        "delay": 0
    },
    "stopovers": [
        {
            "stop": {
                "type": "stop",
                "id": "8000191",
                "name": "Karlsruhe Hbf",
                "location": {
                    "type": "location",
                    "id": "8079041",
                    "latitude": 48.994348,
                    "longitude": 8.399583
                },
                "products": {
                    "nationalExpress": true,
                    "national": true,
                    "regionalExp": true,
                    "regional": true,
                    "suburban": true,
                    "bus": true,
                    "ferry": true,
                    "subway": true,
                    "tram": true,
                    "taxi": true
                }
            },
            "arrival": "2019-12-01T21:56:00+01:00",
            "arrivalDelay": "60",
            "arrivalPlattform": "1",
            "departure": "2019-12-01T21:56:00+01:00",
            "departureDelay": "60",
            "departurePlatform": "1"
        }
    ]
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-trip" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-trip"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-trains-trip"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-trip" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-trip"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-trip" data-method="GET" data-path="api/v0/trains/trip" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-trip', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-trip" onclick="tryItOut('GETapi-v0-trains-trip');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-trip" onclick="cancelTryOut('GETapi-v0-trains-trip');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-trip" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/trip</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-trip" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-trip"
                            data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>tripID</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="tripID" data-endpoint="GETapi-v0-trains-trip" data-component="query" required
                       hidden>
                <br>
                The given ID of the trip.</p>
            <p>
                <b><code>lineName</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="lineName" data-endpoint="GETapi-v0-trains-trip" data-component="query" required
                       hidden>
                <br>
                The given name of the line.</p>
            <p>
                <b><code>start</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="start" data-endpoint="GETapi-v0-trains-trip" data-component="query" required
                       hidden>
                <br>
                The IBNR of the starting point of the train.</p>
        </form>
        <h2>Check in</h2>
        <p>Creates a check in for a train</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X POST \
    "http://localhost/api/v0/trains/checkin?tripID=1%7C1937395%7C17%7C80%7C24112019&amp;lineName=62&amp;start=8079041&amp;destination=8079041&amp;body=This+is+my+first+Check-in%21&amp;tweet=1&amp;toot=" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/checkin"
);

let params = {
    "tripID": "1|1937395|17|80|24112019",
    "lineName": "62",
    "start": "8079041",
    "destination": "8079041",
    "body": "This is my first Check-in!",
    "tweet": "1",
    "toot": "",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (200, Successfully checked in):</p>
        </blockquote>
        <pre><code class="language-json">{
    "distance": 16.152,
    "duration": 900,
    "statusId": 34,
    "points": 1,
    "lineName": "S 32",
    "alsoOnThisConnection": [
        {
            "id": 1,
            "name": "J. Doe",
            "username": "jdoe",
            "train_distance": "454.59",
            "train_duration": "317",
            "points": "66",
            "averageSpeed": 100.5678954
        }
    ]
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-POSTapi-v0-trains-checkin" hidden>
            <blockquote>Received response<span id="execution-response-status-POSTapi-v0-trains-checkin"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v0-trains-checkin"></code></pre>
        </div>
        <div id="execution-error-POSTapi-v0-trains-checkin" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v0-trains-checkin"></code></pre>
        </div>
        <form id="form-POSTapi-v0-trains-checkin" data-method="POST" data-path="api/v0/trains/checkin" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-trains-checkin', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v0-trains-checkin" onclick="tryItOut('POSTapi-v0-trains-checkin');">Try
                    it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v0-trains-checkin"
                        onclick="cancelTryOut('POSTapi-v0-trains-checkin');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v0-trains-checkin" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-black">POST</small>
                <b><code>api/v0/trains/checkin</code></b>
            </p>
            <p>
                <label id="auth-POSTapi-v0-trains-checkin" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="POSTapi-v0-trains-checkin" data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>tripID</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="tripID" data-endpoint="POSTapi-v0-trains-checkin" data-component="query"
                       required hidden>
                <br>
                ID of the to-be-ckecked-in trip.</p>
            <p>
                <b><code>lineName</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="lineName" data-endpoint="POSTapi-v0-trains-checkin" data-component="query"
                       required hidden>
                <br>
                ID of the to-be-checked-in trip.</p>
            <p>
                <b><code>start</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="start" data-endpoint="POSTapi-v0-trains-checkin" data-component="query"
                       required hidden>
                <br>
                The IBNR of the starting point of the train.</p>
            <p>
                <b><code>destination</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="destination" data-endpoint="POSTapi-v0-trains-checkin" data-component="query"
                       required hidden>
                <br>
                The IBNR of the destination.</p>
            <p>
                <b><code>body</code></b>&nbsp;&nbsp;<small>string</small> <i>optional</i> &nbsp;
                <input type="text" name="body" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" hidden>
                <br>
                max:280 The body of the status.</p>
            <p>
                <b><code>tweet</code></b>&nbsp;&nbsp;<small>boolean</small> <i>optional</i> &nbsp;
                <label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="tweet" value="1"
                                                                               data-endpoint="POSTapi-v0-trains-checkin"
                                                                               data-component="query"><code>true</code></label>
                <label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="tweet" value="0"
                                                                               data-endpoint="POSTapi-v0-trains-checkin"
                                                                               data-component="query"><code>false</code></label>
                <br>
                Should this post be tweeted?</p>
            <p>
                <b><code>toot</code></b>&nbsp;&nbsp;<small>boolean</small> <i>optional</i> &nbsp;
                <label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="toot" value="1"
                                                                               data-endpoint="POSTapi-v0-trains-checkin"
                                                                               data-component="query"><code>true</code></label>
                <label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="toot" value="0"
                                                                               data-endpoint="POSTapi-v0-trains-checkin"
                                                                               data-component="query"><code>false</code></label>
                <br>
                Should this post be posted to mastodon?</p>
        </form>
        <h2>Latest train stations</h2>
        <p>Retrieves the last 5 station the logged in user arrived at</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/latest" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/latest"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "id": 3,
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "latitude": 48.994348,
        "longitude": 48.994348
    }
]</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-latest" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-latest"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-trains-latest"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-latest" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-latest"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-latest" data-method="GET" data-path="api/v0/trains/latest" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-latest', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-latest" onclick="tryItOut('GETapi-v0-trains-latest');">Try it
                    out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-latest" onclick="cancelTryOut('GETapi-v0-trains-latest');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-latest" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/latest</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-latest" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="GETapi-v0-trains-latest" data-component="header"></label>
            </p>
        </form>
        <h2>Home Station</h2>
        <p>Gets the home station of the logged in user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/trains/home" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/home"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": 3,
    "ibnr": "8079041",
    "name": "Karlsruhe Bahnhofsvorplatz",
    "latitude": 48.994348,
    "longitude": 48.994348
}</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-trains-home" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-home"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-trains-home"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-trains-home" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-trains-home"></code></pre>
        </div>
        <form id="form-GETapi-v0-trains-home" data-method="GET" data-path="api/v0/trains/home" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-home', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-trains-home" onclick="tryItOut('GETapi-v0-trains-home');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-trains-home" onclick="cancelTryOut('GETapi-v0-trains-home');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-trains-home" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/trains/home</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-trains-home" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-home"
                            data-component="header"></label>
            </p>
        </form>
        <h2>Home Station</h2>
        <p>Sets the home station for the logged in user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/trains/home?ibnr=8123" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/trains/home"
);

let params = {
    "ibnr": "8123",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">"Ost.Punkt 812 km"</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-PUTapi-v0-trains-home" hidden>
            <blockquote>Received response<span id="execution-response-status-PUTapi-v0-trains-home"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-trains-home"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-trains-home" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-trains-home"></code></pre>
        </div>
        <form id="form-PUTapi-v0-trains-home" data-method="PUT" data-path="api/v0/trains/home" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-trains-home', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-trains-home" onclick="tryItOut('PUTapi-v0-trains-home');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-trains-home" onclick="cancelTryOut('PUTapi-v0-trains-home');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-trains-home" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/trains/home</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-trains-home" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-trains-home"
                            data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <p>
                <b><code>ibnr</code></b>&nbsp;&nbsp;<small>integer</small> &nbsp;
                <input type="number" name="ibnr" data-endpoint="PUTapi-v0-trains-home" data-component="query" required
                       hidden>
                <br>
            </p>
        </form>
        <h1>User management</h1>
        <h2>Login</h2>
        <p>This endpoint handles a normal user login</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X POST \
    "http://localhost/api/v0/auth/login" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email":"aut","password":"voluptatem"}'
</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/auth/login"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "aut",
    "password": "voluptatem"
}

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
    "expires_at": "2021-10-01T12:00:00+02:00"
}</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <div id="execution-results-POSTapi-v0-auth-login" hidden>
            <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-login"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-login"></code></pre>
        </div>
        <div id="execution-error-POSTapi-v0-auth-login" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v0-auth-login"></code></pre>
        </div>
        <form id="form-POSTapi-v0-auth-login" data-method="POST" data-path="api/v0/auth/login" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-login', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v0-auth-login" onclick="tryItOut('POSTapi-v0-auth-login');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v0-auth-login" onclick="cancelTryOut('POSTapi-v0-auth-login');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v0-auth-login" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-black">POST</small>
                <b><code>api/v0/auth/login</code></b>
            </p>
            <p>
                <label id="auth-POSTapi-v0-auth-login" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="POSTapi-v0-auth-login"
                            data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <p>
                <b><code>email</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="email" data-endpoint="POSTapi-v0-auth-login" data-component="body" required
                       hidden>
                <br>
            </p>
            <p>
                <b><code>password</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="password" data-endpoint="POSTapi-v0-auth-login" data-component="body" required
                       hidden>
                <br>
            </p>

        </form>
        <h2>Sign-Up</h2>
        <p>This endpoint is meant for creating a new user with username &amp; password.</p>
        <p>You should probably start here.</p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X POST \
    "http://localhost/api/v0/auth/signup" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"Gertrud123","name":"Gertrud","email":"gertrud@traewelling.de","password":"thisisnotasecurepassword123","confirm_password":"thisisnotasecurepassword123"}'
</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/auth/signup"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "Gertrud123",
    "name": "Gertrud",
    "email": "gertrud@traewelling.de",
    "password": "thisisnotasecurepassword123",
    "confirm_password": "thisisnotasecurepassword123"
}

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
    "expires_at": "2021-10-01T12:00:00+02:00",
    "message": "Registration successfull.."
}</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <div id="execution-results-POSTapi-v0-auth-signup" hidden>
            <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-signup"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-signup"></code></pre>
        </div>
        <div id="execution-error-POSTapi-v0-auth-signup" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v0-auth-signup"></code></pre>
        </div>
        <form id="form-POSTapi-v0-auth-signup" data-method="POST" data-path="api/v0/auth/signup" data-authed="0"
              data-hasfiles="0" data-headers='{"Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-signup', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v0-auth-signup" onclick="tryItOut('POSTapi-v0-auth-signup');">Try it out
                    ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v0-auth-signup" onclick="cancelTryOut('POSTapi-v0-auth-signup');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v0-auth-signup" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-black">POST</small>
                <b><code>api/v0/auth/signup</code></b>
            </p>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <p>
                <b><code>username</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="username" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required
                       hidden>
                <br>
                The @-name of a user. Must be uniqe, max 15 chars and apply to regex:/^[a-zA-Z0-9_]*$/</p>
            <p>
                <b><code>name</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="name" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required
                       hidden>
                <br>
                The displayname of a user. Max 50 chars.</p>
            <p>
                <b><code>email</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="email" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required
                       hidden>
                <br>
                The mail of the user.</p>
            <p>
                <b><code>password</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="password" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required
                       hidden>
                <br>
            </p>
            <p>
                <b><code>confirm_password</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="confirm_password" data-endpoint="POSTapi-v0-auth-signup" data-component="body"
                       required hidden>
                <br>
                Must be equal to password.</p>

        </form>
        <h2>Accept privacy</h2>
        <p>Accepts the current privacy agreement</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/user/accept_privacy" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/accept_privacy"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "message": "privacy agreement successfully accepted"
}</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">
&lt;&lt;&gt;&gt;</code></pre>
        <div id="execution-results-PUTapi-v0-user-accept_privacy" hidden>
            <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-accept_privacy"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-user-accept_privacy"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-user-accept_privacy" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-user-accept_privacy"></code></pre>
        </div>
        <form id="form-PUTapi-v0-user-accept_privacy" data-method="PUT" data-path="api/v0/user/accept_privacy"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-accept_privacy', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-user-accept_privacy"
                        onclick="tryItOut('PUTapi-v0-user-accept_privacy');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-user-accept_privacy"
                        onclick="cancelTryOut('PUTapi-v0-user-accept_privacy');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-user-accept_privacy" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/user/accept_privacy</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-user-accept_privacy" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="PUTapi-v0-user-accept_privacy"
                                                      data-component="header"></label>
            </p>
        </form>
        <h2>Logout</h2>
        <p>This terminates the session and invalidates the bearer token</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X POST \
    "http://localhost/api/v0/auth/logout" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/auth/logout"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "message": "Successfully logged out."
}</code></pre>
        <div id="execution-results-POSTapi-v0-auth-logout" hidden>
            <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-logout"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-logout"></code></pre>
        </div>
        <div id="execution-error-POSTapi-v0-auth-logout" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v0-auth-logout"></code></pre>
        </div>
        <form id="form-POSTapi-v0-auth-logout" data-method="POST" data-path="api/v0/auth/logout" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-logout', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v0-auth-logout" onclick="tryItOut('POSTapi-v0-auth-logout');">Try it out
                    ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v0-auth-logout" onclick="cancelTryOut('POSTapi-v0-auth-logout');"
                        hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v0-auth-logout" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-black">POST</small>
                <b><code>api/v0/auth/logout</code></b>
            </p>
            <p>
                <label id="auth-POSTapi-v0-auth-logout" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer "
                            data-endpoint="POSTapi-v0-auth-logout" data-component="header"></label>
            </p>
        </form>
        <h2>Get current user</h2>
        <p>Gets the info for the currently logged in user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/getuser" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/getuser"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "id": 1,
    "name": "J. Doe",
    "username": "jdoe",
    "train_distance": "454.59",
    "train_duration": "317",
    "points": "66",
    "averageSpeed": 100.5678954
}</code></pre>
        <div id="execution-results-GETapi-v0-getuser" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-getuser"></span>:</blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-getuser"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-getuser" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-getuser"></code></pre>
        </div>
        <form id="form-GETapi-v0-getuser" data-method="GET" data-path="api/v0/getuser" data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-getuser', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-getuser" onclick="tryItOut('GETapi-v0-getuser');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-getuser" onclick="cancelTryOut('GETapi-v0-getuser');" hidden>
                    Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-getuser" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/getuser</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-getuser" hidden>Authorization header: <b><code>Bearer </code></b><input
                            type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-getuser"
                            data-component="header"></label>
            </p>
        </form>
        <h1>User</h1>
        <p>Stuff for the user I guess</p>
        <h2>Leaderboard</h2>
        <p>Gets the leaderboard for friends, kilometers and users.</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/user/leaderboard" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/leaderboard"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "users": [
        {
            "username": "testuser",
            "train_duration": "90",
            "train_distance": "26711.37",
            "points": "1337"
        }
    ],
    "friends": [
        {
            "username": "testuser",
            "train_duration": "90",
            "train_distance": "26711.37",
            "points": "1337"
        }
    ],
    "kilometers": [
        {
            "username": "testuser",
            "train_duration": "90",
            "train_distance": "26711.37",
            "points": "1337"
        }
    ]
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-user-leaderboard" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-user-leaderboard"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-user-leaderboard"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-user-leaderboard" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-user-leaderboard"></code></pre>
        </div>
        <form id="form-GETapi-v0-user-leaderboard" data-method="GET" data-path="api/v0/user/leaderboard" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user-leaderboard', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-user-leaderboard" onclick="tryItOut('GETapi-v0-user-leaderboard');">Try
                    it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-user-leaderboard"
                        onclick="cancelTryOut('GETapi-v0-user-leaderboard');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-user-leaderboard" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/user/leaderboard</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-user-leaderboard" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-user-leaderboard"
                                                      data-component="header"></label>
            </p>
        </form>
        <h2>Get User</h2>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/user/gertrud123" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/gertrud123"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "username": "testuser",
    "twitterUrl": "https:\/\/twitter.com\/traewelling",
    "mastodonUrl": "https:\/\/chaos.social\/traewelling",
    "statuses": {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "created_at": "2019-11-24 15:44:16",
                "updated_at": "2019-11-24 15:44:16",
                "body": "This is my first checkin!",
                "type": "hafas",
                "event_id": 1,
                "likes_count": 15,
                "favorited": true,
                "user": {
                    "id": 1,
                    "name": "J. Doe",
                    "username": "jdoe",
                    "train_distance": "454.59",
                    "train_duration": "317",
                    "points": "66",
                    "averageSpeed": 100.5678954
                },
                "train_checkin": {
                    "id": 0,
                    "status_id": 10,
                    "trip_id": "1|1937395|17|80|24112019",
                    "origin": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "destination": {
                        "id": 3,
                        "ibnr": "8079041",
                        "name": "Karlsruhe Bahnhofsvorplatz",
                        "latitude": 48.994348,
                        "longitude": 48.994348
                    },
                    "distance": 3.606,
                    "departure": "2019-11-24 15:44:16",
                    "arrival": "2019-11-24 15:44:16",
                    "points": 3,
                    "delay": 0,
                    "hafas_trip": {
                        "id": 16,
                        "trip_id": "1|1937395|17|80|24112019",
                        "category": "bus",
                        "number": "bus-62",
                        "linename": "62",
                        "origin": "8079041",
                        "destination": "8079041",
                        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                        "polyline": "cac715508e44ae253f424562fe5d286e",
                        "departure": "2019-11-24 15:44:16",
                        "arrival": "2019-11-24 15:44:16",
                        "delay": 0
                    }
                },
                "event": {
                    "id": 1,
                    "name": "Weihnachten 2019",
                    "slug": "weihnachten_2019",
                    "hashtag": "MerryTräwellingMas",
                    "host": "Welt",
                    "url": "https:\/\/www.weihnachten.de\/",
                    "trainstation": "8079041",
                    "begin": "2019-12-24 00:00:00",
                    "end": "2019-12-24 23:59:59"
                }
            }
        ],
        "first_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses?page=1",
        "from": 1,
        "next_page_url": "https:\/\/traewelling.de\/api\/v0\/statuses?page=2",
        "path": "https:\/\/traewelling.de\/api\/v0\/statuses",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15
    },
    "user": {
        "id": 1,
        "name": "J. Doe",
        "username": "jdoe",
        "train_distance": "454.59",
        "train_duration": "317",
        "points": "66",
        "averageSpeed": 100.5678954
    }
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-user--username-" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-user--username-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-user--username-"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-user--username-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-user--username-"></code></pre>
        </div>
        <form id="form-GETapi-v0-user--username-" data-method="GET" data-path="api/v0/user/{username}" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user--username-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-user--username-" onclick="tryItOut('GETapi-v0-user--username-');">Try
                    it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-user--username-"
                        onclick="cancelTryOut('GETapi-v0-user--username-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-user--username-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/user/{username}</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-user--username-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-user--username-" data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>username</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="username" data-endpoint="GETapi-v0-user--username-" data-component="url"
                       required hidden>
                <br>
                The username of the requested user.</p>
        </form>
        <h2>Search</h2>
        <p>Searches for users with a query</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/user/search/illo" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/search/illo"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "J. Doe",
            "username": "jdoe",
            "train_distance": "454.59",
            "train_duration": "317",
            "points": "66",
            "averageSpeed": 100.5678954
        }
    ],
    "first_page_url": "https:\/\/traewelling.de\/api\/v0\/user\/search\/jdo?page=1",
    "from": 1,
    "next_page_url": "https:\/\/traewelling.de\/api\/v0\/user\/search\/jdo?page=2",
    "path": "https:\/\/traewelling.de\/api\/v0\/user\/search\/jdo",
    "per_page": 5,
    "prev_page_url": null,
    "to": 5
}</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-user-search--query-" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-user-search--query-"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-user-search--query-"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-user-search--query-" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-user-search--query-"></code></pre>
        </div>
        <form id="form-GETapi-v0-user-search--query-" data-method="GET" data-path="api/v0/user/search/{query}"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user-search--query-', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-user-search--query-"
                        onclick="tryItOut('GETapi-v0-user-search--query-');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-user-search--query-"
                        onclick="cancelTryOut('GETapi-v0-user-search--query-');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-user-search--query-" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/user/search/{query}</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-user-search--query-" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-user-search--query-"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>query</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="query" data-endpoint="GETapi-v0-user-search--query-" data-component="url"
                       required hidden>
                <br>
            </p>
            <p>
                <b><code>searchQuery</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="searchQuery" data-endpoint="GETapi-v0-user-search--query-" data-component="url"
                       required hidden>
                <br>
                The string to be searched for in all registered users</p>
        </form>
        <h2>Get active status</h2>
        <p>Gets the currently active status for a given user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X GET \
    -G "http://localhost/api/v0/user/gertrud123/active" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/gertrud123/active"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (204, No active status):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (404, Not found The parameters in the request were valid, but the server did not find a
                corresponding object.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (200):</p>
        </blockquote>
        <pre><code class="language-json">[
    {
        "id": 10,
        "created_at": "2019-11-24 15:44:16",
        "updated_at": "2019-11-24 15:44:16",
        "body": "This is my first checkin!",
        "type": "hafas",
        "event_id": 1,
        "likes_count": 15,
        "favorited": true,
        "user": {
            "id": 1,
            "name": "J. Doe",
            "username": "jdoe",
            "train_distance": "454.59",
            "train_duration": "317",
            "points": "66",
            "averageSpeed": 100.5678954
        },
        "train_checkin": {
            "id": 0,
            "status_id": 10,
            "trip_id": "1|1937395|17|80|24112019",
            "origin": {
                "id": 3,
                "ibnr": "8079041",
                "name": "Karlsruhe Bahnhofsvorplatz",
                "latitude": 48.994348,
                "longitude": 48.994348
            },
            "destination": {
                "id": 3,
                "ibnr": "8079041",
                "name": "Karlsruhe Bahnhofsvorplatz",
                "latitude": 48.994348,
                "longitude": 48.994348
            },
            "distance": 3.606,
            "departure": "2019-11-24 15:44:16",
            "arrival": "2019-11-24 15:44:16",
            "points": 3,
            "delay": 0,
            "hafas_trip": {
                "id": 16,
                "trip_id": "1|1937395|17|80|24112019",
                "category": "bus",
                "number": "bus-62",
                "linename": "62",
                "origin": "8079041",
                "destination": "8079041",
                "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang &gt;70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
                "polyline": "cac715508e44ae253f424562fe5d286e",
                "departure": "2019-11-24 15:44:16",
                "arrival": "2019-11-24 15:44:16",
                "delay": 0
            }
        },
        "event": {
            "id": 1,
            "name": "Weihnachten 2019",
            "slug": "weihnachten_2019",
            "hashtag": "MerryTräwellingMas",
            "host": "Welt",
            "url": "https:\/\/www.weihnachten.de\/",
            "trainstation": "8079041",
            "begin": "2019-12-24 00:00:00",
            "end": "2019-12-24 23:59:59"
        }
    }
]</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-GETapi-v0-user--username--active" hidden>
            <blockquote>Received response<span id="execution-response-status-GETapi-v0-user--username--active"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v0-user--username--active"></code></pre>
        </div>
        <div id="execution-error-GETapi-v0-user--username--active" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-GETapi-v0-user--username--active"></code></pre>
        </div>
        <form id="form-GETapi-v0-user--username--active" data-method="GET" data-path="api/v0/user/{username}/active"
              data-authed="1" data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user--username--active', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v0-user--username--active"
                        onclick="tryItOut('GETapi-v0-user--username--active');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v0-user--username--active"
                        onclick="cancelTryOut('GETapi-v0-user--username--active');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v0-user--username--active" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-green">GET</small>
                <b><code>api/v0/user/{username}/active</code></b>
            </p>
            <p>
                <label id="auth-GETapi-v0-user--username--active" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="GETapi-v0-user--username--active"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
            <p>
                <b><code>username</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="username" data-endpoint="GETapi-v0-user--username--active" data-component="url"
                       required hidden>
                <br>
                The username of the requested user.</p>
        </form>
        <h2>Update avatar</h2>
        <p>Gets the avatar of a given user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/user/profilepicture" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: multipart/form-data" \
    -H "Accept: application/json" \
    -F "image=@/tmp/php7bom4u" </code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/profilepicture"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "PUT",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200, OK. The avatar was successfully uploaded.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-PUTapi-v0-user-profilepicture" hidden>
            <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-profilepicture"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-user-profilepicture"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-user-profilepicture" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-user-profilepicture"></code></pre>
        </div>
        <form id="form-PUTapi-v0-user-profilepicture" data-method="PUT" data-path="api/v0/user/profilepicture"
              data-authed="1" data-hasfiles="1"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"multipart\/form-data","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-profilepicture', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-user-profilepicture"
                        onclick="tryItOut('PUTapi-v0-user-profilepicture');">Try it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-user-profilepicture"
                        onclick="cancelTryOut('PUTapi-v0-user-profilepicture');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-user-profilepicture" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/user/profilepicture</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-user-profilepicture" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="PUTapi-v0-user-profilepicture"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <p>
                <b><code>image</code></b>&nbsp;&nbsp;<small>file</small> &nbsp;
                <input type="file" name="image" data-endpoint="PUTapi-v0-user-profilepicture" data-component="body"
                       required hidden>
                <br>
                This is actually the body of the request. Scribe won't let me document it like that.</p>

        </form>
        <h2>Update DisplayName</h2>
        <p>Updates the display name of the current user</p>
        <p><small class="badge badge-darkred">requires authentication</small></p>
        <blockquote>
            <p>Example request:</p>
        </blockquote>
        <pre><code class="language-bash">curl -X PUT \
    "http://localhost/api/v0/user/displayname" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"aut"}'
</code></pre>
        <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v0/user/displayname"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "aut"
}

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
        <blockquote>
            <p>Example response (200, OK. The displayName of the current user was changed.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (400, Bad Request The parameters are wrong or not given.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong
                credentials were supplied.):</p>
        </blockquote>
        <pre><code class="language-json">&lt;Binary data&gt; -  empty response</code></pre>
        <blockquote>
            <p>Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):</p>
        </blockquote>
        <pre><code class="language-json">{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}</code></pre>
        <div id="execution-results-PUTapi-v0-user-displayname" hidden>
            <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-displayname"></span>:
            </blockquote>
            <pre class="json"><code id="execution-response-content-PUTapi-v0-user-displayname"></code></pre>
        </div>
        <div id="execution-error-PUTapi-v0-user-displayname" hidden>
            <blockquote>Request failed with error:</blockquote>
            <pre><code id="execution-error-message-PUTapi-v0-user-displayname"></code></pre>
        </div>
        <form id="form-PUTapi-v0-user-displayname" data-method="PUT" data-path="api/v0/user/displayname" data-authed="1"
              data-hasfiles="0"
              data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}'
              onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-displayname', this);">
            <h3>
                Request&nbsp;&nbsp;&nbsp;
                <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-PUTapi-v0-user-displayname" onclick="tryItOut('PUTapi-v0-user-displayname');">Try
                    it out ⚡
                </button>
                <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-PUTapi-v0-user-displayname"
                        onclick="cancelTryOut('PUTapi-v0-user-displayname');" hidden>Cancel
                </button>&nbsp;&nbsp;
                <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-PUTapi-v0-user-displayname" hidden>Send Request 💥
                </button>
            </h3>
            <p>
                <small class="badge badge-darkblue">PUT</small>
                <b><code>api/v0/user/displayname</code></b>
            </p>
            <p>
                <label id="auth-PUTapi-v0-user-displayname" hidden>Authorization header:
                    <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer "
                                                      data-endpoint="PUTapi-v0-user-displayname"
                                                      data-component="header"></label>
            </p>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <p>
                <b><code>username</code></b>&nbsp;&nbsp;<small>string</small> &nbsp;
                <input type="text" name="username" data-endpoint="PUTapi-v0-user-displayname" data-component="body"
                       required hidden>
                <br>
                This is actually a string in the body, not a json-request.</p>

        </form>
    </div>
    <div class="dark-box">
        <div class="lang-selector">
            <a href="#" data-language-name="bash">bash</a>
            <a href="#" data-language-name="javascript">javascript</a>
        </div>
    </div>
</div>
<script>
    $(function () {
        var languages = ["bash", "javascript"];
        setupLanguages(languages);
    });
</script>
</body>
</html>