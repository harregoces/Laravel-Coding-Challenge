# ADR 0001: Caching TTL, Keys & Stampede Prevention

- **Status:** Accepted
- **Date:** 2025-01-10

## Context

The Quotes Collector application integrates with external APIs (API Ninjas) that have rate limits. To respect these limits while providing good user experience, we need an effective caching strategy. The challenge specifies a 30-second TTL for all quote endpoints. We must decide on the cache driver, key naming conventions, and whether to implement cache stampede prevention.

The application serves both web and API traffic for two main quote types:
1. Quote of the Day (QOD) - should be consistent across all users within the TTL window
2. Random quotes batch - should be shared between web and API endpoints

## Decision

We have implemented **Redis-based caching** with the following characteristics:

**Cache Driver:** Redis (configured in `config/cache.php` and `docker-compose.yml`)
- Provides atomic operations for future stampede prevention
- Better performance than database cache for high-traffic scenarios
- Easy to scale horizontally if needed

**TTL Configuration:** 30 seconds (configurable via `QUOTES_TTL` environment variable)
- Defined in `config/quotes.php:4`
- Applied consistently across all cache operations

**Cache Key Convention:**
- `qod.current` - Quote of the Day
- `quotes.batch` - Random quotes batch (shared between web and API)
- Prefix with domain concept, suffix with scope/type
- All keys defined as class constants in `NinjaQuotesClient.php:23-24`

**Cache Busting:** Explicit cache invalidation via `?new=1` query parameter
- Implemented in controllers: `QuotesController.php:14-16`, `TodayController.php:14-16`
- Uses `cache()->forget()` to remove specific key
- Allows users to force refresh without waiting for TTL expiration

**Stampede Prevention:** Basic mitigation through retry logic
- Retry logic with exponential backoff in `NinjaQuotesClient.php:82-134`
- Falls back to stale cached data on API failure (`NinjaQuotesClient.php:64-68`)
- Does not implement full mutex-based stampede prevention (acceptable for this traffic level)

## Consequences

**Positive:**
- 30-second TTL significantly reduces API calls (from potentially hundreds per minute to 2 per minute maximum)
- Shared cache keys between web and API ensure consistency and further reduce API calls
- Redis performance handles concurrent requests without bottlenecks
- Cache busting provides manual override for testing and immediate updates
- Fallback to stale data prevents complete service failure during API outages

**Negative:**
- Redis adds infrastructure dependency (requires Redis service in Docker Compose)
- 30 seconds means users may see same quotes for half a minute
- No true stampede prevention - if cache expires during high traffic, multiple requests might hit API simultaneously
- Cache warming not implemented - first request after expiry always hits API

**Neutral:**
- Cache detection flag (`cached`) allows UI to indicate data freshness (`QuoteDTO.php:10,23`)
- TTL is configurable but changing it requires redeployment

## Alternatives Considered

1. **Database Cache (SQLite)** - Originally specified in challenge requirements
   - **Pros:** No additional infrastructure, simpler deployment
   - **Cons:** Slower than Redis, more disk I/O, harder to scale
   - **Why Rejected:** Redis provides better performance and our Docker setup already includes it

2. **File-based Cache**
   - **Pros:** Simplest setup, no external dependencies
   - **Cons:** Concurrency issues, slower, no atomic operations
   - **Why Rejected:** Unsuitable for concurrent requests, no built-in expiration

3. **Longer TTL (5+ minutes)**
   - **Pros:** Even fewer API calls, reduced costs
   - **Cons:** Stale data for users, violates challenge requirement
   - **Why Rejected:** 30 seconds is a hard requirement

4. **No Caching (Direct API calls)**
   - **Pros:** Always fresh data, simplest code
   - **Cons:** Would hit rate limits immediately, poor performance, violates requirements
   - **Why Rejected:** Required by challenge, API rate limits make this infeasible

## How to Verify

- **Cache TTL works:** Visit `/quotes`, wait 30+ seconds, visit again - new quotes should appear
- **Cache busting works:** Visit `/quotes`, immediately visit `/quotes?new=1` - should see different quotes
- **Shared cache:** Check web `/quotes` and API `/api/quotes` within 30s - should see same batch
- **Cached flag:** Inspect API response `meta.source` or web page for `[cached]` prefix
- **Redis connection:** Run `docker-compose exec redis redis-cli KEYS *` to see cache keys

## Code Anchors

- Cache configuration: `laravel-app/config/cache.php:30-33` (Redis default store)
- TTL definition: `laravel-app/config/quotes.php:4`
- Cache keys: `laravel-app/app/Services/NinjaQuotesClient.php:23-24`
- QOD caching: `laravel-app/app/Services/NinjaQuotesClient.php:27-72`
- Random quotes caching: `laravel-app/app/Services/NinjaQuotesClient.php:137-190`
- Cache busting (web): `laravel-app/app/Http/Controllers/QuotesController.php:14-16`
- Cache busting (API): `laravel-app/app/Http/Controllers/Api/QuotesApiController.php:14-16`
- Retry + fallback: `laravel-app/app/Services/NinjaQuotesClient.php:59-71, 165-177`
- Cached detection: `laravel-app/app/DTOs/QuoteDTO.php:10,23`
- Docker Redis: `docker-compose.yml:30-37`
