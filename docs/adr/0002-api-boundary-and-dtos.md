# ADR 0002: External API Boundary & DTO Normalization

- **Status:** Proposed
- **Date:** 2025-09-15

## Context
We consume ZenQuotes free endpoints. Their response fields (`q`, `a`) and shapes may vary and can include fields we don't need. We also must support **stub** and **real** clients. Tests should not depend on raw payload shapes.

## Decision
Define a **QuoteApiClient** interface and a **QuoteDTO** that the rest of the app uses. Provide two implementations:
- **Real client** hitting ZenQuotes free endpoints.
- **Stub client** backed by local fixtures.

Normalize to `QuoteDTO(text, author, cached)` and keep API+web rendering logic independent from vendor payloads. Include `"cached"` and a `[cached]` prefix for human‑readable output.

## Consequences
- **Pros:** Stable internal contract; easy mocking in tests; simple switch between stub/real via config/env.
- **Cons:** Slight up‑front cost to define DTO and interface; must maintain mapping code.

## Alternatives Considered
1. Use the HTTP client directly in controllers — quick but hard to test and refactor; leaks vendor shapes.
2. Store raw JSON and decode inline — fragile and couples storage to vendor payloads.

## How to Verify
- Unit tests target the client and the DTO mapping.
- Feature tests remain stable if the underlying client changes or is stubbed.
- Toggling `QUOTES_CLIENT=stub|real` switches sources without code changes.
