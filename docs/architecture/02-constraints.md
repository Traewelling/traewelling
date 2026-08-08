# 2. Constraints

## 2.1 Technical Constraints

| Constraint                                         | Consequence                                                                                                                                                                                                                                                                                                                                                                                      |
|----------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| PHP and Laravel                                    | Eloquent ORM, queue, migrations and Passport define the available patterns. Framework upgrades set the pace.                                                                                                                                                                                                                                                                                     |
| MySQL, MariaDB and SQLite                          | All three are supported for **production** use, from large public instances down to small self hosted ones. Every feature, migration and query must work on all three. Queries therefore go through Eloquent or the query builder. Driver specific SQL is allowed only behind an explicit driver check and with a portable fallback, for example when a migration needs an online `ALTER TABLE`. |
| Vue 3 frontend                                     | The frontend is a client of the public API, not a privileged inner layer.                                                                                                                                                                                                                                                                                                                        |
| Transitous / MOTIS as the only transit data source | Trips, stations and real time data are not owned by us. Availability, identifier stability and quality are outside our control.                                                                                                                                                                                                                                                                  |

## 2.2 Organisational Constraints

| Constraint                        | Consequence                                                                                                                                                                      |
|-----------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Public API with unknown consumers | Breaking changes require announcement in `API_CHANGELOG.md` and a transition window of at least six months. During that window the old and the new behaviour exist side by side. |

## 2.3 Legal and Regulatory Constraints

| Constraint          | Consequence                                                                                                  |
|---------------------|--------------------------------------------------------------------------------------------------------------|
| AGPL-3.0-only       | Anyone running a modified instance must publish their changes. Dependencies must carry a compatible licence. |
| GDPR                | Personal data export, deletion and data minimisation are functional requirements, not optional extras.       |
| Data provider terms | Licence and attribution information of transit data sources must be carried through to the frontend.         |
