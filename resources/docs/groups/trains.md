# Trains
This category handles the search of trainstations, train departures, line runs and the creation of train check ins.

## Autocomplete
This endpoint can be called multiple times in succession when searching stations by name to provide suggestions
for the user to select from. Please provide at least 3 characters when retrieving suggestions. Otherwise,
only call this endpoint with less than 3 characters if the user explicitly requested a search.

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/autocomplete/Kar" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
[
    {
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "provider": "train"
    }
]
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

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
<div id="execution-results-GETapi-v0-trains-autocomplete--station-" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-autocomplete--station-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-autocomplete--station-"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-autocomplete--station-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-autocomplete--station-"></code></pre>
</div>
<form id="form-GETapi-v0-trains-autocomplete--station-" data-method="GET" data-path="api/v0/trains/autocomplete/{station}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-autocomplete--station-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-autocomplete--station-" onclick="tryItOut('GETapi-v0-trains-autocomplete--station-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-autocomplete--station-" onclick="cancelTryOut('GETapi-v0-trains-autocomplete--station-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-autocomplete--station-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/autocomplete/{station}</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-autocomplete--station-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-autocomplete--station-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>station</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="station" data-endpoint="GETapi-v0-trains-autocomplete--station-" data-component="url" required  hidden>
<br>
String to be searched for in the stations</p>
</form>


## Stations nearby
Searches for nearby train stations

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/nearby?latitude=48.994348&longitude=48.994348" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/nearby"
);

let params = {
    "latitude": "48.994348",
    "longitude": "48.994348",
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


> Example response (200):

```json
{
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
}
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
<div id="execution-results-GETapi-v0-trains-nearby" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-nearby"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-nearby"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-nearby" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-nearby"></code></pre>
</div>
<form id="form-GETapi-v0-trains-nearby" data-method="GET" data-path="api/v0/trains/nearby" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-nearby', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-nearby" onclick="tryItOut('GETapi-v0-trains-nearby');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-nearby" onclick="cancelTryOut('GETapi-v0-trains-nearby');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-nearby" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/nearby</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-nearby" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-nearby" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>latitude</code></b>&nbsp;&nbsp;<small>number</small>  &nbsp;
<input type="number" name="latitude" data-endpoint="GETapi-v0-trains-nearby" data-component="query" required  hidden>
<br>
min:-180, max:180</p>
<p>
<b><code>longitude</code></b>&nbsp;&nbsp;<small>number</small>  &nbsp;
<input type="number" name="longitude" data-endpoint="GETapi-v0-trains-nearby" data-component="query" required  hidden>
<br>
min:-180, max:180</p>
</form>


## Stationboard
Returns the trains that will depart from a station in the near future or at a specific point in time.

<small class="badge badge-darkred">requires authentication</small>

Responses can be filtered for types of public transport e.g. busses, regional and national trains.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/stationboard?station=Karlsruhe&when=2019-12-01T21%3A03%3A00%2B01%3A00&travelType=express" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/stationboard"
);

let params = {
    "station": "Karlsruhe",
    "when": "2019-12-01T21:03:00+01:00",
    "travelType": "express",
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
{
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
<div id="execution-results-GETapi-v0-trains-stationboard" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-stationboard"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-stationboard"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-stationboard" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-stationboard"></code></pre>
</div>
<form id="form-GETapi-v0-trains-stationboard" data-method="GET" data-path="api/v0/trains/stationboard" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-stationboard', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-stationboard" onclick="tryItOut('GETapi-v0-trains-stationboard');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-stationboard" onclick="cancelTryOut('GETapi-v0-trains-stationboard');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-stationboard" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/stationboard</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-stationboard" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-stationboard" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>station</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="station" data-endpoint="GETapi-v0-trains-stationboard" data-component="query" required  hidden>
<br>
The name of the train station</p>
<p>
<b><code>when</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="when" data-endpoint="GETapi-v0-trains-stationboard" data-component="query"  hidden>
<br>
date nullable Timestamp of the query</p>
<p>
<b><code>travelType</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="travelType" data-endpoint="GETapi-v0-trains-stationboard" data-component="query"  hidden>
<br>
nullable Must be one of the following: 'nationalExpress', 'express', 'regionalExp', 'regional', 'suburban', 'bus', 'ferry', 'subway', 'tram', 'taxi'</p>
</form>


## Train trip
Returns the stopovers and other details of a specific train.

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/trip?tripID=1%7C1937395%7C17%7C80%7C24112019&lineName=62&start=8079041" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/trip"
);

let params = {
    "tripID": "1|1937395|17|80|24112019",
    "lineName": "62",
    "start": "8079041",
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
{
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
        "stopovers": "[{\"stop\":{\"type\":\"stop\",\"id\":\"8079041\",\"name\":\"Karlsruhe Bahnhofsvorplatz\",\"location\":{\"type\":\"location\",\"id\":\"8079041\",\"latitude\":48.994348,\"longitude\":8.399583},\"products\":{\"nationalExpress\":true,\"national\":true,\"regionalExp\":true,\"regional\":true,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":null,\"arrivalDelay\":null,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:50:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 62 Entenfang >70 from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"362191\",\"name\":\"Ebertstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"362191\",\"latitude\":48.994519,\"longitude\":8.395395},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:51:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:51:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377218\",\"name\":\"Welfenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377218\",\"latitude\":48.99531,\"longitude\":8.386118},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:53:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:53:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721329\",\"name\":\"Beiertheim West, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721329\",\"latitude\":48.994896,\"longitude\":8.382693},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:54:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:54:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721468\",\"name\":\"Windeckstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721468\",\"latitude\":48.99122,\"longitude\":8.371016},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":true}},\"arrival\":\"2019-11-24T15:55:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:55:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723868\",\"name\":\"Hardecksiedlung, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723868\",\"latitude\":48.992262,\"longitude\":8.368813},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T15:56:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:56:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721437\",\"name\":\"Schwimmschulweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721437\",\"latitude\":48.994653,\"longitude\":8.364247},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:57:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:57:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721380\",\"name\":\"Hornisgrindestra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721380\",\"latitude\":48.995966,\"longitude\":8.360409},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:58:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:58:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721349\",\"name\":\"Edelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721349\",\"latitude\":48.996865,\"longitude\":8.354071},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T15:59:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T15:59:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721458\",\"name\":\"Wattkopfstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721458\",\"latitude\":48.994573,\"longitude\":8.351635},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:00:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:00:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721333\",\"name\":\"Bernsteinstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721333\",\"latitude\":48.991912,\"longitude\":8.348363},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:01:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:01:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721350\",\"name\":\"Eichelbergstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721350\",\"latitude\":48.99353,\"longitude\":8.345109},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:02:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:02:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721455\",\"name\":\"T\\u00dcV, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721455\",\"latitude\":48.996667,\"longitude\":8.348426},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:03:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:03:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723629\",\"name\":\"St. Josef Kirche, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723629\",\"latitude\":48.998851,\"longitude\":8.348615},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:04:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:04:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721346\",\"name\":\"Durmersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721346\",\"latitude\":49.000514,\"longitude\":8.350619},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:05:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:05:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"723802\",\"name\":\"Sinner (Durmersheimer Stra\\u00dfe), Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"723802\",\"latitude\":49.002663,\"longitude\":8.35506},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:06:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:06:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721415\",\"name\":\"Gr\\u00fcnwinkel Friedhof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721415\",\"latitude\":49.005521,\"longitude\":8.356103},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:07:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:07:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721339\",\"name\":\"Blohnstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721339\",\"latitude\":49.007346,\"longitude\":8.356417},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:08:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:08:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 62 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"363851\",\"name\":\"Entenfang, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"363851\",\"latitude\":49.010609,\"longitude\":8.359501},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":true,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":true}},\"arrival\":\"2019-11-24T16:10:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:12:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null,\"remarks\":[{\"type\":\"hint\",\"code\":\"text.journeystop.product.or.direction.changes.stop.message\",\"text\":\"As Bus 70 heading towards 70 Heidehof from here\"}]},{\"stop\":{\"type\":\"stop\",\"id\":\"721398\",\"name\":\"K\\u00e4rcherstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721398\",\"latitude\":49.015895,\"longitude\":8.361559},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:14:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:14:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"373522\",\"name\":\"Hertzstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"373522\",\"latitude\":49.020246,\"longitude\":8.364984},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721326\",\"name\":\"Barbaraweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721326\",\"latitude\":49.023967,\"longitude\":8.365946},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:15:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:15:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721410\",\"name\":\"Madenburgweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721410\",\"latitude\":49.027051,\"longitude\":8.36546},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:16:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:16:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721332\",\"name\":\"Berliner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721332\",\"latitude\":49.03026,\"longitude\":8.364939},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721364\",\"name\":\"Germersheimer Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721364\",\"latitude\":49.032894,\"longitude\":8.364858},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:17:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:17:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721462\",\"name\":\"Wei\\u00dfenburger Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721462\",\"latitude\":49.036957,\"longitude\":8.366989},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:18:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:18:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721436\",\"name\":\"Schweigener Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721436\",\"latitude\":49.03844,\"longitude\":8.369586},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:19:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:19:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721385\",\"name\":\"Kaiserslauterner Stra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721385\",\"latitude\":49.036687,\"longitude\":8.374153},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:20:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:20:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721900\",\"name\":\"Neureut Flughafenstra\\u00dfe, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721900\",\"latitude\":49.036139,\"longitude\":8.380535},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:21:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:21:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"721172\",\"name\":\"Neureut Rosmarinweg, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"721172\",\"latitude\":49.035482,\"longitude\":8.385605},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":false,\"taxi\":false}},\"arrival\":\"2019-11-24T16:22:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":\"2019-11-24T16:22:00+01:00\",\"departureDelay\":0,\"departurePlatform\":null},{\"stop\":{\"type\":\"stop\",\"id\":\"377009\",\"name\":\"Heidehof, Karlsruhe\",\"location\":{\"type\":\"location\",\"id\":\"377009\",\"latitude\":49.031536,\"longitude\":8.387008},\"products\":{\"nationalExpress\":false,\"national\":false,\"regionalExp\":false,\"regional\":false,\"suburban\":false,\"bus\":true,\"ferry\":false,\"subway\":false,\"tram\":true,\"taxi\":false}},\"arrival\":\"2019-11-24T16:23:00+01:00\",\"arrivalDelay\":0,\"arrivalPlatform\":null,\"departure\":null,\"departureDelay\":null,\"departurePlatform\":null}]",
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
<div id="execution-results-GETapi-v0-trains-trip" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-trip"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-trip"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-trip" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-trip"></code></pre>
</div>
<form id="form-GETapi-v0-trains-trip" data-method="GET" data-path="api/v0/trains/trip" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-trip', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-trip" onclick="tryItOut('GETapi-v0-trains-trip');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-trip" onclick="cancelTryOut('GETapi-v0-trains-trip');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-trip" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/trip</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-trip" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-trip" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>tripID</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="tripID" data-endpoint="GETapi-v0-trains-trip" data-component="query" required  hidden>
<br>
The given ID of the trip.</p>
<p>
<b><code>lineName</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="lineName" data-endpoint="GETapi-v0-trains-trip" data-component="query" required  hidden>
<br>
The given name of the line.</p>
<p>
<b><code>start</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="start" data-endpoint="GETapi-v0-trains-trip" data-component="query" required  hidden>
<br>
The IBNR of the starting point of the train.</p>
</form>


## Check in
Creates a check in for a train

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/trains/checkin?tripID=1%7C1937395%7C17%7C80%7C24112019&lineName=62&start=8079041&destination=8079041&body=This+is+my+first+Check-in%21&tweet=1&toot=" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
    .forEach(key => url.searchParams.append(key, params[key]));

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


> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

```json

<<>>
```
> Example response (200, Successfully checked in):

```json
{
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
<div id="execution-results-POSTapi-v0-trains-checkin" hidden>
    <blockquote>Received response<span id="execution-response-status-POSTapi-v0-trains-checkin"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v0-trains-checkin"></code></pre>
</div>
<div id="execution-error-POSTapi-v0-trains-checkin" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v0-trains-checkin"></code></pre>
</div>
<form id="form-POSTapi-v0-trains-checkin" data-method="POST" data-path="api/v0/trains/checkin" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-trains-checkin', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v0-trains-checkin" onclick="tryItOut('POSTapi-v0-trains-checkin');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v0-trains-checkin" onclick="cancelTryOut('POSTapi-v0-trains-checkin');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v0-trains-checkin" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-black">POST</small>
 <b><code>api/v0/trains/checkin</code></b>
</p>
<p>
<label id="auth-POSTapi-v0-trains-checkin" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="POSTapi-v0-trains-checkin" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>tripID</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="tripID" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" required  hidden>
<br>
ID of the to-be-ckecked-in trip.</p>
<p>
<b><code>lineName</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="lineName" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" required  hidden>
<br>
ID of the to-be-checked-in trip.</p>
<p>
<b><code>start</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="start" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" required  hidden>
<br>
The IBNR of the starting point of the train.</p>
<p>
<b><code>destination</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="destination" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" required  hidden>
<br>
The IBNR of the destination.</p>
<p>
<b><code>body</code></b>&nbsp;&nbsp;<small>string</small>     <i>optional</i> &nbsp;
<input type="text" name="body" data-endpoint="POSTapi-v0-trains-checkin" data-component="query"  hidden>
<br>
max:280 The body of the status.</p>
<p>
<b><code>tweet</code></b>&nbsp;&nbsp;<small>boolean</small>     <i>optional</i> &nbsp;
<label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="tweet" value="1" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" ><code>true</code></label>
<label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="tweet" value="0" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" ><code>false</code></label>
<br>
Should this post be tweeted?</p>
<p>
<b><code>toot</code></b>&nbsp;&nbsp;<small>boolean</small>     <i>optional</i> &nbsp;
<label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="toot" value="1" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" ><code>true</code></label>
<label data-endpoint="POSTapi-v0-trains-checkin" hidden><input type="radio" name="toot" value="0" data-endpoint="POSTapi-v0-trains-checkin" data-component="query" ><code>false</code></label>
<br>
Should this post be posted to mastodon?</p>
</form>


## Latest train stations
Retrieves the last 5 station the logged in user arrived at

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/latest" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
[
    {
        "id": 3,
        "ibnr": "8079041",
        "name": "Karlsruhe Bahnhofsvorplatz",
        "latitude": 48.994348,
        "longitude": 48.994348
    }
]
```
> Example response (401, Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied.):

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
<div id="execution-results-GETapi-v0-trains-latest" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-latest"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-latest"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-latest" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-latest"></code></pre>
</div>
<form id="form-GETapi-v0-trains-latest" data-method="GET" data-path="api/v0/trains/latest" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-latest', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-latest" onclick="tryItOut('GETapi-v0-trains-latest');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-latest" onclick="cancelTryOut('GETapi-v0-trains-latest');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-latest" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/latest</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-latest" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-latest" data-component="header"></label>
</p>
</form>


## Home Station
Gets the home station of the logged in user

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/home" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
{
    "id": 3,
    "ibnr": "8079041",
    "name": "Karlsruhe Bahnhofsvorplatz",
    "latitude": 48.994348,
    "longitude": 48.994348
}
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
<div id="execution-results-GETapi-v0-trains-home" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-trains-home"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-trains-home"></code></pre>
</div>
<div id="execution-error-GETapi-v0-trains-home" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-trains-home"></code></pre>
</div>
<form id="form-GETapi-v0-trains-home" data-method="GET" data-path="api/v0/trains/home" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-trains-home', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-trains-home" onclick="tryItOut('GETapi-v0-trains-home');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-trains-home" onclick="cancelTryOut('GETapi-v0-trains-home');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-trains-home" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/trains/home</code></b>
</p>
<p>
<label id="auth-GETapi-v0-trains-home" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-trains-home" data-component="header"></label>
</p>
</form>


## Home Station
Sets the home station for the logged in user

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/trains/home?ibnr=8123" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/home"
);

let params = {
    "ibnr": "8123",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "PUT",
    headers,
}).then(response => response.json());
```


> Example response (200):

```json
"Ost.Punkt 812 km"
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
<div id="execution-results-PUTapi-v0-trains-home" hidden>
    <blockquote>Received response<span id="execution-response-status-PUTapi-v0-trains-home"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v0-trains-home"></code></pre>
</div>
<div id="execution-error-PUTapi-v0-trains-home" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v0-trains-home"></code></pre>
</div>
<form id="form-PUTapi-v0-trains-home" data-method="PUT" data-path="api/v0/trains/home" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-trains-home', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-PUTapi-v0-trains-home" onclick="tryItOut('PUTapi-v0-trains-home');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-PUTapi-v0-trains-home" onclick="cancelTryOut('PUTapi-v0-trains-home');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-PUTapi-v0-trains-home" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-darkblue">PUT</small>
 <b><code>api/v0/trains/home</code></b>
</p>
<p>
<label id="auth-PUTapi-v0-trains-home" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-trains-home" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
<p>
<b><code>ibnr</code></b>&nbsp;&nbsp;<small>integer</small>  &nbsp;
<input type="number" name="ibnr" data-endpoint="PUTapi-v0-trains-home" data-component="query" required  hidden>
<br>
</p>
</form>



