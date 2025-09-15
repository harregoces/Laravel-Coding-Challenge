# ADR 0001: Caching TTL, Keys & Stampede Prevention

- **Status:** Proposed
- **Date:** 2025-09-15

## Context
The app integrates ZenQuotes free API with a free‑tier rate limit (typically 5 requests / 30s / IP). We must keep the UX fast, respect the limit, and share cache across **web**, **API**, and **console** (if implemented). Storage is **SQLite**; cache driver is **database**. TTL is **30 seconds** by requirement. Keys suggested: `qod.current` and `quotes.batch`. The `?new=1` query param forces a refresh.

## Decision
Use Laravel's **database** cache driver to store **normalized** quote data (DTOs) for **30s**. Share the following keys across layers:
- `qod.current` for `/today`
- `quotes.batch` for `/quotes` and `/api/quotes`

On `?new=1`, explicitly **forget** the relevant key then fetch → cache → return.

Optionally (stretch), guard against cache stampede using a simple lock around refresh or an "early refresh" pattern.

## Consequences
- **Pros:** Minimal API calls, adheres to rate limits, consistent results across surfaces, simple invalidation via `?new=1`.
- **Cons:** Data can be stale for up to 30s; requires consistent key use and test coverage.

## Alternatives Considered
1. **No caching** — simpler code but unreliable under rate limits and slower UX.
2. **File cache** — similar semantics, but DB cache centralizes storage and is easier to ship cross‑platform.
3. **Per‑request fetch** — avoids staleness but breaks rate limits and harms latency.

## How to Verify
- Feature tests assert `[cached]` appears on second request within TTL.
- `?new=1` results in a response **without** `[cached]` followed by cached responses.
- API and web endpoints observe the same cache.
