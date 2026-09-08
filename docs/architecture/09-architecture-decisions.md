# 9. Architecture Decisions

Significant, expensive to reverse decisions are recorded as Architecture Decision Records
in [`decisions/`](decisions/).

## What deserves an ADR

Write one when a decision is hard to reverse, affects many parts of the system, or when
somebody will predictably ask "why on earth is it done like this" in a year. Examples:
identifier strategy, choice of data provider, authentication mechanism, dropping a
supported database driver.

Do not write one for routine implementation choices, naming, or anything a pull request
description already explains adequately.

## Process

1. Copy [`decisions/_template.md`](decisions/_template.md) to
   `decisions/NNNN-short-title.md` with the next free number. If two open pull requests
   claim the same number, the one merged later renumbers before merge.
2. Open it with status `proposed` and discuss it in the pull request.
3. On merge, set the status to `accepted` and add it to the index below.
4. **Do not rewrite an accepted ADR.** Correcting a factual error or a broken link is
   fine, changing the reasoning is not. If the decision itself changes, write a new ADR,
   mark the old one `superseded by ADR-NNNN`, and link both ways.
5. An ADR records a decision, not a status. Keep migration progress, open tickets and
   anything else that goes stale out of it, so that rule 4 stays realistic.

## Index

| ADR                                                | Title                 | Status   | Date       |
|----------------------------------------------------|-----------------------|----------|------------|
| [ADR-0001](decisions/0001-uuid-as-primary-key.md)  | UUIDs as primary keys | accepted | 2026-08-08 |
| [ADR-0002](decisions/0002-per-endpoint-api-versioning.md) | Break an endpoint by versioning it | proposed | 2026-08-29 |
