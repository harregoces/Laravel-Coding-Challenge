# ADR 0003: External API Resilience (Retries, Backoff, Fallbacks)

- **Status:** Accepted
- **Date:** 2025-01-10

## Context

The Quotes Collector application depends on an external API (API Ninjas) which can experience transient failures (network timeouts, rate limiting with 429 responses, temporary outages). Without proper error handling, these failures would result in application errors and poor user experience.

Key resilience challenges:
1. **Network timeouts:** API may be slow or unreachable
2. **Rate limiting:** API Ninjas may return 429 Too Many Requests
3. **Transient errors:** Temporary issues that resolve with retry
4. **Complete outages:** API unavailable for extended periods
5. **User experience:** Must not show errors for temporary issues

## Decision

We have implemented **multi-layered resilience** with retry logic, exponential backoff, and fallback to stale cached data:

**Retry Logic with Exponential Backoff** (`NinjaQuotesClient.php:82-134`)
- Maximum 3 retry attempts for each API request
- Base delay of 1 second, doubling on each retry (1s, 2s, 4s)
- Catches all exceptions during HTTP requests
- Uses `sleep()` for backoff delays
- Re-throws exception after max retries exhausted

**Request Timeout** (`NinjaQuotesClient.php:103`)
- 10-second timeout configured in stream context
- Prevents indefinite hanging on slow API responses
- Balances between allowing time for response and failing fast

**Stale Cache Fallback:**
- Quote of the Day: Falls back to stale cache if API fails (`NinjaQuotesClient.php:64-68`)
- Random quotes: Falls back to stale cache if API fails (`NinjaQuotesClient.php:169-173`)
- Logs warning but returns cached data instead of throwing exception
- Only throws if both API and cache fail

**Error Logging:**
- All API failures logged with `\Log::warning()` (`NinjaQuotesClient.php:59, 165`)
- Includes error message in log context
- Allows monitoring of API health without exposing errors to users

**Error Context:**
- Uses `@file_get_contents()` to suppress PHP warnings (`NinjaQuotesClient.php:107`)
- Captures last error with `error_get_last()` for logging
- Converts to RuntimeException with descriptive messages

## Consequences

**Pros:**
- **Graceful degradation:** App continues working with stale data during API outages
- **Automatic recovery:** Transient errors (99% of issues) resolve automatically via retries
- **Rate limit handling:** Exponential backoff respects API rate limits
- **User experience:** Users see cached data instead of error pages
- **Monitoring:** Warnings logged allow tracking API health
- **Cost reduction:** Retries avoid unnecessary user-facing errors

**Cons:**
- **Extended response time:** Up to 7 seconds (1+2+4) added latency on complete failure before fallback
- **Stale data:** Users may see outdated quotes during extended outages
- **Resource usage:** Sleeping threads consume server resources during retries
- **No circuit breaker:** Continues attempting API calls even during known outages
- **Limited retry intelligence:** Doesn't distinguish between retriable (429, timeout) and non-retriable (401, 403) errors

## Alternatives Considered

1. **No retries - fail immediately**
   - **Pros:** Fast failure, simple code
   - **Cons:** Poor UX, transient errors become user-facing failures
   - **Why Rejected:** Unacceptable user experience

2. **Circuit breaker pattern** (e.g., with Laravel's circuit breaker)
   - **Pros:** Prevents repeated calls during known outages, faster failure detection
   - **Cons:** Additional complexity, requires state management, may be overkill for this traffic
   - **Why Rejected:** Not required for current scale, can add later if needed

3. **Longer retry delays** (10s, 20s, 30s)
   - **Pros:** More respectful of rate limits
   - **Cons:** Unacceptable user-facing latency (60+ seconds)
   - **Why Rejected:** UX degradation not worth marginal rate limit benefit

4. **Queue-based async retries**
   - **Pros:** Non-blocking, can retry in background
   - **Cons:** Complex to implement, requires job queue, harder to test, doesn't help synchronous requests
   - **Why Rejected:** Overhead not justified for simple quote API

5. **Multiple API providers with automatic failover**
   - **Pros:** Higher availability, no single point of failure
   - **Cons:** Complex to implement, normalize multiple formats, higher costs
   - **Why Rejected:** Single API sufficient for current needs, cache provides adequate fallback

6. **Fixed retry delay** (no exponential backoff)
   - **Pros:** Simpler logic, predictable timing
   - **Cons:** Doesn't respect rate limits, may worsen thundering herd
   - **Why Rejected:** Exponential backoff is industry best practice for retries

## How to Verify

- **Retry logic:** Temporarily set invalid API key, observe 3 retries in logs before fallback
- **Backoff timing:** Add timing logs, verify delays are 1s, 2s, 4s
- **Stale cache fallback:** Disconnect network, visit `/quotes` - should show cached data with warning in logs
- **Timeout works:** Use slow API endpoint simulator, verify request fails after 10s
- **Error logging:** Tail Laravel logs during API failure, verify `warning` level messages with context
- **User experience:** During API failure, users should see cached quotes, not error page

## Code Anchors

- Retry method: `laravel-app/app/Services/NinjaQuotesClient.php:82-134`
- Retry loop: `laravel-app/app/Services/NinjaQuotesClient.php:92-131`
- Exponential backoff: `laravel-app/app/Services/NinjaQuotesClient.php:124`
- Request timeout: `laravel-app/app/Services/NinjaQuotesClient.php:103`
- QOD stale fallback: `laravel-app/app/Services/NinjaQuotesClient.php:64-68`
- Random quotes stale fallback: `laravel-app/app/Services/NinjaQuotesClient.php:169-177`
- Error logging (QOD): `laravel-app/app/Services/NinjaQuotesClient.php:59-61`
- Error logging (random): `laravel-app/app/Services/NinjaQuotesClient.php:165-167`
- Error suppression: `laravel-app/app/Services/NinjaQuotesClient.php:107`
- Max retries config: `laravel-app/app/Services/NinjaQuotesClient.php:89`
