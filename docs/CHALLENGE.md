
# Laravel Coding Challenge — “Quotes Collector” (Candidate Instructions)

This challenge assesses your ability to design clean Laravel code, integrate a 3rd‑party API, model & persist data, implement caching and auth, write robust tests, document your decisions, and ship a container image you can run locally or in CI.

---

## Tech & Tooling

- **Framework:** Laravel 12 (PHP **8.2+**, CI uses **8.3**)
- **Auth:** Laravel Sanctum (Bearer tokens for API)
- **Database:** **SQLite** for app data
- **Caching:** **database** cache driver (SQLite), TTL **30s**
- **Static analysis:** **Larastan (PHPStan)** at **level 7** (core). *(Stretch: level 9) *
- **Container:** `php:8.3-apache-bookworm` (Apache; mod_rewrite enabled)
- **Rate limit & attribution:** Use only **ZenQuotes free** endpoints; show attribution link to **zenquotes.io** anywhere quotes are rendered; respect free tier (typically **5 requests / 30s / IP**).

> The starter repo includes stubs, fixtures, basic views, example tests, CI, and a Containerfile. You’ll bootstrap Laravel 12 in place via a script.

---

## Core Requirements

1) **Quote of the Day — `/today`**  
   - Source: **ZenQuotes free** `/api/today` *(no premium)*  
   - **Cache 30s**; if served from cache, prefix the text with **`[cached]`**  
   - Display a **random image** from `/api/image`
     - Local images for **STUB** client mode are stored in `public/images/inspiration/*` (five images are prefilled)  
   - Support **`?new=1`** to refresh (bust cache → fetch → cache → display)  
   - Accessible to **guests and authenticated users**

2) **Random Quotes  — `/quotes`**  
   **Web**
   - Show **5 quotes** for guests; **10 quotes** for authenticated users  
   - Serve from a cached **batch**; **TTL 30s**; support **`?new=1`** to refresh the batch  
   - If authenticated, each item has an **“Add to favorites”** button (idempotent)  
   - **Banner** displays client mode: **STUB** or **REAL**

   **API**
   - **GET `/api/quotes`**  
   - Supports **`?new=1`** to refresh the cached batch (bust cache → fetch → cache → display)   
   - **Defaults**: `count` **omitted → 5** (regardless of auth)  
   - If **`count > 5`**, **authentication required** — otherwise **401 JSON** `{ "error": "Unauthenticated" }`  
   - Upper bound **10** (even for authenticated)  
   - Response includes `meta.client` (**"stub"** or **"real"**; **defaults to `"real"`** if omitted) and `meta.count` and per‑item `cached` flags

3) **Favorites (CRUD minus Update)**  
   **Web (auth only)**  
   - **GET `/favorites`** — list current user’s favorites (empty‑state message if none)  
   - **POST `/favorites`** — add a favorite *(idempotent)*; body contains `text` and optional `author`  
   - **DELETE `/favorites/{quote}`** — remove from favorites  

   **API (auth only; Sanctum)**  
   - **GET `/api/favorites`**  
   - **POST `/api/favorites`** — JSON `{ "text": "...", "author": "..." }`  
   - **DELETE `/api/favorites`** — JSON `{ "unique_hash": "..." }` **or** `{ "text": "...", "author": "..." }`  

   *Why no Update?* Quotes from external sources don’t have a meaningful partial update for a “favorite.” Create/delete is sufficient; add must be idempotent (no duplicates per user).

4) **Seeds**  
   - Create **3 users**, each with **3 favorites**, using **local JSON fixtures** (no network calls in seeders)

5) **Caching**  
   - Use the **database** cache driver (SQLite). Provide migration for cache table (bootstrapped)  
   - Suggested keys: `qod.current` (QOD), `quotes.batch` (random quotes batch)  
   - Share cache between web and API

6) **Containerized Delivery (Core)**  
   - CI builds an OCI image from the included **Containerfile** (`php:8.3-apache-bookworm`)  
   - Optionally **publish a public image to GHCR**: set repo variables `PUBLISH=true` and `MAKE_PUBLIC=true` (workflow includes a visibility step; you may also set visibility via GitHub UI)

7) **Testing**  
   - Make the provided **acceptance tests** pass (`php artisan test`)  
   - Add at least **2 unit tests** (e.g., API client normalization; favorites idempotency)  
   - **Fix the flawed module**: `app/Flawed/NaiveQuoteCache.php` has a TTL bug; `tests/Unit/FlawedNaiveCacheTest.php` fails until fixed (commit with a short diagnosis note)  
   - Optional micro‑enhancement: add a simple `?author=` filter to `/quotes` that uses **cached** data only and include a test

8) **ADRs (2 short)** — required  
   - `/docs/adr/0001-caching-approach.md` — TTL, cache keys, stampede prevention (if any), trade‑offs  
   - `/docs/adr/0002-api-boundary-and-dtos.md` — service/DTO boundary, normalization choices, and test strategy

9) **Postman collection** — required*  
   - Update `starter-overlay/postman/quotes-collection.json` base URL and token  
   - *If you implement the **Swagger/api‑test** stretch goal, you may mark Postman “optional” in your README*

10) **Documentation**  
   - Keep **README** current (run instructions for Windows/macOS/Linux; container run instructions)  
   - Complete **docs/SELF_REVIEW.md** before submission  
   - If you used AI, document it in **AI_USAGE.md** (what, where, and your review of it)

---

## Stretch Modules (choose any 2)

- Swagger/OpenAPI or `/api-test` page
- Console command: `php artisan Get-FiveRandomQuotes [--new]` sharing the `/quotes` cache
- Rate‑limit resilience: 429 backoff + logging + simple lock to avoid cache stampede
- All‑users report page
  - Policy/Gate: restrict delete to **own** favorites + negative tests
- Larastan level 9 (keep CI green)

---

## What we evaluate

- **Code design & clarity** — structure, naming, self‑documenting code; DTOs & boundaries; validation & error handling
- **Correctness & completeness** — features, redirects, caching semantics, favorites idempotency
- **Testing** — meaningful feature tests; resilient unit tests; isolation (`RefreshDatabase`/`DatabaseMigrations`)
- **Security** — authN/authZ, CSRF, guarded inputs, no secret/token leakage
- **Docs & communication** — ADRs, Self‑Review, commit messages, PR template, time‑spent
- **Delivery** — CI, container build, developer experience

---

## Getting Started (quick)

```bash
git clone <your-fork-url>
cd laravel-quotes-starter

./scripts/bootstrap-laravel.sh

cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
vendor/bin/phpstan analyse
php artisan serve  # http://127.0.0.1:8000
```

**Container (Podman/Docker)**

```bash
podman build -t quotes-app:dev -f starter-overlay/Containerfile .
podman run --rm -p 8080:80 -e APP_KEY=base64:dummy quotes-app:dev
# or docker build/run ...
```

---

### Notes
- Toggle client mode via `.env` → `QUOTES_CLIENT=stub|real` (default `real`)
- Use only ZenQuotes **free** endpoints; ensure visible **attribution** to zenquotes.io
- Respect free tier limits; prefer **batch caching** and local sampling for quotes
