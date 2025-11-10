# ADR 0002: API Boundary, Contracts & Data Transfer Objects

- **Status:** Accepted
- **Date:** 2025-01-10

## Context

The Quotes Collector application needs to integrate with external quote APIs while maintaining clean architecture and testability. The application must support multiple API clients (stub for testing, real for production) and normalize different response formats into a consistent internal representation.

Key architectural challenges:
1. **Multiple clients:** Need to support both stub (local fixtures) and real API clients without duplicating code
2. **Format differences:** Different APIs return quotes in different formats (ZenQuotes uses `q`/`a` keys, API Ninjas uses `text`/`author`)
3. **Testability:** Feature tests must run without network calls
4. **Flexibility:** Should be easy to swap API providers or add new ones
5. **Type safety:** Need strong typing throughout the application

## Decision

We have implemented a **service layer with contracts and DTOs** using the following components:

**QuoteApiClient Contract** (`app/Contracts/QuoteApiClient.php`)
- Defines interface for quote API clients with two methods:
  - `fetchQuoteOfTheDay(): QuoteDTO`
  - `fetchRandomQuotes(int $count = 10): array` (array of QuoteDTO)
- Enforces consistent API across all implementations
- Enables dependency injection in controllers

**Concrete Implementations:**
1. **NinjaQuotesClient** (`app/Services/NinjaQuotesClient.php:10`) - Production client using API Ninjas
   - Handles HTTP requests with retry logic
   - Normalizes API Ninjas format (`quote`/`author`) to internal format (`text`/`author`)
   - Manages caching with 30s TTL

2. **ZenQuotesClientStub** (`app/Services/Stubs/ZenQuotesClientStub.php`) - Test client using local fixtures
   - Reads from JSON files in `database/fixtures/`
   - No network calls - fast and deterministic tests
   - Returns data in ZenQuotes format (`q`/`a` keys)

**QuoteDTO** (`app/DTOs/QuoteDTO.php:5`)
- Immutable data transfer object with public readonly properties
- Three properties: `text`, `author`, `cached` (boolean flag)
- Static factory method `fromZenQuotes()` normalizes multiple formats
- Supports both `q`/`a` (ZenQuotes) and `text`/`author` (API Ninjas) keys
- `toArray()` method adds `[cached]` prefix when data from cache
- No business logic - pure data structure

**Service Binding** (`app/Providers/AppServiceProvider.php:14-19`)
- Binds `QuoteApiClient` interface to concrete implementation based on `config('quotes.client')`
- `'real'` → NinjaQuotesClient
- `'stub'` (or any other value) → ZenQuotesClientStub
- Uses Laravel's service container for dependency injection
- Configuration driven via `QUOTES_CLIENT` environment variable

**Controller Injection:**
- Controllers declare dependency on `QuoteApiClient` interface, not concrete classes
- `QuotesController.php:10`, `TodayController.php:10`, `QuotesApiController.php:10`
- Laravel automatically resolves to correct implementation at runtime
- Easy to test by mocking the interface

## Consequences

**Positive:**
- **Clean separation:** Business logic (controllers) decoupled from API implementation details
- **Testable:** Feature tests use stub client without network calls (`tests/Feature/*.php` set `quotes.client=stub`)
- **Type safe:** PHP strict types ensure compile-time checking
- **Flexible:** Can add new API providers by implementing `QuoteApiClient` interface
- **Consistent data:** All code works with `QuoteDTO` regardless of source API
- **DRY principle:** Format normalization logic centralized in `QuoteDTO::fromZenQuotes()`
- **Easy configuration:** Switch between stub/real with single environment variable

**Negative:**
- **Additional layer:** Extra abstraction adds slight complexity
- **DTO creation overhead:** Every API response creates new DTO objects (negligible performance impact)
- **Contract maintenance:** If API needs change, must update interface and all implementations
- **Naming confusion:** `fromZenQuotes()` method name is misleading since we use API Ninjas (historical artifact)

**Neutral:**
- DTOs are simple and have no business logic - they're just data containers
- Factory method pattern in DTO allows future format support without breaking existing code
- Stub client could be further optimized but sufficient for current test needs

## Alternatives Considered

1. **No abstraction - direct API calls in controllers**
   - **Pros:** Simpler, fewer files, less code
   - **Cons:** Controllers tightly coupled to API, impossible to test without mocking HTTP, hard to switch APIs
   - **Why Rejected:** Violates SOLID principles, not testable, not maintainable

2. **Repository Pattern instead of Service Layer**
   - **Pros:** More familiar to some developers, includes persistence logic
   - **Cons:** Overkill for simple API client, quotes aren't really "repositories"
   - **Why Rejected:** Service layer more appropriate for API integration without local persistence

3. **Arrays instead of DTOs**
   - **Pros:** Less code, no class definitions needed
   - **Cons:** No type safety, easy to make mistakes with key names, no IDE autocomplete
   - **Why Rejected:** PHP 8.3 typed properties + DTOs provide much better DX and safety

4. **Single client with strategy pattern**
   - **Pros:** Single class, uses composition
   - **Cons:** More complex than interface + implementations, harder to test
   - **Why Rejected:** Multiple implementations with shared interface is clearer and more Laravel-idiomatic

5. **API wrapper package (e.g., Guzzle facades)**
   - **Pros:** Less boilerplate for HTTP calls
   - **Cons:** Additional dependency, less control, harder to customize caching
   - **Why Rejected:** Simple `file_get_contents()` with retry logic sufficient for this use case

## How to Verify

- **Interface implementation:** Check `NinjaQuotesClient implements QuoteApiClient` (`NinjaQuotesClient.php:10`)
- **Stub works:** Run `php artisan test` - all tests use stub and pass without network
- **Real client works:** Set `QUOTES_CLIENT=real`, visit `/quotes` - should see API Ninjas quotes
- **Stub client works:** Set `QUOTES_CLIENT=stub`, visit `/quotes` - should see fixture data
- **DTO normalization:** Test `QuoteDTOTest.php` validates both formats work
- **Binding:** Check `AppServiceProvider` returns correct client based on config
- **Type safety:** Run `vendor/bin/phpstan analyse` - should pass with no type errors

## Code Anchors

- Contract definition: `laravel-app/app/Contracts/QuoteApiClient.php:8-12`
- Real client: `laravel-app/app/Services/NinjaQuotesClient.php:10`
- Stub client: `laravel-app/app/Services/Stubs/ZenQuotesClientStub.php`
- DTO class: `laravel-app/app/DTOs/QuoteDTO.php:5-28`
- DTO factory method: `laravel-app/app/DTOs/QuoteDTO.php:13-18`
- Service binding: `laravel-app/app/Providers/AppServiceProvider.php:14-19`
- Controller injection: `laravel-app/app/Http/Controllers/QuotesController.php:10`
- Test configuration: `tests/Feature/QuotesPageTest.php:16` (sets stub mode)
- DTO tests: `laravel-app/tests/Unit/QuoteDTOTest.php`
- Fixtures: `laravel-app/database/fixtures/quotes.json`, `qod.json`
