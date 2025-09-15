
# Grader’s Checklist — Quotes Challenge

## Repo & Hygiene (0–5)
- [ ] Progressive commits show meaningful steps (no single giant dump)
- [ ] `.env.example` configured for SQLite + database cache; no secrets committed
- [ ] CI green: tests + Larastan + container build

## Core Features (Correctness)
- [ ] `/today`: uses ZenQuotes `/api/today`; TTL 30s; `[cached]` prefix; local image; `?new=1`
- [ ] `/quotes`: guests see 5; authenticated see 10; shared cached batch; `?new=1`; **banner shows client mode**
- [ ] Favorites (web): list, add (idempotent), delete; auth only; helpful empty state
- [ ] Favorites (API): GET/POST/DELETE; auth only; delete supports `unique_hash` or `text`+`author`
- [ ] API `/api/quotes`: `count` default 5; `?new=1` refresh; `count>5` requires auth → 401 JSON `{ "error": "Unauthenticated" }`; `meta.client` present with default `"real"`

## Caching & Resilience (0–5)
- [ ] Database cache (SQLite) used; TTL 30s; documented keys (`qod.current`, `quotes.batch`)
- [ ] Refresh semantics correct across web/API; cache sharing verified
- [ ] Handles 429/network failure gracefully (message/logging/backoff) *(stretch)*

## Automated Tests (0–5)
- [ ] Feature tests: cache behavior, redirects/authz, API 401 path
- [ ] Unit tests: API client normalization; favorites idempotency
- [ ] Flawed module fixed; unit tests passing
- [ ] Tests isolate DB (`RefreshDatabase`/`DatabaseMigrations`)

## Security & Validation (0–5)
- [ ] Sanctum Bearer tokens for API; 401 on unauthorized where required
- [ ] CSRF protection on forms; guarded/validated inputs
- [ ] Delete only own favorites (policy/gate if implemented)

## Documentation & DX (0–5)
- [ ] README is clear (local & container run, test, static analysis)
- [ ] ADRs completed (clear decisions & trade‑offs)
- [ ] Self‑Review completed; time‑spent documented
- [ ] Postman collection updated (base_url, token)

## Overall
- Strengths: ________________________________________
- Areas to improve: _________________________________
- Decision: ☐ Advance ☐ Hold ☐ No‑hire
