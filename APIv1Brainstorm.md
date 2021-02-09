# API v1 (oder sowas in die Richtung)
Wichtig: ALLES (außer File-Uploads) muss application/json sein! KEIN FIRLEFANZ!!1


## Auth

### /auth/signup
#### Request
{
  "username": "Gertrud123",
  "name": "Gertrud Musterfrau",
  "email": "gertrud@traewelling.de",
  "password": "thisisnotasecurepassword123",
}

#### Response
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3Mw",
  "expires_at": "2021-10-01T12:00:00+02:00"
}

### /auth/login
#### Request
{
  "email": "gertrud@traewelling.de",
  "password": "thisisnotasecurepassword123"
}
#### Response
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3Mw",
  "expires_at": "2021-10-01T12:00:00+02:00"
}

### /auth/logout

### /auth/login/twitter GET DELETE

### /auth/login/mastodon GET DELETE

### /auth/login/apple GET DELETE

### /auth/login/google 🤢 GET DELETE

### /auth/password/reset GET

### /auth/password/change POST
Wenn angemeldet muss altes passwort mitgegeben werden, wenn nicht angemeldet muss der reset-token mitgegeben werden

### /auth/session/list GET

### /auth/session/revoke DELETE


## Account

### /account/delete DELETE

### /account/profile POST
Displayname, username, mail

### /account/settings
DBL, business-checkin (?), default dashboard

### /account/profilepicture POST DELETE

### /account/accept_privacy 


## User

### /user GET
Aktueller User

### /user/{username}

### /user/{username}/active

### /user/{username}/statuses

### /user/{username}/profilepicture

### /user/{username}/block POST DELETE

### /user/{username}/follow POST DELETE

### /user/{username}/following POST DELETE

### /user/search/{query}


## Leaderboard

### /leaderboard

### /leaderboard/friends

### /leaderboard/kilometers

### /leaderboard/{year}/{month}


## Statuses

### /statuses

### /statuses/active

### /statuses/{id} GET DELETE PUT
Auch aus event auschecken können oder sowas

### /statuses/{id}/like POST DELETE 

### /statuses/{id}/likes GET


## Events

### /event/{slug}

### /event/{slug}/statuses


## Notifications

### /notifications GET

### /notifications/{id} DELETE

### /notifications/{id}/read POST

### /notifications/{id}/unread POST


## Trains

### /trains/stations/search/{query}

### /trains/stations/{id}

### /trains/stations/{id}/departures

### /trains/stations/latest

### /trains/stations/nearby?lat=&lon=&products= GET

### /trains/stations/home GET PUT

### /trains/trip GET POST

