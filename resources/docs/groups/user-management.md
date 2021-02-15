# User management


## Login
This endpoint handles a normal user login

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/auth/login" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email":"aspernatur","password":"quia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/v0/auth/login"
);

let headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "aspernatur",
    "password": "quia"
}

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response => response.json());
```


> Example response (200):

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
    "expires_at": "2021-10-01T12:00:00+02:00"
}
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
<div id="execution-results-POSTapi-v0-auth-login" hidden>
    <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-login"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-login"></code></pre>
</div>
<div id="execution-error-POSTapi-v0-auth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v0-auth-login"></code></pre>
</div>
<form id="form-POSTapi-v0-auth-login" data-method="POST" data-path="api/v0/auth/login" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-login', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v0-auth-login" onclick="tryItOut('POSTapi-v0-auth-login');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v0-auth-login" onclick="cancelTryOut('POSTapi-v0-auth-login');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v0-auth-login" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-black">POST</small>
 <b><code>api/v0/auth/login</code></b>
</p>
<p>
<label id="auth-POSTapi-v0-auth-login" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="POSTapi-v0-auth-login" data-component="header"></label>
</p>
<h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
<p>
<b><code>email</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="email" data-endpoint="POSTapi-v0-auth-login" data-component="body" required  hidden>
<br>
</p>
<p>
<b><code>password</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="password" data-endpoint="POSTapi-v0-auth-login" data-component="body" required  hidden>
<br>
</p>

</form>


## Sign-Up
This endpoint is meant for creating a new user with username &amp; password.


You should probably start here.

> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/auth/signup" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"Gertrud123","name":"Gertrud","email":"gertrud@traewelling.de","password":"thisisnotasecurepassword123","confirm_password":"thisisnotasecurepassword123"}'

```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
    "expires_at": "2021-10-01T12:00:00+02:00",
    "message": "Registration successfull.."
}
```
> Example response (400, Bad Request The parameters are wrong or not given.):

```json

<<>>
```
<div id="execution-results-POSTapi-v0-auth-signup" hidden>
    <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-signup"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-signup"></code></pre>
</div>
<div id="execution-error-POSTapi-v0-auth-signup" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v0-auth-signup"></code></pre>
</div>
<form id="form-POSTapi-v0-auth-signup" data-method="POST" data-path="api/v0/auth/signup" data-authed="0" data-hasfiles="0" data-headers='{"Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-signup', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v0-auth-signup" onclick="tryItOut('POSTapi-v0-auth-signup');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v0-auth-signup" onclick="cancelTryOut('POSTapi-v0-auth-signup');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v0-auth-signup" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-black">POST</small>
 <b><code>api/v0/auth/signup</code></b>
</p>
<h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
<p>
<b><code>username</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="username" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required  hidden>
<br>
The @-name of a user. Must be uniqe, max 15 chars and apply to regex:/^[a-zA-Z0-9_]*$/</p>
<p>
<b><code>name</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="name" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required  hidden>
<br>
The displayname of a user. Max 50 chars.</p>
<p>
<b><code>email</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="email" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required  hidden>
<br>
The mail of the user.</p>
<p>
<b><code>password</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="password" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required  hidden>
<br>
</p>
<p>
<b><code>confirm_password</code></b>&nbsp;&nbsp;<small>string</small>  &nbsp;
<input type="text" name="confirm_password" data-endpoint="POSTapi-v0-auth-signup" data-component="body" required  hidden>
<br>
Must be equal to password.</p>

</form>


## Accept privacy
Accepts the current privacy agreement

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


> Example response (200):

```json
{
    "message": "privacy agreement successfully accepted"
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


## Logout
This terminates the session and invalidates the bearer token

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X POST \
    "http://localhost/api/v0/auth/logout" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
{
    "message": "Successfully logged out."
}
```
<div id="execution-results-POSTapi-v0-auth-logout" hidden>
    <blockquote>Received response<span id="execution-response-status-POSTapi-v0-auth-logout"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v0-auth-logout"></code></pre>
</div>
<div id="execution-error-POSTapi-v0-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v0-auth-logout"></code></pre>
</div>
<form id="form-POSTapi-v0-auth-logout" data-method="POST" data-path="api/v0/auth/logout" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('POSTapi-v0-auth-logout', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v0-auth-logout" onclick="tryItOut('POSTapi-v0-auth-logout');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v0-auth-logout" onclick="cancelTryOut('POSTapi-v0-auth-logout');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v0-auth-logout" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-black">POST</small>
 <b><code>api/v0/auth/logout</code></b>
</p>
<p>
<label id="auth-POSTapi-v0-auth-logout" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="POSTapi-v0-auth-logout" data-component="header"></label>
</p>
</form>


## Get current user
Gets the info for the currently logged in user

<small class="badge badge-darkred">requires authentication</small>



> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/v0/getuser" \
    -H "Authorization: Bearer {YOUR_AUTH_KEY}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
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
}).then(response => response.json());
```


> Example response (200):

```json
{
    "id": 1,
    "name": "J. Doe",
    "username": "jdoe",
    "train_distance": "454.59",
    "train_duration": "317",
    "points": "66",
    "averageSpeed": 100.5678954
}
```
<div id="execution-results-GETapi-v0-getuser" hidden>
    <blockquote>Received response<span id="execution-response-status-GETapi-v0-getuser"></span>:</blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v0-getuser"></code></pre>
</div>
<div id="execution-error-GETapi-v0-getuser" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v0-getuser"></code></pre>
</div>
<form id="form-GETapi-v0-getuser" data-method="GET" data-path="api/v0/getuser" data-authed="1" data-hasfiles="0" data-headers='{"Authorization":"Bearer {YOUR_AUTH_KEY}","Content-Type":"application\/json","Accept":"application\/json"}' onsubmit="event.preventDefault(); executeTryOut('GETapi-v0-getuser', this);">
<h3>
    Request&nbsp;&nbsp;&nbsp;
        <button type="button" style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v0-getuser" onclick="tryItOut('GETapi-v0-getuser');">Try it out ⚡</button>
    <button type="button" style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v0-getuser" onclick="cancelTryOut('GETapi-v0-getuser');" hidden>Cancel</button>&nbsp;&nbsp;
    <button type="submit" style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v0-getuser" hidden>Send Request 💥</button>
    </h3>
<p>
<small class="badge badge-green">GET</small>
 <b><code>api/v0/getuser</code></b>
</p>
<p>
<label id="auth-GETapi-v0-getuser" hidden>Authorization header: <b><code>Bearer </code></b><input type="text" name="Authorization" data-prefix="Bearer " data-endpoint="GETapi-v0-getuser" data-component="header"></label>
</p>
</form>



