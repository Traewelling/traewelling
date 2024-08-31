# APIv1 Changelog

In this we try to keep track of changes to the API.
Primarily this should document changes that are not backwards compatible or belongs to already documented endpoints.
This is to help you keep track of the changes and to help you update your code accordingly.

# 2024-08-14

The following endpoints were migrated to match the API conventions. Please also have a look at the API documentation

- `GET /settings/followers` -> `GET /user/self/followers`
- `DELETE /user/removeFollower` with userId in body -> `DELETE /user/self/followers/:id` without userId in body
- `GET /settings/follow-requests` -> `GET /user/self/follow-requests`
- `PUT /user/acceptFollowRequest` with userId in body -> `PUT /user/self/follow-requests/:userId` without userId in body
- `DELETE /user/rejectFollowRequest` with userId in body -> `DELETE /user/self/follow-requests/:userId` without userId in body
- `GET /settings/followings` -> `GET /user/self/followings`

The old endpoints will be removed after 2024-09-30.

# 2024-07-17

The Endpoint `/report` now correctly uses camelCase for the `subjectType` and `subjectId` field.
Since the current usage of this endpoint is very low, the old snake_case fields will be removed after 2024-08-17.

# 2024-06-28

The `LeaderboardUserResource` is now returning the whole `LightUserResource` for the user who created it in the `user`
field.
Thus the following fields of the `LeaderboardUserResource` are now **marked as deprecated and will be removed after
August 2024**.

- `id`
- `displayName`
- `username`
- `profilePicture`

This data is also available in the `user` field.

## 2024-06-01

Changed `/operator` to `/operators`

## 2024-05-31

The `StatusResource` is now returning the whole `LightUserResource` for the user who created it in the `userDetails`
field.
Thus the following fields of the `StatusResource` are now **marked as deprecated and will be removed after August 2024
**.

- `user`
- `username`
- `profilePicture`
- `preventIndex`

This data is also available in the `userDetails` field.

## 2024-05-30

Added `GET /operator` endpoint to get a paginated list of all operators.

## 2024-05-30

Renamed `trainDuration` and `trainDistance` attributes to `totalDuration` and `totalDistance` in all `User` object.
(We have more than just trains.)

The old attributes will be removed after 2024-08.

## 2024-05-30

Deprecated `GET /activeEvents` endpoint, which will be removed after 2024-08.

Change behavior of `GET /events` endpoint:

- Add `timestamp` and `upcoming` query parameters to filter events by timestamp and upcoming events.
- Default behavior (without query parameters) is to return active events.

## 2024-05-28

You can now edit the `eventId` of a status via the `PUT /status/{id}` endpoint.

## 2024-04-27

New endpoint `POST /report` for reporting a Status, Event or User to the admins.
See the [documentation](https://traewelling.de/api/documentation) for more information.

## 2024-03-16

Replaced `GET /trains/station/{name}/departures` with `GET /station/{id}/departures`.
The old endpoint is marked as deprecated and will be removed after 2024-06.

Please note, that the ID is the Träwelling internal ID and not the IBNR!

## 2024-03-10

Replaced `PUT /trains/station/{name}/home` with `PUT /station/{id}/home`.
The old endpoint is marked as deprecated and will be removed after 2024-06.

Please note, that the ID is the Träwelling internal ID and not the IBNR!

## 2024-03-01

> [!WARNING]
> Possibly breaking change: The implementation of next/prev links on user/{username}/statuses endpoint has been changed
> to adhere to the documentation.

## 2024-01-21

The attribute `twitter` in the `User` Model is already always `null` and will be removed after 2024-03.
Please prepare your code accordingly.

## 2023-11-23

The attribute `type` in the `Status` Model is marked as deprecated and now returns a blank string for all statuses as it
is not used.

> [!IMPORTANT]
> **Backwards compatibility** - Will not break your code until February 2024.
>
> After that, the attribute will be removed.

## 2023-11-22

The attribute `role` in the `User` Model is marked as deprecated and now returns `0` for all users.

> [!IMPORTANT]
> **Backwards compatibility** - Will not break your code until February 2024.
>
> After that, the attribute will be removed.

## 2023-11-21

The attribute `twitterUrl` in the `User` Model is marked as deprecated and returns `null`, as Traewelling does not
support Twitter anymore.

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
