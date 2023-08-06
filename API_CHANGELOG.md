# APIv1 Changelog

In this we try to keep track of changes to the API.
Primarily this should document changes that are not backwards compatible or belongs to already documented endpoints.
This is to help you keep track of the changes and to help you update your code accordingly.

## upcoming

### `GET /api/v1/trains/station/{name}/departures`

- Doesn't return `meta->times` anymore. Was a useless field anyway. You know the time you requested. And then just
  subtract or add 15 minutes.

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
