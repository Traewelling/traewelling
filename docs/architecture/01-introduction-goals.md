# 1. Introduction and Goals

## 1.1 Requirements Overview

Träwelling is a free, open source check-in service for public transit journeys in
Europe. Users check into trains, trams, buses, ferries and other transit vehicles, log
their journeys, follow other users and optionally post their journeys to Mastodon.

Core capabilities:

- **Check-in**: find a departure at a station, pick a trip and a destination, and record
  it as a status.
- **Journey data**: trips, stopovers, real time delays and routes come from an external
  transit data provider.
- **Social**: statuses, likes, mentions, following, friend requests, blocking and muting.
- **Statistics**: distance, duration, travelled routes, yearly reviews.
- **Public API**: a versioned REST API used by Träwelling's own frontend and by many third
  party clients and integrations.

## 1.2 Quality Goals

todo

## 1.3 Stakeholders

| Role                      | Expectation                                                                           |
|---------------------------|---------------------------------------------------------------------------------------|
| End users                 | Reliable check-ins, correct statistics, control over who sees what                    |
| Third party API consumers | Stable, documented, versioned endpoints and advance notice of breaking changes        |
| Contributors              | Understandable layering, tests, conventions that do not have to be reverse engineered |
| Self hosters              | Documented deployment, migrations that run unattended                                 |
| Data providers            | Fair use of their APIs, correct attribution and licensing                             |

