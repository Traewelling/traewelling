# APIv1 Changelog

In this we try to keep track of changes to the API.
Primarily this should document changes that are not backwards compatible or belongs to already documented endpoints.
This is to help you keep track of the changes and to help you update your code accordingly.

Want to be notified about breaking API changes? Comment
in [Discussion #2619](https://github.com/Traewelling/traewelling/discussions/2619) to join the notification team.

## Upcoming Breaking Changes

Fields and endpoints listed here are deprecated and will be removed at the indicated date.
During the transition period they continue to work but should no longer be relied upon.
Check back here regularly to stay ahead of removals.

| Announced  | What?                                                                                                                                                                                                             | Safe until | PR                                                            |
|------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|---------------------------------------------------------------|
| 2026-03-07 | `EventDetailsResource.trainDistance` is deprecated → use `totalDistance` instead                                                                                                                                  | 2026-09-30 | [#4554](https://github.com/Traewelling/traewelling/pull/4554) |
| 2026-03-07 | `EventDetailsResource.trainDuration` is deprecated → use `totalDuration` instead                                                                                                                                  | 2026-09-30 | [#4554](https://github.com/Traewelling/traewelling/pull/4554) |
| 2026-03-07 | `CheckinSuccessResource.points.additional` is always `null` and deprecated                                                                                                                                        | 2026-09-30 | [#4553](https://github.com/Traewelling/traewelling/pull/4553) |
| 2026-03-07 | `DepartureResource.destination` is deprecated → use `direction` instead                                                                                                                                           | 2026-09-30 | [#4552](https://github.com/Traewelling/traewelling/pull/4552) |
| 2026-03-07 | `DepartureResource.delay` is deprecated → use `when`/`plannedWhen` difference instead                                                                                                                             | 2026-09-30 | [#4552](https://github.com/Traewelling/traewelling/pull/4552) |
| 2026-03-07 | `DepartureResource` HAFAS-compat fields deprecated: `provenance`, `remarks`, `origin`, `currentTripPosition`, `loadFactor`, `stop.products`, `line.public`, `line.adminCode`, `line.productName`, `line.operator` | 2026-09-30 | [#4552](https://github.com/Traewelling/traewelling/pull/4552) |
| 2026-03-07 | `WebhookResource.clientId` is deprecated → use `client.id` instead                                                                                                                                                | 2026-09-30 | [#4550](https://github.com/Traewelling/traewelling/pull/4550) |
| 2026-03-07 | `WebhookResource.userId` is deprecated → use `user.id` instead                                                                                                                                                    | 2026-09-30 | [#4550](https://github.com/Traewelling/traewelling/pull/4550) |
| 2026-03-05 | `StationResource.ibnr` is always `null` → use `identifiers` array with type `de_db_ibnr` instead                                                                                                                  | 2026-09-30 | [#4500](https://github.com/Traewelling/traewelling/pull/4500) |
| 2026-03-05 | `StationResource.rilIdentifier` is always `null` → use `identifiers` array with type `de_db_ril100` instead                                                                                                       | 2026-09-30 | [#4500](https://github.com/Traewelling/traewelling/pull/4500) |
| 2026-03-07 | `StopoverResource.arrival` is deprecated → use `arrivalReal` (if not null) or `arrivalPlanned` instead                                                                                                            | 2026-09-30 | [#4551](https://github.com/Traewelling/traewelling/pull/4551) |
| 2026-03-07 | `StopoverResource.departure` is deprecated → use `departureReal` (if not null) or `departurePlanned` instead                                                                                                      | 2026-09-30 | [#4551](https://github.com/Traewelling/traewelling/pull/4551) |
| 2026-03-05 | `StopoverResource.rilIdentifier` is always `null` → use the station identifiers endpoint instead                                                                                                                  | 2026-09-30 | [#4502](https://github.com/Traewelling/traewelling/pull/4502) |
| 2026-03-05 | `StopoverResource.evaIdentifier` is always `null` → use the station identifiers endpoint instead                                                                                                                  | 2026-09-30 | [#4502](https://github.com/Traewelling/traewelling/pull/4502) |
| 2026-01-20 | `StatusResource.train` is deprecated → use `checkin` instead                                                                                                                                                      | 2026-07-31 | [#4313](https://github.com/Traewelling/traewelling/pull/4313) |
| 2026-01-20 | `StatusResource.userDetails` is deprecated → use `user` instead                                                                                                                                                   | 2026-07-31 | [#4313](https://github.com/Traewelling/traewelling/pull/4313) |
| 2026-01-20 | `LightUserResource.mastodonUrl` is always `null` → use `mastodon.server` instead                                                                                                                                  | 2026-07-31 | [#4313](https://github.com/Traewelling/traewelling/pull/4313) |
| 2026-03-20 | `POST /api/v1/report` is deprecated → use `POST /api/v1/reports` instead                                                                                                                                          | 2026-09-30 | [#4602](https://github.com/Traewelling/traewelling/pull/4602) |
| 2026-03-30 | `GET /api/v1/static/privacy` is deprecated -> use `GET /api/v1/privacy-policies/current` instead                                                                                                                  | 2026-09-30 | [#4650](https://github.com/Traewelling/traewelling/pull/4650) |
| 2026-03-30 | `PUT /api/v1/settings/acceptPrivacy` is deprecated -> use `PUT /api/v1/privacy-policies/accept` instead                                                                                                           | 2026-09-30 | [#4650](https://github.com/Traewelling/traewelling/pull/4650) |
| 2026-04-01 | `DepartureResource.stop.*` is deprecated → use `DepartureResource.station` instead                                                                                                                                | 2026-09-30 | [#4663](https://github.com/Traewelling/traewelling/pull/4663) |
| 2026-04-07 | `OperatorResource.identifier` is deprecated: legacy HAFAS operator ID, always `null` for new operators                                                                                                            | 2026-09-30 | [#4675](https://github.com/Traewelling/traewelling/pull/4675) |
| 2026-04-07 | `OperatorResource.id` is deprecated as integer: will become a UUID after 2026-09-30. prefer `uuid` instead while migration-period                                                                                 | 2026-09-30 | [#4676](https://github.com/Traewelling/traewelling/pull/4676) |

---

# 2026-04-11

**New endpoints:**
- `GET /api/v1/applications`: List all OAuth applications owned by the authenticated user. Requires a personal access token.
- `POST /api/v1/applications`: Create a new OAuth application. Returns `plainSecret` on creation for confidential clients. Requires a personal access token.
- `PUT /api/v1/applications/{clientId}`: Update an OAuth application owned by the authenticated user. Requires a personal access token.
- `DELETE /api/v1/applications/{clientId}`: Delete an OAuth application owned by the authenticated user. Requires a personal access token.
- `GET /api/v1/users/self/blocks`: List all users blocked by the authenticated user. Requires `write-blocks` scope.
- `GET /api/v1/users/self/mutes`: List all users muted by the authenticated user. Requires `write-blocks` scope.
- `PUT /api/v1/settings/password`: Change the authenticated user's password. Requires a personal access token.

**`PrivacyPolicyResource` extended:**
- Added `id` field: UUID of the privacy policy.
- Added `hasOldAcceptance` field: `true` if the user has accepted a previous version of the policy but not the current one.
- `acceptedAt` now returns the actual acceptance timestamp for the authenticated user (was always `null` before).

**Other:**
- Added `GET /api/v1/applications/{clientId}/webhook-stats`: Returns webhook call log statistics (last 7 days) for an OAuth application. Requires open-beta or admin role; only the application owner or admins can access it.
- Added `disabledAt` field to `WebhookResource`: ISO 8601 timestamp when a webhook was automatically disabled due to repeated failures, or `null` if active.
- Webhooks are now automatically disabled after 5 consecutive final failures. The webhook owner receives an in-app notification.

# 2026-04-07

### Operator migration: UUID primary keys

Operators have been migrated from integer IDs to UUIDs. The `OperatorResource` was extended:

**New fields:**
- `type`: always `"operator"` (as of friendly public transport format)
- `uuid`: stable UUID identifier, use this going forward
- `identifiers`: array of `{ type, identifier, name }` objects covering all known provider IDs (e.g. `motis`, `hafas`, `wikidata`). Returned by `GET /api/v1/operators`.

**Deprecated fields** (see "Upcoming Breaking Changes" table above):
- `id`: still returns the numeric legacy ID for now, but will become a UUID after 2026-09-30. Use `uuid` instead (will be renamed to `id` later)
- `identifier`: single legacy HAFAS operator ID string. Always `null` for new operators. Use the `identifiers` array instead.

**Endpoint changes:**
- `GET /api/v1/operators`: returns `identifiers` for each operator.
- `POST /api/v1/trips` (`operatorId` field): now accepts both UUID and numeric legacy ID. UUID should be preferred.

# 2026-03-28

- `DepartureResource`: added `cancelled` (bool) field — `true` when the departure is cancelled according to the data
  provider

# 2026-03-21

- `GET /api/v1/status/{id}`: New optional query parameter `withIdentifiers`. When `true`, includes `identifiers` in
  origin and destination stopovers (`StopoverResource`)

# 2026-03-20

Added `moderation_notes` (string, nullable), `lock_visibility` (boolean, nullable) and `hide_body` (boolean, nullable)
to `StatusResource`. All three fields are only present for the status owner. `moderation_notes` contains a note left by
the moderation team explaining why the status was moderated. `lock_visibility` indicates that the visibility cannot be
changed by the owner. `hide_body` indicates that the status text is hidden from other users.

Added fields to `StationResource`: `time_offset` (integer, nullable) and `created_at` (ISO 8601 string, nullable).

Added fields to `StationIdentifierResource`: `name` (string, nullable) and `origin` (string, nullable).

`POST /api/v1/report`: `description` is now required (minimum 10 characters). Previously it was optional/nullable.

Added `POST /api/v1/reports` as correctly named replacement for the deprecated `POST /api/v1/report` endpoint.

Added `?query` parameter to `GET /api/v1/operators`: filters operators by name (minimum 2 characters, case-insensitive).
Without the parameter, the endpoint behaves as before.

Added new `POST /api/v1/trips` endpoint for creating manual trips.

---

# 2026-03-15 (Ticket management – closed beta)

Added new `GET|POST|PUT|DELETE /api/v1/tickets` endpoints for managing transit tickets.
Only available to users with the `closed-beta` role.

Added `ticket` field to `StatusResource`: only present when the authenticated user is the status owner and a ticket is
assigned.

Added `PUT /api/v1/statuses/{id}/tickets` endpoint for assigning or removing a ticket from a status (`ticketId`: UUID or
`null`).

---

# 2026-03-14 (Freight train category)

Added new transport category `freightTrain` to `HafasTravelType`. Users can now create manual trips with the freight
train type.

---

# 2026-03-07 (UUID support for Users)

The `UserResource` now includes a `uuid` field alongside the existing integer `id`.
All API endpoints that previously accepted a numeric user ID in the path (e.g. `/user/{id}/follow`, `/user/{id}/block`,
`/user/{id}/mute`, `/user/self/followers/{userId}`, `/user/self/follow-requests/{userId}`, `/user/{user}/trusted`,
`/user/{user}/trusted/{trusted}`) now accept **both** the integer ID and the UUID interchangeably.

Where possible, prefer using the UUID, it will become the primary identifier once the migration is complete.

The integer ID is **not** deprecated and will continue to work until further notice.

# 2026-03-07

The `EventDetailsResource` fields `trainDistance` and `trainDuration` are now **deprecated** and will be removed after *
*2026-09-30**.
Use `totalDistance` and `totalDuration` instead — both fields are now returned alongside the old ones.

The `CheckinSuccessResource.points.additional` field is now **deprecated** (always `null`) and will be removed after *
*2026-09-30**.

The following fields of `DepartureResource` are now **deprecated** and will be removed after **2026-09-30**:

- `destination` → use `direction` for the destination name
- `delay` → calculate from `when`/`plannedWhen` difference instead
- HAFAS-compatibility placeholders (always static/null values): `provenance`, `remarks`, `origin`,
  `currentTripPosition`, `loadFactor`, `stop.products`, `line.public`, `line.adminCode`, `line.productName`,
  `line.operator`

The `StopoverResource.arrival` and `StopoverResource.departure` fields are now **deprecated**.
Use `arrivalReal` (falling back to `arrivalPlanned`) and `departureReal` (falling back to `departurePlanned`) instead.
Both deprecated fields will be removed after **2026-09-30**.

The `WebhookResource` now includes a `user` field containing the full `LightUserResource` of the user who created
the webhook.

The following fields are now **deprecated** and will be removed after **2026-09-30**:

- `clientId` → use `client.id` instead
- `userId` → use `user.id` instead

# 2024-08-14

The following endpoints were migrated to match the API conventions. Please also have a look at the API documentation

- `GET /settings/followers` -> `GET /user/self/followers`
- `DELETE /user/removeFollower` with userId in body -> `DELETE /user/self/followers/:id` without userId in body
- `GET /settings/follow-requests` -> `GET /user/self/follow-requests`
- `PUT /user/acceptFollowRequest` with userId in body -> `PUT /user/self/follow-requests/:userId` without userId in body
- `DELETE /user/rejectFollowRequest` with userId in body -> `DELETE /user/self/follow-requests/:userId` without userId
  in body
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
