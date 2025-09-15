
# Self‑Review Checklist (mirror of grader sheet)

## Repo & Hygiene
- [ ] Progressive commits show meaningful steps (no giant dump)
- [ ] `.env.example` set for SQLite + database cache; no secrets
- [ ] CI green: tests + Larastan + container build

## Core Features
- [ ] `/today`: ZenQuotes `/api/today`; TTL 30s; `[cached]`; local image; `?new=1`
- [ ] `/quotes`: guests=5, authed=10; cached batch; `?new=1`; **client mode banner**
- [ ] Favorites (web): list, add (idempotent), delete; auth only; empty state shown
- [ ] Favorites (API): GET/POST/DELETE; auth only; delete via `unique_hash` or `text`+`author`
- [ ] `/api/quotes`: default `count=5`; supports `?new=1`; `count>5` requires auth → 401 JSON; `meta.client` defaults to `real`

## Caching & Resilience
- [ ] Database cache (SQLite) used; TTL 30s; keys documented
- [ ] Cache is shared across web + API; refresh works
- [ ] Rate‑limit handling (message/log/backoff) *(stretch)*

## Automated Tests
- [ ] Feature tests for cache behavior, redirects/authz, API 401
- [ ] Unit tests: API client normalization; favorites idempotency
- [ ] Flawed module fixed; tests passing
- [ ] DB isolation via `RefreshDatabase`/`DatabaseMigrations`

## Security & Validation
- [ ] Sanctum Bearer tokens for API; 401 on unauthorized
- [ ] CSRF protection; guarded/validated inputs
- [ ] Delete only own favorites *(policy/gate if implemented)*

## Documentation & DX
- [ ] README (local & container run, test, static analysis)
- [ ] ADRs completed (concise & thoughtful)
- [ ] Postman collection updated (base_url, token)
- [ ] Time spent noted
