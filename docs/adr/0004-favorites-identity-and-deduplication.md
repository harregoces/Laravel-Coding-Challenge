# ADR 0004: Favorites Identity & De-duplication (Canonicalization & Constraints)

- **Status:** Accepted
- **Date:** 2025-01-10

## Context

Users can add quotes to their favorites from multiple sources (web pages, API calls). The same quote might appear with slight variations (extra whitespace, different formatting) which should be treated as identical. We need a strategy to:

1. Uniquely identify quotes regardless of formatting differences
2. Prevent duplicate favorites per user
3. Allow multiple users to favorite the same quote
4. Support deletion by either unique hash or text+author combination

Key challenges:
- Same quote text with extra spaces should be one quote, not two
- Authors like "John  Lennon" and "John Lennon" should be treated as same
- Must work across both web and API interfaces
- Database must enforce constraints to prevent duplicates
- Delete operations need flexible identification (hash OR text+author)

## Decision

We have implemented **content-based identity with canonicalization and SHA-256 hashing**:

**QuoteIdentity Utility** (`app/Support/QuoteIdentity.php:12`)
- Static method `hashFrom(string $text, ?string $author)` returns `[canonText, canonAuthor, uniqueHash]`
- Centralizes all identity logic in one place

**Text Canonicalization** (`QuoteIdentity.php:18-19`)
- `trim()` removes leading/trailing whitespace
- `preg_replace('/\s+/', ' ', $text)` collapses multiple spaces/tabs/newlines to single space
- Ensures "Life  is   good" and "Life is good" produce same canonical form

**Author Canonicalization** (`QuoteIdentity.php:22-26`)
- Same trim + whitespace collapse as text
- Empty string after trimming becomes `null`
- Ensures consistent null handling for anonymous quotes

**Unique Hash Generation** (`QuoteIdentity.php:29-30`)
- SHA-256 hash of `"{canonText}|{canonAuthor}"`
- Pipe delimiter separates text from author
- Null author becomes empty string in hash input
- 64-character hex string suitable for database indexing

**Database Schema:**
- `quotes` table has `unique_hash` column with unique constraint (`migrations/2025_01_01_000001_create_quotes_table.php`)
- `favorite_quotes` pivot table has composite unique constraint on `[user_id, quote_id]` (`migrations/2025_01_01_000002_create_favorite_quotes_table.php`)
- Prevents same user from favoriting same quote twice

**FavoritesService** (`app/Services/FavoritesService.php`)
- `addFavorite()` method:
  - Calls `QuoteIdentity::hashFrom()` to get canonical values and hash
  - Uses `firstOrCreate()` to find existing quote or create new one by `unique_hash`
  - Uses `syncWithoutDetaching([quote_id])` for idempotent attach (no error if already favorited)
- `removeFavorite()` method:
  - Accepts either `unique_hash` OR `text+author` combination
  - Finds quote by hash or re-computes hash from text+author
  - Deletes from pivot table using `detach()`

**Idempotency:**
- Adding same favorite twice = no-op (no error, no duplicate)
- Implemented via `syncWithoutDetaching()` which only attaches if not already present
- Delete by non-existent hash = no error, just returns false

## Consequences

**Pros:**
- **No duplicates:** Database constraints enforce uniqueness at both quote and user-quote levels
- **Consistent identity:** Same quote text always produces same hash regardless of whitespace
- **Efficient lookups:** SHA-256 hash indexed for O(1) lookups
- **Flexible deletion:** Can delete by hash (API convenient) or text+author (web forms convenient)
- **Idempotent operations:** Safe to call `addFavorite()` multiple times
- **Multi-source support:** Works identically from web and API
- **Testable:** Pure function `QuoteIdentity::hashFrom()` easy to unit test

**Cons:**
- **Hash collisions:** Theoretical (astronomically unlikely with SHA-256)
- **Case sensitivity:** "LIFE" and "life" are different quotes (could be feature or bug depending on perspective)
- **Author variations:** "John Lennon" and "J. Lennon" treated as different authors (no fuzzy matching)
- **Extra computation:** Every add operation computes hash even if quote exists
- **Storage overhead:** 64-byte hash stored for every quote

## Alternatives Considered

1. **No canonicalization - store exact text as received**
   - **Pros:** Simpler, preserves original formatting
   - **Cons:** "Life is good" and "Life  is  good" would be separate favorites
   - **Why Rejected:** Poor UX, users would see "duplicates"

2. **UUID for quote identity**
   - **Pros:** Guaranteed unique, no hash computation
   - **Cons:** No way to detect duplicates, same quote from different sources = different IDs
   - **Why Rejected:** Doesn't solve deduplication problem

3. **Composite primary key on [text, author]**
   - **Pros:** No hash needed, simpler schema
   - **Cons:** Text can be very long (500+ chars), bad index performance, doesn't handle whitespace variations
   - **Why Rejected:** Performance issues, no canonicalization

4. **MD5 instead of SHA-256**
   - **Pros:** Faster computation, shorter hash (32 chars vs 64)
   - **Cons:** Cryptographically broken, higher collision risk
   - **Why Rejected:** SHA-256 is modern standard, performance difference negligible

5. **Case-insensitive canonicalization**
   - **Pros:** "Life" and "life" treated as same
   - **Cons:** Loses intentional capitalization, "IT" (pronoun) vs "It" (tech) collision
   - **Why Rejected:** Preserving case respects author's intent

6. **Fuzzy author matching** (Levenshtein distance, soundex)
   - **Pros:** Could match "John Lennon" and "J. Lennon"
   - **Cons:** Complex, ambiguous (John Smith vs John Smyth), performance overhead, no clear cutoff
   - **Why Rejected:** Over-engineered for this use case, exact match clearer

## How to Verify

- **Canonicalization:** Run `tests/Unit/QuoteIdentityTest.php` - validates trim and whitespace collapse
- **Hash consistency:** Add same quote with different whitespace - should be one DB record
- **Idempotency:** Call `addFavorite()` twice with same quote - check `favorite_quotes` table has one row
- **Delete by hash:** Use API DELETE with `unique_hash` - should remove favorite
- **Delete by text+author:** Use web form with text+author - should remove favorite
- **Database constraints:** Try inserting duplicate quote with same hash - should fail uniquely
- **Multi-user:** Two users favorite same quote - quotes table has 1 row, favorite_quotes has 2 rows

## Code Anchors

- QuoteIdentity utility: `laravel-app/app/Support/QuoteIdentity.php:12-34`
- Text canonicalization: `laravel-app/app/Support/QuoteIdentity.php:18-19`
- Author canonicalization: `laravel-app/app/Support/QuoteIdentity.php:22-26`
- Hash generation: `laravel-app/app/Support/QuoteIdentity.php:29-30`
- FavoritesService add: `laravel-app/app/Services/FavoritesService.php` (addFavorite method)
- FavoritesService remove: `laravel-app/app/Services/FavoritesService.php` (removeFavorite method)
- Quotes migration: `laravel-app/database/migrations/2025_01_01_000001_create_quotes_table.php`
- Favorites migration: `laravel-app/database/migrations/2025_01_01_000002_create_favorite_quotes_table.php`
- Idempotent attach: FavoritesService uses `syncWithoutDetaching()`
- Unit tests: `laravel-app/tests/Unit/QuoteIdentityTest.php`
- Feature tests: `laravel-app/tests/Feature/FavoritesApiTest.php`, `FavoritesWebTest.php`
