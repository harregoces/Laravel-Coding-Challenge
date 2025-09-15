# ADR 0003: External API Resilience (Retries, Backoff, Fallbacks)

- **Status:** Proposed
- **Date:** <YYYY-MM-DD>

## Context
We call ZenQuotes’ free endpoints under a 5 req/30 s/IP limit. Transient failures (429, timeouts, DNS) can occur. We must keep UX predictable, avoid stampedes, and not exceed rate limits. The app uses the database cache (TTL 30 s) and exposes `?new=1` to refresh.

## Decision
Use the Laravel HTTP client with **bounded retries** and **exponential backoff with jitter** for idempotent GETs:
- On **429** and network timeouts, retry up to N times with backoff (e.g., 100ms, 200ms, 400ms ± jitter).
- If retries exhaust or API is unavailable, **serve the last cached batch** (if present). If no cache exists, show a friendly message and log at `warning`.
- Keep the **single refresh locus**: `quotes.batch` and `qod.current`. Only the first request after `?new=1` fetches; others read cache.
- (Optional) Guard refresh with a simple **cache lock** to avoid a thundering herd.

## Consequences
**Pros:** Better UX under transient errors; fewer hard failures; predictable rate‑limit behavior.  
**Cons:** Slight code complexity; small latency increase on backoff; must test retry branches.

## Alternatives Considered
1) **No retries** — faster failures, but poorer UX under transient issues.  
2) **Circuit breaker** — robust but heavier than needed for this scope.  
3) **Fail open to fixtures** — simple demo mode but risks inconsistent prod behavior.

## How to Verify
- Unit: simulate 429/timeouts via `Http::fake()` and assert backoff + fallback to cache.  
- Feature: `/api/quotes?new=1` returns cached data when upstream fails; logs contain a single warning.  

## Code anchors
-  `App/Services/ZenQuotesClient`, retry policy; log context (request path, status).