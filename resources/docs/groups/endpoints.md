# Endpoints


## api/v0/user/accept_privacy

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/user/accept_privacy" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


<div id="execution-results-PUTapi-v0-user-accept_privacy" hidden>
    <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-accept_privacy"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v0-user-accept_privacy"></code></pre>
</div>
<div id="execution-error-PUTapi-v0-user-accept_privacy" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v0-user-accept_privacy"></code></pre>
</div>
<form id="form-PUTapi-v0-user-accept_privacy" data-method="PUT" data-path="api/v0/user/accept_privacy" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-accept_privacy', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-PUTapi-v0-user-accept_privacy" onclick="tryItOut('PUTapi-v0-user-accept_privacy');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-PUTapi-v0-user-accept_privacy" onclick="cancelTryOut('PUTapi-v0-user-accept_privacy');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-PUTapi-v0-user-accept_privacy" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-darkblue">PUT</small>
 <b><code>api/v0/user/accept_privacy</code></b>
</p>
<p>
<label id="auth-PUTapi-v0-user-accept_privacy" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-user-accept_privacy" data-component="header"></label>
</p>
</form>


## api/v0/user/leaderboard

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/user/leaderboard" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```
<div id="execution-results-GETapi-v0-user-leaderboard" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-user-leaderboard"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-user-leaderboard"></code></pre>
</div>
<div id="execution-error-GETapi-v0-user-leaderboard" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-user-leaderboard"></code></pre>
</div>
<form id="form-GETapi-v0-user-leaderboard" data-method="GET" data-path="api/v0/user/leaderboard" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user-leaderboard', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-user-leaderboard" onclick="tryItOut('GETapi-v0-user-leaderboard');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-user-leaderboard" onclick="cancelTryOut('GETapi-v0-user-leaderboard');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-user-leaderboard" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/user/leaderboard</code></b>
</p>
<p>
<label id="auth-GETapi-v0-user-leaderboard" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-user-leaderboard" data-component="header"></label>
</p>
</form>


## api/v0/user/{username}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/user/modi" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/user/modi"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```
<div id="execution-results-GETapi-v0-user--username-" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-user--username-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-user--username-"></code></pre>
</div>
<div id="execution-error-GETapi-v0-user--username-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-user--username-"></code></pre>
</div>
<form id="form-GETapi-v0-user--username-" data-method="GET" data-path="api/v0/user/{username}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user--username-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-user--username-" onclick="tryItOut('GETapi-v0-user--username-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-user--username-" onclick="cancelTryOut('GETapi-v0-user--username-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-user--username-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/user/{username}</code></b>
</p>
<p>
<label id="auth-GETapi-v0-user--username-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-user--username-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>username</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="username" data-endpoint="GETapi-v0-user--username-" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/user/search/{query}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/user/search/temporibus" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/user/search/temporibus"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```
<div id="execution-results-GETapi-v0-user-search--query-" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-user-search--query-"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-user-search--query-"></code></pre>
</div>
<div id="execution-error-GETapi-v0-user-search--query-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-user-search--query-"></code></pre>
</div>
<form id="form-GETapi-v0-user-search--query-" data-method="GET" data-path="api/v0/user/search/{query}" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user-search--query-', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-user-search--query-" onclick="tryItOut('GETapi-v0-user-search--query-');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-user-search--query-" onclick="cancelTryOut('GETapi-v0-user-search--query-');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-user-search--query-" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/user/search/{query}</code></b>
</p>
<p>
<label id="auth-GETapi-v0-user-search--query-" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-user-search--query-" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>query</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="query" data-endpoint="GETapi-v0-user-search--query-" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/user/{username}/active

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/user/et/active" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/user/et/active"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```
<div id="execution-results-GETapi-v0-user--username--active" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-user--username--active"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-user--username--active"></code></pre>
</div>
<div id="execution-error-GETapi-v0-user--username--active" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-user--username--active"></code></pre>
</div>
<form id="form-GETapi-v0-user--username--active" data-method="GET" data-path="api/v0/user/{username}/active" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-user--username--active', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-user--username--active" onclick="tryItOut('GETapi-v0-user--username--active');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-user--username--active" onclick="cancelTryOut('GETapi-v0-user--username--active');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-user--username--active" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/user/{username}/active</code></b>
</p>
<p>
<label id="auth-GETapi-v0-user--username--active" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-user--username--active" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
<p>
<b><code>username</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="username" data-endpoint="GETapi-v0-user--username--active" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/user/profilepicture

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/user/profilepicture" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/user/profilepicture"
);

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


<div id="execution-results-PUTapi-v0-user-profilepicture" hidden>
    <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-profilepicture"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v0-user-profilepicture"></code></pre>
</div>
<div id="execution-error-PUTapi-v0-user-profilepicture" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v0-user-profilepicture"></code></pre>
</div>
<form id="form-PUTapi-v0-user-profilepicture" data-method="PUT" data-path="api/v0/user/profilepicture" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-profilepicture', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-PUTapi-v0-user-profilepicture" onclick="tryItOut('PUTapi-v0-user-profilepicture');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-PUTapi-v0-user-profilepicture" onclick="cancelTryOut('PUTapi-v0-user-profilepicture');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-PUTapi-v0-user-profilepicture" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-darkblue">PUT</small>
 <b><code>api/v0/user/profilepicture</code></b>
</p>
<p>
<label id="auth-PUTapi-v0-user-profilepicture" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-user-profilepicture" data-component="header"></label>
</p>
</form>


## api/v0/user/displayname

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/user/displayname" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/user/displayname"
);

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


<div id="execution-results-PUTapi-v0-user-displayname" hidden>
    <blockquote>Received response<span id="execution-response-status-PUTapi-v0-user-displayname"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v0-user-displayname"></code></pre>
</div>
<div id="execution-error-PUTapi-v0-user-displayname" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v0-user-displayname"></code></pre>
</div>
<form id="form-PUTapi-v0-user-displayname" data-method="PUT" data-path="api/v0/user/displayname" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('PUTapi-v0-user-displayname', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-PUTapi-v0-user-displayname" onclick="tryItOut('PUTapi-v0-user-displayname');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-PUTapi-v0-user-displayname" onclick="cancelTryOut('PUTapi-v0-user-displayname');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-PUTapi-v0-user-displayname" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-darkblue">PUT</small>
 <b><code>api/v0/user/displayname</code></b>
</p>
<p>
<label id="auth-PUTapi-v0-user-displayname" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="PUTapi-v0-user-displayname" data-component="header"></label>
</p>
</form>


## api/v0/statuses/enroute/all

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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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


## api/v0/statuses/event/{statusId}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/event/sed" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/event/sed"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>


## api/v0/statuses/{statusId}/like

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/statuses/voluptatem/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/voluptatem/like"
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
<b><code>statusId</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="statusId" data-endpoint="POSTapi-v0-statuses--statusId--like" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/statuses/{statusId}/like

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/v0/statuses/rerum/like" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/rerum/like"
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
<b><code>statusId</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="statusId" data-endpoint="DELETEapi-v0-statuses--statusId--like" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/statuses/{statusId}/likes

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/rerum/likes" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/rerum/likes"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
<b><code>statusId</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="statusId" data-endpoint="GETapi-v0-statuses--statusId--likes" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/statuses

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>


## api/v0/statuses/{status}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/statuses/in" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/in"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>


## api/v0/statuses/{status}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/statuses/accusamus" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/accusamus"
);

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
<b><code>status</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="status" data-endpoint="PUTapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/statuses/{status}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/v0/statuses/nam" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/statuses/nam"
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
<b><code>status</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="status" data-endpoint="DELETEapi-v0-statuses--status-" data-component="url" required  hidden>
<br>
</p>
</form>


## api/v0/trains/autocomplete/{station}

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/autocomplete/ut" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/autocomplete/ut"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</p>
</form>


## api/v0/trains/stationboard

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/stationboard" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/stationboard"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>


## api/v0/trains/trip

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/trip" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/trip"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>


## api/v0/trains/checkin

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/trains/checkin" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/checkin"
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
</form>


## api/v0/trains/latest

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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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


## api/v0/trains/home

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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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


## api/v0/trains/home

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X PUT \
    "http://localhost/api/v0/trains/home" \
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
    method: "PUT",
    headers,
}).then(response => response.json());
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
</form>


## api/v0/trains/nearby

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/trains/nearby" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/v0/trains/nearby"
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


> Example response (401):

```json
{
    "message": "Unauthenticated."
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
</form>



