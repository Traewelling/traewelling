# Statuses


## Show active statuses
Returns all statuses of currently active trains

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/enroute/all" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (200):

```json
[
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
                "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang >70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
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
]
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-GETapi-v0-statuses-enroute-all" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses-enroute-all"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-statuses-enroute-all"></code></pre>
</div>
<div id="execution-error-GETapi-v0-statuses-enroute-all" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-statuses-enroute-all"></code></pre>
</div>
<form id="form-GETapi-v0-statuses-enroute-all" data-method="GET" data-path="api/v0/statuses/enroute/all" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses-enroute-all', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-statuses-enroute-all" onclick="tryItOut('GETapi-v0-statuses-enroute-all');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-statuses-enroute-all" onclick="cancelTryOut('GETapi-v0-statuses-enroute-all');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-statuses-enroute-all" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/statuses/enroute/all</code></b>
</p>
<p>
<label id="auth-GETapi-v0-statuses-enroute-all" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses-enroute-all" data-component="header"></label>
</p>
</form>


## Event-Statuses
Displays all statuses concerning a specific event as a paginated object.

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/event/ab" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/event/ab"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response => response.json());
```


> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-GETapi-v0-statuses-event--statusId-" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses-event--statusId-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-statuses-event--statusId-"></code></pre>
</div>
<div id="execution-error-GETapi-v0-statuses-event--statusId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-statuses-event--statusId-"></code></pre>
</div>
<form id="form-GETapi-v0-statuses-event--statusId-" data-method="GET" data-path="api/v0/statuses/event/{statusId}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses-event--statusId-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-statuses-event--statusId-" onclick="tryItOut('GETapi-v0-statuses-event--statusId-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-statuses-event--statusId-" onclick="cancelTryOut('GETapi-v0-statuses-event--statusId-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-statuses-event--statusId-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/statuses/event/{statusId}</code></b>
</p>
<p>
<label id="auth-GETapi-v0-statuses-event--statusId-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses-event--statusId-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>statusId</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="statusId" data-endpoint="GETapi-v0-statuses-event--statusId-" data-component="url" required  hidden>
<br>
</p>
<p>
<b><code>eventID</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="eventID" data-endpoint="GETapi-v0-statuses-event--statusId-" data-component="url" required  hidden>
<br>
the slug of the event</p>
</form>


## Like a Status
Creates a like for a given status

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/statuses/10/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/10/like"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response => response.json());
```


> Example response (200, Like successfully created):

```json

<<true>>
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-POSTapi-v0-statuses--statusId--like" hidden>
    <blockquote>Received response<span id="execution-response-status-POSTapi-v0-statuses--statusId--like"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v0-statuses--statusId--like"></code></pre>
</div>
<div id="execution-error-POSTapi-v0-statuses--statusId--like" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v0-statuses--statusId--like"></code></pre>
</div>
<form id="form-POSTapi-v0-statuses--statusId--like" data-method="POST" data-path="api/v0/statuses/{statusId}/like" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-statuses--statusId--like', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v0-statuses--statusId--like" onclick="tryItOut('POSTapi-v0-statuses--statusId--like');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v0-statuses--statusId--like" onclick="cancelTryOut('POSTapi-v0-statuses--statusId--like');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v0-statuses--statusId--like" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-black">POST</small>
 <b><code>api/v0/statuses/{statusId}/like</code></b>
</p>
<p>
<label id="auth-POSTapi-v0-statuses--statusId--like" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="POSTapi-v0-statuses--statusId--like" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="statusId" data-endpoint="POSTapi-v0-statuses--statusId--like" data-component="url" required  hidden>
<br>
id for the to-be-liked status</p>
</form>


## Unlike a Status
Removes a like for a given status

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/v0/statuses/17/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/17/like"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response => response.json());
```


> Example response (200, Like successfully destroyed):

```json

<<true>>
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-DELETEapi-v0-statuses--statusId--like" hidden>
    <blockquote>Received response<span id="execution-response-status-DELETEapi-v0-statuses--statusId--like"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v0-statuses--statusId--like"></code></pre>
</div>
<div id="execution-error-DELETEapi-v0-statuses--statusId--like" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v0-statuses--statusId--like"></code></pre>
</div>
<form id="form-DELETEapi-v0-statuses--statusId--like" data-method="DELETE" data-path="api/v0/statuses/{statusId}/like" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v0-statuses--statusId--like', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-DELETEapi-v0-statuses--statusId--like" onclick="tryItOut('DELETEapi-v0-statuses--statusId--like');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-DELETEapi-v0-statuses--statusId--like" onclick="cancelTryOut('DELETEapi-v0-statuses--statusId--like');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-DELETEapi-v0-statuses--statusId--like" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-red">DELETE</small>
 <b><code>api/v0/statuses/{statusId}/like</code></b>
</p>
<p>
<label id="auth-DELETEapi-v0-statuses--statusId--like" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="DELETEapi-v0-statuses--statusId--like" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="statusId" data-endpoint="DELETEapi-v0-statuses--statusId--like" data-component="url" required  hidden>
<br>
id for the to-be-unliked status</p>
</form>


## Retrieve Likes
Retrieves all likes for a status

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/7/likes?page=5" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/7/likes"
);

let params = {
    "page": "5",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response => response.json());
```


> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (200):

```json
{
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
}
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-GETapi-v0-statuses--statusId--likes" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses--statusId--likes"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-statuses--statusId--likes"></code></pre>
</div>
<div id="execution-error-GETapi-v0-statuses--statusId--likes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-statuses--statusId--likes"></code></pre>
</div>
<form id="form-GETapi-v0-statuses--statusId--likes" data-method="GET" data-path="api/v0/statuses/{statusId}/likes" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses--statusId--likes', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-statuses--statusId--likes" onclick="tryItOut('GETapi-v0-statuses--statusId--likes');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-statuses--statusId--likes" onclick="cancelTryOut('GETapi-v0-statuses--statusId--likes');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-statuses--statusId--likes" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/statuses/{statusId}/likes</code></b>
</p>
<p>
<label id="auth-GETapi-v0-statuses--statusId--likes" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses--statusId--likes" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="statusId" data-endpoint="GETapi-v0-statuses--statusId--likes" data-component="url" required  hidden>
<br>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>page</code></b>&nbsp;&nbsp;<small>integer</small>     <i>optional</i> &nbsp;
<input type="number" name="page" data-endpoint="GETapi-v0-statuses--statusId--likes" data-component="query"  hidden>
<br>
Needed to display the specified page</p>
</form>


## Dashboard &amp; User-statuses
Retrieves either the (global) dashboard for the logged in user or all statuses of a specified user

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses?view=user&username=gertrud123&page=7" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses"
);

let params = {
    "view": "user",
    "username": "gertrud123",
    "page": "7",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response => response.json());
```


> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (200):

```json
[
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
                        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang >70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
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
]
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-GETapi-v0-statuses" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-statuses"></code></pre>
</div>
<div id="execution-error-GETapi-v0-statuses" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-statuses"></code></pre>
</div>
<form id="form-GETapi-v0-statuses" data-method="GET" data-path="api/v0/statuses" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-statuses" onclick="tryItOut('GETapi-v0-statuses');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-statuses" onclick="cancelTryOut('GETapi-v0-statuses');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-statuses" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/statuses</code></b>
</p>
<p>
<label id="auth-GETapi-v0-statuses" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>view</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="view" data-endpoint="GETapi-v0-statuses" data-component="query"  hidden>
<br>
(i.e. the user’s dashboard). Can be user,global or personal.</p>
<p>
<b><code>username</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="username" data-endpoint="GETapi-v0-statuses" data-component="query"  hidden>
<br>
Only required if view is set to user.</p>
<p>
<b><code>page</code></b>&nbsp;&nbsp;<small>integer</small>     <i>optional</i> &nbsp;
<input type="number" name="page" data-endpoint="GETapi-v0-statuses" data-component="query"  hidden>
<br>
Needed to display the specified page</p>
</form>


## Retrieve Status
Retrieves a single status.

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/perferendis" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/perferendis"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response => response.json());
```


> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (200):

```json
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
            "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang >70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
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
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-GETapi-v0-statuses--status-" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-statuses--status-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-statuses--status-"></code></pre>
</div>
<div id="execution-error-GETapi-v0-statuses--status-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-statuses--status-"></code></pre>
</div>
<form id="form-GETapi-v0-statuses--status-" data-method="GET" data-path="api/v0/statuses/{status}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-statuses--status-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-statuses--status-" onclick="tryItOut('GETapi-v0-statuses--status-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-statuses--status-" onclick="cancelTryOut('GETapi-v0-statuses--status-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-statuses--status-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/statuses/{status}</code></b>
</p>
<p>
<label id="auth-GETapi-v0-statuses--status-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-statuses--status-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>status</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="status" data-endpoint="GETapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
</p>
<p>
<b><code>statusId</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="statusId" data-endpoint="GETapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
The id of a status.</p>
</form>


## Update status
Updates the status text that a user previously posted

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/statuses/1" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"{}":"This is an updated status body! \ud83e\udd73\nToDo: This accepts plaintext as body, not a key=>value pair."}'

```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/1"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "{}": "This is an updated status body! \ud83e\udd73\nToDo: This accepts plaintext as body, not a key=>value pair."
}

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response => response.json());
```


> Example response (200, The status object has been modified on the server (i.e. the status text was changed). The response contains the modified version of the status.):

```json

{"This is an updated status body! 🥳"}
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (403, Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-PUTapi-v0-statuses--status-" hidden>
    <blockquote>Received response<span id="execution-response-status-PUTapi-v0-statuses--status-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v0-statuses--status-"></code></pre>
</div>
<div id="execution-error-PUTapi-v0-statuses--status-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v0-statuses--status-"></code></pre>
</div>
<form id="form-PUTapi-v0-statuses--status-" data-method="PUT" data-path="api/v0/statuses/{status}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-statuses--status-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-PUTapi-v0-statuses--status-" onclick="tryItOut('PUTapi-v0-statuses--status-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-PUTapi-v0-statuses--status-" onclick="cancelTryOut('PUTapi-v0-statuses--status-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-PUTapi-v0-statuses--status-" hidden>Send Request 💥</button>
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
<label id="auth-PUTapi-v0-statuses--status-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-statuses--status-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>status</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="status" data-endpoint="PUTapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
ID of the status</p>
<h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
<p>
<b><code>{}</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="{}" data-endpoint="PUTapi-v0-statuses--status-" data-component="body"  hidden>
<br>
New body of the status.</p>

</form>


## Destroy status
Removes a status that a user has posted previously.

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/v0/statuses/4" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/4"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response => response.json());
```


> Example response (204, No content. The status with the given ID has been deleted. Nothing further needs to be said, so the response will not have any content.):

```json
<Empty response>
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (404, Not found The parameters in the request were valid, but the server did not find a corresponding object.):

```json

<<>>
```
> Example response (406, Not Acceptable The privacy agreement has not yet been accepted.):

```json
{
    "error": "Privacy agreement not yet accepted!",
    "updated": "2019-11-04 20:07:00",
    "german": "string",
    "english": "string"
}
```
<div id="execution-results-DELETEapi-v0-statuses--status-" hidden>
    <blockquote>Received response<span id="execution-response-status-DELETEapi-v0-statuses--status-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-v0-statuses--status-"></code></pre>
</div>
<div id="execution-error-DELETEapi-v0-statuses--status-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-v0-statuses--status-"></code></pre>
</div>
<form id="form-DELETEapi-v0-statuses--status-" data-method="DELETE" data-path="api/v0/statuses/{status}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v0-statuses--status-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-DELETEapi-v0-statuses--status-" onclick="tryItOut('DELETEapi-v0-statuses--status-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-DELETEapi-v0-statuses--status-" onclick="cancelTryOut('DELETEapi-v0-statuses--status-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-DELETEapi-v0-statuses--status-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-red">DELETE</small>
 <b><code>api/v0/statuses/{status}</code></b>
</p>
<p>
<label id="auth-DELETEapi-v0-statuses--status-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="DELETEapi-v0-statuses--status-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>status</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="status" data-endpoint="DELETEapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
ID of the status</p>
</form>



