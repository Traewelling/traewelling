# ADR-0002: Break an endpoint by versioning it, not by deprecating fields inside it

- **Status**: proposed
- **Date**: 2026-08-29
- **Deciders**: Träwelling maintainers
- **Related**: `API_CHANGELOG.md`, [RFC 8594 (The Sunset HTTP Header Field)](https://www.rfc-editor.org/info/rfc8594/)

## Context and Problem Statement

Breaking changes to the public API have so far been made inside the existing endpoint. A field
gets a successor, both are returned side by side, the old one is listed in the "Upcoming Breaking
Changes" table of `API_CHANGELOG.md` with a date at least six months out, and it is removed once
that date passes.

The result is that one endpoint carries several states at the same time. `StopoverResource` alone
currently returns `id` (which holds a station id and will later hold a stopover id), `stopoverId`
(which holds today what `id` will hold tomorrow), `name` next to `station.name`, `identifiers`
next to `station.identifiers`. A client author reading the response cannot see
which fields are the current ones, and the table they have to consult instead keeps growing.

This is harder to migrate against than a clean cut, not easier, which was the point of the
transition periods in the first place.

## Decision Drivers

- A client author should be able to see what an endpoint returns without cross-referencing dates
- One endpoint, one answer: no field that means different things before and after a deadline
- Existing clients must keep working until they have had a fair chance to migrate
- Migration should be a single, discrete step a client can plan, not a slow drift

## Decision Outcome

**When an endpoint has to break, it gets a new version. The old version stays as it is and is
switched off later, with notice.**

The old and the new shape live next to each other as two separate endpoints, not as two states of
one endpoint. `/v1/status/{id}` keeps answering exactly what it answers today, unchanged, until it
is switched off; `/v2/status/{id}` answers the new shape. A client migrates one endpoint at a
time, in one step, by changing a url. Nothing changes underneath a client that has not moved yet.

Fields are no longer deprecated inside an endpoint. Renaming, removing or repurposing a field is a
reason to publish a new version of that endpoint, not to add a second field beside the first.

To make that possible for a single endpoint, the version moved out of the server url and into the
path: the documented server url ends at `/api` and every operation carries its own version as its
first path segment. A version is a single value per document, so as long as it sat in the server
url, raising one endpoint meant raising all of them.

The "Upcoming Breaking Changes" table keeps its job, with a narrower subject: it announces which
endpoint versions are being switched off and when. The field level deprecations already in it stay
until their dates pass; no new ones are added.

A switch-off is announced in two places: in `API_CHANGELOG.md`, for a human who reads it, and in
the responses of the endpoint version itself, for a client that does not. An endpoint version with
a fixed shutdown date carries a `Sunset` header (RFC 8594) holding that date, plus a `Link` header
with the `sunset` relation type pointing at the changelog:

```http
Sunset: Wed, 30 Sep 2026 23:59:59 GMT
Link: <https://github.com/Traewelling/traewelling/blob/develop/API_CHANGELOG.md>; rel="sunset"
```

The deadline then travels on the traffic a client already makes, instead of waiting in a document
somebody has to remember to check.

### Consequences

- Good: a response shows what it means. No field is correct only relative to a date
- Good: migration is one url change per endpoint, at a moment the client picks
- Good: an endpoint nobody had a problem with is never touched
- Good: a client learns about a shutdown date from the responses it already receives, and can
  surface it without polling the changelog
- Bad: two code paths for as long as both versions are served. This is the price for not having
  two meanings in one code path, and it is bounded by the shutdown date
- Bad: the version is repeated in all 178 operation annotations instead of standing once in the
  server url. `tests/Feature/ApiDocumentationTest.php` fails if a path has no version prefix or
  resolves to no route

## Open Points

How long a superseded endpoint version is served before it is switched off is decided per
endpoint and announced in `API_CHANGELOG.md`. It is deliberately not fixed here.
