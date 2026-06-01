# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Träwelling is a free, open-source check-in service for logging public transit journeys across Europe. Users can check into trains, trams, buses, and other transportation modes, track trips, follow friends, and optionally share their journeys to Mastodon.

**Tech Stack**: Laravel 12 (PHP 8.4), Vue 3, Vite, TailwindCSS/DaisyUI
**License**: AGPL-3.0-only (changes must be published)

## Core Development Principles

**CRITICAL**: These principles MUST be followed for all new code. Even if legacy code doesn't follow these patterns, all new development must adhere to them.

### 1. API-First Development

**All features must be developed API-first.** The frontend is a consumer of the API, not the primary interface.

- Create API endpoints in `app/Http/Controllers/API/v1/` before building frontend components
- Design API responses to be frontend-agnostic (usable by web, mobile, third-party apps)
- Document all endpoints with Swagger annotations
- Test API endpoints independently of the frontend

### 2. Modern Frontend: Vue 3 Only

**All new frontend components MUST be written in Vue 3 with Composition API.**

- Use `<script setup>` syntax for new components
- Leverage Pinia stores for state management
- Use TypeScript types from `resources/types/Api.gen.ts`
- No new jQuery, legacy Vue 2, or vanilla JS for components
- Use TailwindCSS utility classes for styling, avoid custom CSS when possible
- New components should be placed in `resources/tailwind-app/` 

### 3. Strict Layered Architecture

**ALWAYS maintain proper separation of concerns.** Follow this layered architecture religiously:

```
Request → Controller → Form Request (validation) → Policy (authorization)
  → Service (business logic) → Repository (data access) → Model (ORM)
  → DTO (data transfer) → API Resource (response formatting) → Response
```

**Layer Responsibilities**:
- **Controllers**: Route requests, orchestrate flow, return responses (thin layer)
- **Form Requests**: Validate input data
- **Policies**: Authorization logic (who can do what)
- **Services**: Business logic, orchestration of repositories, external API calls
- **Repositories**: Data access, complex queries, database abstraction
- **Models**: Eloquent ORM, relationships, basic accessors/mutators
- **DTOs**: Type-safe data structures for passing data between layers
- **API Resources**: Response formatting and serialization

**Never**:
- Put business logic in controllers
- Put database queries in controllers
- Put authorization checks outside of policies
- Skip the service layer for non-trivial operations

### 4. Event-Driven & Asynchronous Architecture

**Prefer event-driven and queue-based patterns** when operations are:
- Time-consuming (API calls, file processing, calculations)
- Independent of the request-response cycle
- Side effects of main operations
- Notifying external systems

**Use Cases**:
- ✅ Posting to Mastodon after check-in → Queue job
- ✅ Calculating polylines for trips → Queue job
- ✅ Sending notifications → Event listener → Queue job
- ✅ Triggering webhooks → Event listener → Queue job
- ✅ Generating exports → Queue job

**Implementation**:
- Dispatch domain events from services/models (e.g., `UserCheckedIn`)
- Create event listeners that dispatch queue jobs
- Jobs should be idempotent and handle failures gracefully

### 5. Legacy Naming: Avoid "Train" and "HAFAS"

**CRITICAL**: The codebase has legacy naming that is being phased out.

**Legacy Names to Avoid**:
- ❌ `train_*` (database tables) - contains ALL transit types, not just trains
- ❌ `TrainController`, `TrainService` - should be `TransportController`, `TransportService`
- ❌ `HAFAS` in generic contexts - HAFAS is just one data provider among many
- ❌ Hardcoding "train" or "railway" in user-facing text

**Modern Naming**:
- ✅ Use generic terms: `transport`, `transit`, `journey`, `trip`, `checkin`
- ✅ Be data-provider agnostic: support HAFAS, Transitous, MOTIS equally
- ✅ Support all transit types: train, tram, bus, subway, ferry, etc.

**When Working with Legacy Code**:
- You may need to use `train_checkins` table or `TrainStopover` model (they exist)
- Do NOT perpetuate this naming in new code
- Add comments explaining the misnomer if necessary
- If refactoring, consider renaming to generic terms

**Example**:
```php
// ❌ Bad: Perpetuates legacy naming
public function getTrainDetails() { ... }

// ✅ Good: Generic and accurate
public function getTransitDetails() { ... }
public function getTripDetails() { ... }
```

### 6. Test Everything

**Write tests for all new functionality.** Testing is not optional.

**Test Types**:
- **Unit Tests** (`tests/Unit/`): Test individual classes, methods, and business logic in isolation
  - Services, repositories, DTOs, helpers, utilities
  - Mock dependencies, focus on single responsibility
  - Fast execution, no database required

- **Feature Tests** (`tests/Feature/`): Test complete workflows and API endpoints
  - API request/response cycles
  - Authentication and authorization
  - Database interactions and transactions
  - Integration between multiple components

**Testing Requirements**:
- ✅ All new API endpoints must have feature tests
- ✅ All new services must have unit tests
- ✅ All new repositories must have tests (unit or feature)
- ✅ Complex business logic must have unit tests
- ✅ Privacy and authorization logic must be tested thoroughly
- ✅ Test both success and failure scenarios
- ✅ Test edge cases and validation

**Example Test Structure**:
```php
// Feature Test for API endpoint
public function test_user_can_create_checkin_with_valid_data() { ... }
public function test_user_cannot_checkin_without_authentication() { ... }
public function test_checkin_respects_privacy_settings() { ... }

// Unit Test for Service
public function test_calculate_trip_distance_returns_correct_value() { ... }
public function test_service_throws_exception_for_invalid_station() { ... }
```

**Run Tests Regularly**:
- Before committing: `composer test` or `php artisan test`
- Use `--filter` for specific tests during development
- All tests must pass before creating a pull request

## Common Commands

### Development

```bash
# Start full development environment (server + queue + vite)
composer dev

# Individual services
php artisan serve                    # Laravel dev server (port 8000)
php artisan queue:listen --tries=1   # Queue worker for async jobs
npm run dev                          # Vite dev server with HMR

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Fresh database with seed data
php artisan tinker                   # Interactive console
```

### Testing

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/APIv1/StatusTest.php

# Run with coverage
php artisan test --coverage

# Parallel testing (faster)
php artisan test --parallel
```

### Code Quality

```bash
# Laravel IDE helper generation
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta

# Swagger API documentation generation
php artisan l5-swagger:generate

# Generate TypeScript types from Swagger
npm run api
```

### Frontend

```bash
npm run dev     # Development mode with hot reload
npm run build   # Production build
```

### Queue & Background Jobs

```bash
# Process background jobs (webhooks, emails, polyline calculations)
php artisan queue:work --queue=default,webhook,export

# Monitor failed jobs
php artisan queue:failed
php artisan queue:retry {id}
```

## High-Level Architecture

### Backend Structure

**Laravel Application** with clean architecture:

- **Models** (`app/Models/`): Core entities (User, Status, Checkin, Trip, Station, Stopover)
- **Controllers** (`app/Http/Controllers/`):
  - `API/v1/`: RESTful API endpoints (26+ controllers)
  - `Frontend/`: Web interface controllers
  - `Backend/`: Admin functionality
- **Services** (`app/Services/`): Business logic layer (OperatorService, StationService, OpenRailRoutingService)
- **Repositories** (`app/Repositories/`): Data access abstraction (StationRepository, TripRepository)
- **DTOs** (`app/Dto/`): Type-safe data transfer objects
- **Enums** (`app/Enum/`): Type-safe enumerations (Business, StatusVisibility, HafasTravelType)
- **Resources** (`app/Http/Resources/`): API response formatters (34+ resources)
- **Policies** (`app/Policies/`): Authorization logic
- **Jobs** (`app/Jobs/`): Async processing (PostStatusOnMastodon, RefreshPolyline, SendVerificationEmail)
- **Events/Listeners** (`app/Events/`, `app/Listeners/`): Event-driven architecture

**Key Architectural Patterns**:
- Repository Pattern for data access
- Service Layer for business logic
- DTO Pattern for type-safe data transfer
- Policy Pattern for authorization
- Event-Driven Architecture with listeners
- Observer Pattern for model lifecycle hooks

### Core Data Model

1. **User**: Authentication with Passport OAuth, social features (follow/block/mute), privacy settings
2. **Status**: User's journey post (public/friends/private visibility)
3. **Checkin**: Concrete journey check-in record with origin/destination/trip
4. **Trip**: Journey metadata from transit APIs (HAFAS/Transitous/MOTIS) with operator, line, category
5. **Station**: Transit stations with IBNR/RIL/IFOPT identifiers, geo coordinates, multilingual names
6. **Stopover**: Individual stop in a trip with planned/real times and delays
7. **Event**: Travel events for tagging journeys
8. **Operator**: Transit company data linked to Wikidata

**Important Note**: Tables are prefixed `train_*` (legacy naming) but contain all transit types, not just trains.

### Privacy Architecture

Multi-level visibility system:
- **Status Visibility**: PRIVATE (only user), FRIENDS (followers only), PUBLIC (everyone)
- **Private Profiles**: Content only visible to approved followers
- **Block/Mute**: User-level content filtering
- **Friend Requests**: Approval-based following system
- **GDPR Compliance**: Personal data export via Spatie package

### External Integrations

- **Transit APIs**: HAFAS (Deutsche Bahn), Transitous, MOTIS for real-time data
- **OpenRailRouting (BRouter)**: Route matching and polyline generation
- **Mastodon**: OAuth login and federation (post journeys to Mastodon)
- **Wikidata**: Operator and station information enrichment
- **Spatie Packages**: Activity logging, permissions, personal data export, webhooks, sitemap

### Frontend Architecture

**Vue 3 + Vite** with modern tooling:

- **Components** (`resources/vue/components/`):
  - `Checkin/`: Check-in form and journey display
  - `Map/`: MapLibre integration for live tracking
  - `Status/`: Status cards and interactions
  - `Settings/`: Profile, privacy, webhook management
  - `Stats/`: Statistics and charts
- **Stores** (`resources/vue/stores/`): Pinia state management (user, activeCheckin, notifications)
- **Views** (`resources/vue/views/`): Full-page components (Dashboard, Profile, ActiveJourneys)
- **API** (`resources/js/api/`): Axios-based HTTP client with TypeScript types
- **Styling**: TailwindCSS v4 + DaisyUI v5 + Bootstrap 5
- **Maps**: MapLibre GL (replacing Leaflet)
- **Charts**: ApexCharts + Chart.js
- **i18n**: Multi-language support via laravel-vue-i18n

### Data Flow Example (Check-in)

```
Vue Component → API Endpoint (TransportController)
  → Service Layer (StationService, TripRepository)
  → Models (Trip, Checkin, Status)
  → Event (UserCheckedIn)
  → Listeners (webhooks, polyline calculation)
  → Background Jobs (Mastodon posting, route calculation)
  → Response (CheckinSuccessResource)
```

### Testing Architecture

- **Feature Tests** (`tests/Feature/`): API endpoints, integration tests, privacy logic
- **Unit Tests** (`tests/Unit/`): Model logic, services, helpers
- **Base Classes**: `FeatureTestCase`, `ApiTestCase` with fixtures and helpers
- **Database**: Seeded and reset between tests
- **PHPUnit 12** with Laravel testing utilities

## Important Conventions

### Backend

- **Route Naming**: Use Laravel resource conventions (`status.index`, `user.follow`)
- **API Versioning**: All API routes under `/api/v1/`
- **Validation**: Use Form Request classes (`app/Http/Requests/`)
- **Authorization**: Use Policy classes, not inline gate checks
- **Queue Jobs**: Jobs should implement `ShouldQueue` and handle failures gracefully
- **Timestamps**: Use UTC datetime with custom `UTCDateTime` cast
- **Activity Logging**: Models use `LogsActivity` trait (Spatie) for audit trails

### Frontend

- **Component Naming**: PascalCase (e.g., `CheckinForm.vue`)
- **State Management**: Use Pinia stores for shared state
- **API Calls**: Use centralized API module, not inline Axios
- **Styling**: Prefer TailwindCSS utility classes over custom CSS
- **i18n**: Use `$t()` helper for all user-facing text
- **Error Handling**: Use Notyf for toast notifications

### Database

- **Migrations**: Include `down()` method for rollback
- **Foreign Keys**: Use `constrained()` and `cascadeOnDelete()` where appropriate
- **Indexes**: Add indexes for frequently queried columns
- **Timestamps**: Use `timestamps()` on all tables
- **Soft Deletes**: Use `softDeletes()` for user-facing data

### Git Commits

- Use gitmoji for commit messages (see project README badge)
- Keep pull requests small and focused
- Include tests for new features

## Key Files to Reference

### Configuration

- `.env.example`: Environment variables template
- `config/auth.php`: Passport + web guards configuration
- `config/permission.php`: Spatie permission roles
- `config/services.php`: External service credentials (Mastodon, etc.)
- `routes/api.php`: API routes with scope-based permissions
- `routes/web.php`: Web interface routes

### Documentation

- `docs/contributing/`: Contribution guidelines
- `docs/hosting/`: Self-hosting instructions
- `storage/api-docs/api-docs.json`: Swagger API documentation

### Testing

- `tests/FeatureTestCase.php`: Base class with fixtures
- `tests/ApiTestCase.php`: API-specific test utilities
- `phpunit.xml`: PHPUnit configuration

## Common Development Tasks

### Adding a New API Endpoint

1. Create route in `routes/api.php` with appropriate middleware and scopes
2. Create/update controller in `app/Http/Controllers/API/v1/`
3. Create Form Request for validation in `app/Http/Requests/`
4. Create Policy for authorization (if needed)
5. Create/update API Resource for response formatting
6. Add feature tests in `tests/Feature/APIv1/`
7. Update Swagger documentation annotations
8. Run `php artisan l5-swagger:generate`

### Adding a New Vue Component

1. Create component in `resources/vue/components/`
2. Import and register in parent component or view
3. Use TypeScript types from `resources/types/Api.gen.ts`
4. Add translations to `lang/{locale}.json` if needed
5. Use Pinia stores for shared state
6. Test in browser with HMR (`npm run dev`)

### Adding a Background Job

1. Create job class in `app/Jobs/`
2. Implement `ShouldQueue` interface
3. Dispatch job using `dispatch()` or listeners
4. Test locally with `php artisan queue:listen`
5. Add feature test to verify job behavior

### Running Database Migrations

1. Create migration: `php artisan make:migration create_xyz_table`
2. Write `up()` and `down()` methods
3. Run: `php artisan migrate`
4. Update model relationships if needed
5. Update factories/seeders for testing

## Background Services

The application requires these processes to run:

1. **Queue Worker**: Handles async jobs (webhooks, emails, polyline calculations)
   ```bash
   php artisan queue:work --queue=default,webhook,export
   ```

2. **Scheduler** (via cron): Runs scheduled tasks every minute
   ```bash
   * * * * * php artisan schedule:run
   ```

## Security Notes

- **AGPL-3.0 License**: All modifications must be published
- **OAuth Scopes**: API uses Passport scopes for fine-grained permissions
- **Privacy Policy**: Enforced via middleware on all authenticated routes
- **Data Protection**: GDPR export functionality via Spatie package
- **Activity Logging**: Critical operations are logged for audit trails

## Performance Considerations

- **Eager Loading**: Use `with()` in API resources to avoid N+1 queries
- **Caching**: Polylines and station data are cached to reduce API calls
- **Async Jobs**: Heavy operations (Mastodon posting, polyline generation) run in queue
- **Pagination**: Use `paginate()` for large datasets
- **Frontend**: Pinia state persistence reduces redundant API calls

## External Service Dependencies

- Transitous API: Transit data
- OpenRailRouting (BRouter): Route polylines
- Mastodon: OAuth and federation
- Wikidata: Station/operator enrichment
- Redis: Queue backend and caching (optional but recommended)
