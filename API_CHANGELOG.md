# APIv1 Changelog

In this we try to keep track of changes to the API.
Primarily this should document changes that are not backwards compatible or belongs to already documented endpoints.
This is to help you keep track of the changes and to help you update your code accordingly.

## 2023-11-22

The attribute `role` in the `User` Model is marked as deprecated and now returns `0` for all users.

> [!IMPORTANT]
> **Backwards compatibility** - Will not break your code until February 2024.
>
> After that, the attribute will be removed.

## 2023-11-21

The attribute `twitterUrl` in the `User` Model is marked as deprecated and returns `null`, as Traewelling does not support Twitter anymore.

> [!IMPORTANT]
> **Backwards compatibility** - Will not break your code until February 2024.
>
> After that, the attribute will be removed.

## 2023-10-30

Deprecated `trainSpeed` and `speed` attribute in `LeaderboardUser`, `Status` and `UserBase`-object.
There are `distance` and `duration` attributes, which you can use to calculate the speed yourself.

> [!IMPORTANT]
> **Backwards compatibility** - Will not break your code until December 2023.
> 
> As of now, the `speed` attribute return 0 for all objects and will be removed after 2023-12-31.

## 2023-09-22

Dropped endpoint `POST /api/v1/auth/login` ([#1772](https://github.com/Traewelling/traewelling/issues/1772))

## 2023-08-09

Dropped endpoint `POST /api/v1/auth/signup` ([#1772](https://github.com/Traewelling/traewelling/issues/1772))

> **warning**
> Endpoint `POST /api/v1/auth/login` will be removed in the future as well.
> Please migrate to OAuth2 as soon as possible.

## 2023-08-06.2

Renamed ~~`overriddenDeparture`~~ to `manualDeparture` and ~~`overriddenArrival`~~ to `manualArrival` in all endpoints
which
return a `Status` object ([#1809](https://github.com/Traewelling/traewelling/pull/1809))

Affected endpoints:

- `GET /api/v1/event/{slug}/statuses`
- `GET /api/v1/statistics/daily/{date}`
- `GET /api/v1/statuses`
- `GET /api/v1/dashboard`
- `GET /api/v1/dashboard/future`
- `GET /api/v1/dashboard/global`
- `GET /api/v1/user/{username}/statuses`
- `POST /api/v1/trains/checkin`
- `GET /api/v1/user/statuses/active`
- `GET /api/v1/status/{id}`
- `PUT /api/v1/status/{id}`

> [!IMPORTANT]
> **Backwards compatibility will be kept until 2023-10**
> The old attributes ~~`real_departure`~~ and ~~`real_arrival`~~ will still work until 2023-10, then they will be
> removed.
> Affected endpoints will return both named attributes until then.

## 2023-08-06.1

Changed input parameter ~~`real_departure`~~ to `manual_departure` and ~~`real_arrival`~~ to `manual_arrival` in
endpoint `PUT /api/v1/status/{id}` ([#1809](https://github.com/Traewelling/traewelling/pull/1809))

> [!IMPORTANT]
> **Backwards compatibility will be kept until 2023-10**
> The old attributes ~~`real_departure`~~ and ~~`real_arrival`~~ will still work until 2023-10, then they will be
> removed.
